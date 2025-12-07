<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Kot;
use App\Models\InventoryRequestItem;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class POSController extends Controller
{
    public function index(Request $request)
    {
        // Staff members only validation
        if (Auth::user()->role !== 'staff') {
            abort(403, 'Only staff members can access POS system.');
        }

        // Clear session logic
        if ($request->has('clear') || !session()->has('pos_initialized')) {
            session()->forget(['sale_id', 'pos_cart', 'pos_customer_payment', 'pos_payment_method']);
            session()->put('pos_initialized', true);
        }

        $user = User::with('branch')->find(Auth::id());
        $userBranchId = (int) $user->branch_id;

        // Fetch Items Logic
        $query = Item::where('is_active', true)
            ->with(['branchPrices.branch'])
            ->orderBy('item_name', 'asc');

        if ($user && $user->role === 'staff' && $userBranchId) {
            $query = $query->with(['inventory' => function($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            }]);
        } else {
            $query = $query->with('inventory');
        }

        $allItems = $query->get();

        // Attach Price Logic
        $allItems = $allItems->map(function ($item) use ($user, $userBranchId) {
            $posPrice = 0;
            if ($user && $user->role === 'staff' && $userBranchId) {
                $bp = $item->branchPrices->firstWhere('branch_id', $userBranchId);
                if ($bp) {
                    $posPrice = $bp->price;
                }
            }

            if ($posPrice === 0 || $posPrice === 0.0) {
                $firstBp = $item->branchPrices->first();
                $posPrice = $firstBp ? $firstBp->price : 0;
            }

            $item->setAttribute('pos_price', $posPrice);
            return $item;
        });

        // Group by category
        $items = $allItems->groupBy('category')->sortKeys()->map(function ($categoryItems) {
            return $categoryItems->sortBy('item_name')->values();
        });

        // --- IMPORT ORDER LOGIC (UPDATED) ---
        $importedCart = [];
        $importedOrderId = null; // To store the Order ID

        if ($request->has('import_order')) {
            $order = Order::with('items.item')->find($request->import_order);

            if ($order) {
                $importedOrderId = $order->id;

                foreach ($order->items as $oItem) {
                    // item එක null නොවන බව තහවුරු කරගැනීම
                    if ($oItem->item) {
                        $importedCart[] = [
                            'id' => $oItem->item_id,
                            'name' => $oItem->item->item_name,
                            'price' => (float)$oItem->price, // පැරණි ඇණවුමේ මිලම භාවිතා කිරීම
                            'quantity' => (float)$oItem->quantity,
                            'itemType' => $oItem->item->item_type ?? 'Kitchen', // Default value එකක් දීම
                            'category' => $oItem->item->category ?? 'Other',
                        ];
                    }
                }
            }
        }

        return view('pos.index', compact('items', 'importedCart', 'importedOrderId'));
    }

    public function processSale(Request $request)
    {
        if (Auth::user()->role !== 'staff') {
            abort(403, 'Only staff members can access POS system.');
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:CASH,CARD,CARD & CASH,CREDIT,COMPLIMENTARY,ONLINE',
            'customer_payment' => 'nullable|numeric|min:0',
            'card_payment' => 'nullable|numeric|min:0',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        // 1. Receipt Number Generation
        $today = now();
        $datePrefix = $today->format('ymd');
        $counter = Sale::whereDate('created_at', $today->toDateString())->count() + 1;
        $maxAttempts = 5;
        $attempt = 0;

        do {
            $receiptNo = 'RCP' . $datePrefix . str_pad($counter, 4, '0', STR_PAD_LEFT);
            $exists = Sale::where('receipt_no', $receiptNo)->exists();
            if ($exists) {
                $counter++;
            }
            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        if (Sale::where('receipt_no', $receiptNo)->exists()) {
            $receiptNo = 'RCP' . $datePrefix . str_pad($counter, 4, '0', STR_PAD_LEFT) . '-' . substr(md5(uniqid('', true)), 0, 4);
        }

        // 2. Data Preparation Variables
        $subtotal = 0;
        $saleItemsData = []; // SaleItem Table එකට දාන්න දත්ත
        $kotItems = [];      // Kitchen එකට යවන්න
        $botItems = [];      // Bar එකට යවන්න

        // User Details
        $user = Auth::user();
        $userId = (int) ($user->id ?? null);
        $userBranchId = (int) ($user->branch_id ?? null);

        // 3. Loop Items ONLY ONCE to calculate totals and separate KOT/BOT
        foreach ($request->items as $requestItem) {
            $item = Item::find($requestItem['id']);
            if (!$item) continue;

            $quantity = $requestItem['quantity'];

            // Price Logic
            if ($user && $user->role === 'staff' && $userBranchId) {
                $unitPrice = $item->branchPrices()->where('branch_id', $userBranchId)->first()?->price
                            ?? ($item->branchPrices()->first()?->price ?? 0);
            } else {
                $unitPrice = $item->branchPrices()->first()?->price ?? 0;
            }

            $totalPrice = $unitPrice * $quantity;
            $subtotal += $totalPrice;

            // Prepare Item Data for SaleItems Table
            $saleItemsData[] = [
                'item_id' => $item->id,
                'item_name' => $item->item_name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];

            // Separate into KOT and BOT Arrays
            $itemType = $item->item_type ?? 'Kitchen';
            $category = $item->category ?? '';

            $itemDataForOrder = [
                'item_id' => $item->id,
                'item_name' => $item->item_name,
                'quantity' => $quantity
            ];

            if ($itemType === 'Bar' || $itemType === 'Both' || $category === 'Beverages') {
                $botItems[] = $itemDataForOrder;
                // Both type items usually go to kitchen as well, or logic depends on your business.
                // Assuming 'Both' implies it needs to be tracked in both or logic handled here.
                // If 'Both' means it appears on both tickets, uncomment below:
                // if ($itemType === 'Both') $kotItems[] = $itemDataForOrder;
            }

            // Note: Logic adjusted to ensure items are captured correctly
            if ($itemType === 'Kitchen' || $itemType === 'Both' || ($itemType !== 'Bar' && $category !== 'Beverages')) {
                $kotItems[] = $itemDataForOrder;
            }
        }

        // Determine Order Type String
        $orderType = 'KOT';
        if (!empty($botItems) && empty($kotItems)) {
            $orderType = 'BOT';
        } elseif (!empty($botItems) && !empty($kotItems)) {
            $orderType = 'MIXED';
        }

        $discount = 0;
        $tax = 0;
        $total = $subtotal; // Add tax logic here if needed

        // 4. Payment Logic
        $customerPayment = $request->customer_payment ?? 0;
        $cardPayment = $request->card_payment ?? 0;
        $balance = 0;
        $creditBalance = 0;

        switch ($request->payment_method) {
            case 'CASH':
                if ($customerPayment < $total) {
                    $creditBalance = $total - $customerPayment;
                } else {
                    $balance = $customerPayment - $total;
                }
                break;
            case 'CARD':
                if ($cardPayment < $total) {
                    $creditBalance = $total - $cardPayment;
                } else {
                    $balance = $cardPayment - $total;
                }
                $customerPayment = 0;
                break;
            case 'CARD & CASH':
                $totalPaid = $customerPayment + $cardPayment;
                if ($totalPaid < $total) {
                    $creditBalance = $total - $totalPaid;
                } else {
                    $balance = $totalPaid - $total;
                }
                break;
            case 'CREDIT':
                $customerPayment = 0;
                $cardPayment = 0;
                $creditBalance = $total;
                break;
            default:
                $customerPayment = $total;
        }

        $saleId = null;
        $orderId = $request->order_id; // Capture outside closure

        Log::info('POS Sale Processing', [
            'order_id' => $orderId,
            'payment' => $request->payment_method,
            'total' => $total
        ]);

        try {
            DB::transaction(function () use (
                $receiptNo, $subtotal, $discount, $tax, $total, $request,
                $saleItemsData, $kotItems, $botItems, $orderType,
                $customerPayment, $cardPayment, $balance, $creditBalance,
                &$saleId, $orderId, $user, $userId, $userBranchId
            ) {

                // A. Create Sale Record
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
                    'order_type' => $orderType,
                ]);

                $saleId = $sale->id;

                // B. Update Existing Order (If linked)
                if ($orderId) {
                    $order = Order::find($orderId);
                    if ($order) {
                        $totalTendered = ($customerPayment ?? 0) + ($cardPayment ?? 0);

                        $amountProcessed = 0;
                        if ($request->payment_method !== 'CREDIT') {
                            $amountProcessed = ($totalTendered >= $total) ? $total : $totalTendered;
                        }

                        $newPaidAmount = $order->paid_amount + $amountProcessed;
                        $newBalance = $order->total_amount - $newPaidAmount;
                        if($newBalance < 0) $newBalance = 0;

                        $newStatus = ($newBalance <= 0.01) ? 2 : 1; // 2=Paid, 1=Partial/Pending

                        $order->update([
                            'status' => $newStatus,
                            'paid_amount' => $newPaidAmount,
                            'balance_amount' => $newBalance,
                            'order_status' => 2 // 2=Completed
                        ]);

                        Log::info("Order Updated: ID {$order->id}, Status: {$newStatus}");
                    }
                }

                // C. Create Sale Items & Update Inventory
                foreach ($saleItemsData as $itemData) {
                    $itemData['sale_id'] = $sale->id;
                    SaleItem::create($itemData);

                    // Inventory Update Logic
                    if ($user->role === 'staff' && $userBranchId) {
                        $inventory = Inventory::where('item_id', $itemData['item_id'])
                            ->where('branch_id', $userBranchId)
                            ->first();

                        if ($inventory) {
                            $inventory->decrement('current_stock', $itemData['quantity']);
                        } else {
                            // Handle missing inventory (Create new record with negative stock)
                            $centralInventory = Inventory::where('item_id', $itemData['item_id'])
                                ->whereNull('branch_id')->first();
                            $defaultLowAlert = $centralInventory ? $centralInventory->low_stock_alert : 10;

                            Inventory::create([
                                'item_id' => $itemData['item_id'],
                                'branch_id' => $userBranchId,
                                'current_stock' => -$itemData['quantity'],
                                'low_stock_alert' => $defaultLowAlert,
                            ]);
                        }
                    }
                }

                // D. Create KOT Record (If items exist)
                if (!empty($kotItems)) {
                    $kotCount = Kot::whereDate('created_at', now()->toDateString())->where('type', 'KOT')->count() + 1;
                    $kotNo = 'KOT' . now()->format('ymd') . str_pad($kotCount, 4, '0', STR_PAD_LEFT);

                    Kot::create([
                        'kot_no' => $kotNo,
                        'sale_id' => $sale->id,
                        'branch_id' => $userBranchId,
                        'user_id' => $userId,
                        'user_name' => $user->name,
                        'type' => 'KOT',
                        'items' => $kotItems,
                        'status' => 'pending'
                    ]);
                }

                // E. Create BOT Record (If items exist)
                if (!empty($botItems)) {
                    $botCount = Kot::whereDate('created_at', now()->toDateString())->where('type', 'BOT')->count() + 1;
                    $botNo = 'BOT' . now()->format('ymd') . str_pad($botCount, 4, '0', STR_PAD_LEFT);

                    Kot::create([
                        'kot_no' => $botNo,
                        'sale_id' => $sale->id,
                        'branch_id' => $userBranchId,
                        'user_id' => $userId,
                        'user_name' => $user->name,
                        'type' => 'BOT',
                        'items' => $botItems,
                        'status' => 'pending'
                    ]);
                }

                session(['sale_id' => $sale->id]);
            });

             // 1. Order විස්තර ලබා ගැනීම (JSON එකට යැවීමට)
            $linkedOrder = null;
            $formattedOrderId = null;
            $customerName = null;

            if ($orderId) {
                $linkedOrder = Order::find($orderId);
                if ($linkedOrder) {
                    $formattedOrderId = '#' . str_pad($linkedOrder->id, 5, '0', STR_PAD_LEFT);
                    $customerName = $linkedOrder->customer_name;
                }
            }

            return response()->json([
                'success' => true,
                'receipt_no' => $receiptNo,
                'user_name' => Auth::user()->name,

                // --- අලුතින් එක් කළ කොටස (START) ---
                'order_id_display' => $formattedOrderId, // Order ID එක (#00005 ලෙස)
                'customer_name' => $customerName,        // Customer Name එක
                // --- අලුතින් එක් කළ කොටස (END) ---

                'subtotal' => number_format($subtotal, 2),
                'total' => number_format($total, 2),
                'customer_payment' => number_format($customerPayment, 2),
                'card_payment' => number_format($cardPayment, 2),
                'balance' => number_format($balance, 2),
                'payment_method' => $request->payment_method,
                'redirect_url' => route('pos.receipt', $saleId)
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('POS Sale DB Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Database error occurred. Please contact support.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('POS Sale General Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing sale.'
            ], 500);
        }
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['saleItems', 'branch']);
        return view('pos.receipt', compact('sale'));
    }

    /**
     * Clear POS session data for new order
     */
    public function clearSession()
    {
        // Clear all POS related session data
        session()->forget(['sale_id', 'pos_cart', 'pos_customer_payment', 'pos_payment_method']);
        session()->flush(); // Clear all session data

        return response()->json([
            'success' => true,
            'message' => 'Session cleared successfully'
        ]);
    }
}
