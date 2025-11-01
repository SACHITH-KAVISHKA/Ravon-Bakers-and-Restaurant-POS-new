<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\InventoryRequestItem;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Kot;
use App\Models\KotItem;
use App\Services\PrinterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function index()
    {
        // Clear any previous sale session when starting new POS session
        if (request()->has('clear') || !session()->has('pos_initialized')) {
            session()->forget(['sale_id', 'pos_cart', 'pos_customer_payment', 'pos_payment_method']);
            session()->put('pos_initialized', true);
        }
        
        $user = User::with('branch')->find(Auth::id());
        
        // Show all active items regardless of stock availability
        // Staff can sell items even without stock in their branch
        // Always eager load branchPrices (and branch) and inventory for efficiency
        $userBranchId = (int) $user->branch_id;
        
        $query = Item::where('is_active', true)
            ->with(['branchPrices.branch'])
            ->orderBy('category');

        if ($user && $user->role === 'staff' && $userBranchId) {
            // For staff, also eager-load inventory for their branch
            $query = $query->with(['inventory' => function($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            }]);
        } else {
            $query = $query->with('inventory');
        }

        $items = $query->get()->groupBy('category');

        // Attach a branch-aware pos_price attribute to each item so views can use it
        $items = $items->map(function ($categoryItems) use ($user, $userBranchId) {
            return $categoryItems->map(function ($item) use ($user, $userBranchId) {
                $posPrice = 0;
                // If staff with branch, prefer that branch's price
                if ($user && $user->role === 'staff' && $userBranchId) {
                    $bp = $item->branchPrices->firstWhere('branch_id', $userBranchId);
                    if ($bp) {
                        $posPrice = $bp->price;
                    }
                }

                // fallback to first branch price if none found
                if ($posPrice === 0 || $posPrice === 0.0) {
                    $firstBp = $item->branchPrices->first();
                    $posPrice = $firstBp ? $firstBp->price : 0;
                }

                // Attach attribute
                $item->setAttribute('pos_price', $posPrice);
                return $item;
            });
        });
            
        return view('pos.index', compact('items'));
    }

    public function processSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:CASH,CARD,CARD & CASH,CREDIT,COMPLIMENTARY,ONLINE',
            'customer_payment' => 'nullable|numeric|min:0',
            'card_payment' => 'nullable|numeric|min:0',
        ]);

        // Generate a unique receipt number safely.
        // Format: RCP + yymmdd + 4-digit counter (0001-9999). If collision occurs, retry with incremented counter.
        $today = now();
        $datePrefix = $today->format('ymd');
        $counter = Sale::whereDate('created_at', $today->toDateString())->count() + 1;
        $maxAttempts = 5;
        $attempt = 0;
        do {
            $receiptNo = 'RCP' . $datePrefix . str_pad($counter, 4, '0', STR_PAD_LEFT);
            // If exists, increment counter and retry
            $exists = Sale::where('receipt_no', $receiptNo)->exists();
            if ($exists) {
                $counter++;
            }
            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        // If still exists after attempts, append a short random suffix to ensure uniqueness
        if (Sale::where('receipt_no', $receiptNo)->exists()) {
            $receiptNo = 'RCP' . $datePrefix . str_pad($counter, 4, '0', STR_PAD_LEFT) . '-' . substr(md5(uniqid('', true)), 0, 4);
        }
        $subtotal = 0;
        $saleItems = [];

        // Calculate subtotal and prepare sale items
        foreach ($request->items as $requestItem) {
            $item = Item::find($requestItem['id']);
            $quantity = $requestItem['quantity'];
            
            // No stock validation here - allow sale even without stock
            // Stock will be reduced only if available in the branch
            
            // Prefer branch price for the staff user's branch when processing a sale
            $user = Auth::user();
            $userId = (int) $user->id;
            $userBranchId = (int) $user->branch_id;
            
            if ($user && $user->role === 'staff' && $userBranchId) {
                $unitPrice = $item->branchPrices()->where('branch_id', $userBranchId)->first()?->price ?? ($item->branchPrices()->first()?->price ?? 0);
            } else {
                $unitPrice = $item->branchPrices()->first()?->price ?? 0;
            }
            $totalPrice = $unitPrice * $quantity;
            
            $subtotal += $totalPrice;
            
            $saleItems[] = [
                'item_id' => $item->id,
                'item_name' => $item->item_name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];
        }

        $discount = 0; // No discount
        $tax = 0; // No tax  
        $total = $subtotal; // Simple total calculation
        
        // Initialize payment variables
        $customerPayment = $request->customer_payment ?? 0;
        $cardPayment = $request->card_payment ?? 0;
        $balance = 0;
        $creditBalance = 0;
        
        // Handle payment calculations based on payment method
        switch ($request->payment_method) {
            case 'CASH':
                // Allow partial payments - any unpaid amount becomes credit
                if ($customerPayment < $total) {
                    $creditBalance = $total - $customerPayment;
                    $balance = 0;
                } else {
                    $balance = $customerPayment - $total;
                }
                break;
                
            case 'CARD':
                // Allow partial card payments - any unpaid amount becomes credit
                if ($cardPayment < $total) {
                    $creditBalance = $total - $cardPayment;
                    $balance = 0;
                } else {
                    $balance = $cardPayment - $total; // Calculate overpayment balance
                }
                $customerPayment = 0; // No cash involved
                break;
                
            case 'CARD & CASH':
                // Allow partial combined payments - any unpaid amount becomes credit
                $totalPaid = $customerPayment + $cardPayment;
                if ($totalPaid < $total) {
                    $creditBalance = $total - $totalPaid;
                    $balance = 0;
                } else {
                    $balance = $totalPaid - $total;
                }
                break;
                
            case 'CREDIT':
                // For credit, no payment made, entire amount as credit
                $customerPayment = 0;
                $cardPayment = 0;
                $balance = 0;
                $creditBalance = $total;
                break;
                
            default:
                // For complimentary and online payments
                $customerPayment = $total;
                $cardPayment = 0;
                $balance = 0;
        }

        $saleId = null;

        DB::transaction(function () use ($receiptNo, $subtotal, $discount, $tax, $total, $request, $saleItems, $customerPayment, $cardPayment, $balance, $creditBalance, &$saleId) {
            $user = Auth::user();
            $userId = (int) ($user->id ?? null);
            $userBranchId = (int) ($user->branch_id ?? null);

            // Create sale record, now storing user_id and branch_id for robustness
            $sale = Sale::create([
                'receipt_no' => $receiptNo,
                'terminal' => '01',
                'user_id' => $userId ?: null,
                'branch_id' => $userBranchId ?: null,
                'user_name' => $user->name ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => match($request->payment_method) {
                    'CASH' => 'cash',
                    'CARD' => 'card', 
                    'CARD & CASH' => 'card_and_cash',
                    'CREDIT' => 'credit',
                    'COMPLIMENTARY' => 'complimentary',
                    'ONLINE' => 'online',
                    default => 'cash'
                },
                'customer_payment' => $customerPayment,
                'card_payment' => $cardPayment,
                'balance' => $balance,
                'credit_balance' => $creditBalance,
            ]);

            $saleId = $sale->id;

            // Separate items by type for KOT/BOT
            $kitchenItems = [];
            $barItems = [];

            // Create sale items and update inventory
            foreach ($saleItems as $saleItem) {
                $saleItem['sale_id'] = $sale->id;
                SaleItem::create($saleItem);
                
                // Get item details to determine type
                $item = Item::find($saleItem['item_id']);
                
                // Categorize items for KOT/BOT
                if ($item && $item->item_type) {
                    if ($item->item_type === 'Kitchen' || $item->item_type === 'Both') {
                        $kitchenItems[] = [
                            'item' => $item,
                            'quantity' => $saleItem['quantity'],
                            'unit_price' => $saleItem['unit_price'],
                            'total_price' => $saleItem['total_price'],
                            'item_name' => $saleItem['item_name']
                        ];
                    }
                    if ($item->item_type === 'Bar' || $item->item_type === 'Both') {
                        $barItems[] = [
                            'item' => $item,
                            'quantity' => $saleItem['quantity'],
                            'unit_price' => $saleItem['unit_price'],
                            'total_price' => $saleItem['total_price'],
                            'item_name' => $saleItem['item_name']
                        ];
                    }
                }
                
                // Handle inventory for branch staff
                if ($user->role === 'staff' && $userBranchId) {
                    $inventory = Inventory::where('item_id', $saleItem['item_id'])
                        ->where('branch_id', $userBranchId)
                        ->first();
                    
                    if ($inventory) {
                        // Scenario 1 & 2: Inventory exists - reduce stock (can go negative)
                        $inventory->decrement('current_stock', $saleItem['quantity']);
                    } else {
                        // Scenario 3: No inventory record - create new with negative quantity
                        // Get default low_stock_alert from central inventory if available
                        $centralInventory = Inventory::where('item_id', $saleItem['item_id'])
                            ->whereNull('branch_id')
                            ->first();
                        
                        $defaultLowAlert = $centralInventory ? $centralInventory->low_stock_alert : 10;
                        
                        Inventory::create([
                            'item_id' => $saleItem['item_id'],
                            'branch_id' => $userBranchId,
                            'current_stock' => -$saleItem['quantity'], // Negative to indicate deficit
                            'low_stock_alert' => $defaultLowAlert,
                        ]);
                    }
                }
            }

            // Create KOT if there are kitchen items AND auto-print to thermal printer
            if (count($kitchenItems) > 0) {
                $kot = $this->createKot('KOT', $kitchenItems, $sale, $user, $userBranchId);
                
                // Auto-print to thermal printer (NO browser window)
                if (config('printers.auto_print.kot')) {
                    app(PrinterService::class)->printKOT($kot);
                }
            }

            // Create BOT if there are bar items AND auto-print to thermal printer
            if (count($barItems) > 0) {
                $bot = $this->createKot('BOT', $barItems, $sale, $user, $userBranchId);
                
                // Auto-print to thermal printer (NO browser window)
                if (config('printers.auto_print.bot')) {
                    app(PrinterService::class)->printBOT($bot);
                }
            }

            session(['sale_id' => $sale->id]);
        });

        // Return success response (NO print URLs for pop-ups)
        return response()->json([
            'success' => true,
            'receipt_no' => $receiptNo,
            'user_name' => Auth::user()->name,
            'subtotal' => number_format($subtotal, 2),
            'total' => number_format($total, 2),
            'customer_payment' => number_format($customerPayment, 2),
            'card_payment' => number_format($cardPayment, 2),
            'balance' => number_format($balance, 2),
            'payment_method' => $request->payment_method,
            'redirect_url' => route('pos.receipt', $saleId),
            // Removed print_urls - thermal printers handle automatically
        ]);
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['saleItems', 'branch']);
        return view('pos.receipt', compact('sale'));
    }

    /**
     * Print receipt to thermal printer
     */
    public function printReceipt(Sale $sale, PrinterService $printerService)
    {
        try {
            $sale->load(['saleItems', 'branch', 'user']);
            
            $result = $printerService->printReceipt($sale);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => "Receipt sent to printer successfully"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to print receipt. Check printer connection."
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Print error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear POS session data for new order
     */
    public function clearSession()
    {
        // Clear all POS related session data including KOT/BOT IDs
        session()->forget(['sale_id', 'pos_cart', 'pos_customer_payment', 'pos_payment_method', 'kot_id', 'bot_id']);
        session()->flush(); // Clear all session data
        
        return response()->json([
            'success' => true,
            'message' => 'Session cleared successfully'
        ]);
    }

    /**
     * Create KOT or BOT for sale items
     * 
     * @param string $type 'KOT' or 'BOT'
     * @param array $items Items to add to the ticket
     * @param Sale $sale Related sale
     * @param User $user User creating the ticket
     * @param int|null $branchId Branch ID
     * @return Kot
     */
    private function createKot($type, $items, $sale, $user, $branchId)
    {
        // Generate KOT/BOT number
        $prefix = $type === 'KOT' ? 'KOT' : 'BOT';
        $lastKot = Kot::where('type', $type)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $number = 1;
        if ($lastKot) {
            $lastNumber = intval(substr($lastKot->kot_no, -4));
            $number = $lastNumber + 1;
        }
        
        $kotNo = $prefix . '-' . date('Ymd') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        
        // Create KOT/BOT
        $kot = Kot::create([
            'kot_no' => $kotNo,
            'type' => $type,
            'sale_id' => $sale->id,
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'notes' => 'Auto-generated from POS sale #' . $sale->receipt_no,
        ]);
        
        // Add items to KOT/BOT
        foreach ($items as $itemData) {
            KotItem::create([
                'kot_id' => $kot->id,
                'item_id' => $itemData['item']->id,
                'item_name' => $itemData['item_name'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['total_price'],
                'notes' => null
            ]);
        }
        
        // Load relationships for printing
        $kot->load(['kotItems', 'branch', 'user', 'sale']);
        
        return $kot;
    }
}
