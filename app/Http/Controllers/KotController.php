<?php

namespace App\Http\Controllers;

use App\Models\Kot;
use App\Models\KotItem;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Services\PrinterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KotController extends Controller
{
    /**
     * Display the KOT/BOT management page
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'all'); // all, KOT, BOT

        $query = Kot::with(['kotItems', 'branch', 'user']);

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        // Show today's orders by default
        if (!$request->has('date_from')) {
            $query->whereDate('created_at', today());
        } else {
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
        }

        $kots = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('kot.index', compact('kots', 'type'));
    }

    /**
     * Display KOT/BOT creation form
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get all active items grouped by type
        $items = Item::where('is_active', true)
            ->with(['branchPrices.branch'])
            ->orderBy('category')
            ->get()
            ->groupBy('item_type');

        return view('kot.create', compact('items'));
    }

    /**
     * Store a new KOT/BOT
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:KOT,BOT',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.instructions' => 'nullable|string',
            'table_no' => 'nullable|string',
            'order_type' => 'required|in:Dine-In,Take-Away,Delivery',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            
            // Generate KOT/BOT number
            $today = now();
            $prefix = $request->type === 'KOT' ? 'KOT' : 'BOT';
            $datePrefix = $today->format('ymd');
            $counter = Kot::where('type', $request->type)
                ->whereDate('created_at', $today->toDateString())
                ->count() + 1;
            
            $kotNo = $prefix . $datePrefix . str_pad($counter, 4, '0', STR_PAD_LEFT);

            // Create KOT/BOT
            $kot = Kot::create([
                'kot_no' => $kotNo,
                'type' => $request->type,
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'table_no' => $request->table_no,
                'order_type' => $request->order_type,
                'status' => 'Pending',
                'notes' => $request->notes,
            ]);

            // Create KOT items
            foreach ($request->items as $requestItem) {
                $item = Item::find($requestItem['id']);
                $quantity = $requestItem['quantity'];
                
                // Get price for the item
                if ($user->role === 'staff' && $user->branch_id) {
                    $unitPrice = $item->branchPrices()
                        ->where('branch_id', $user->branch_id)
                        ->first()?->price ?? 
                        ($item->branchPrices()->first()?->price ?? 0);
                } else {
                    $unitPrice = $item->branchPrices()->first()?->price ?? 0;
                }
                
                $totalPrice = $unitPrice * $quantity;
                
                KotItem::create([
                    'kot_id' => $kot->id,
                    'item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'status' => 'Pending',
                    'special_instructions' => $requestItem['instructions'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->type . ' created successfully',
                'kot_id' => $kot->id,
                'kot_no' => $kotNo,
                'print_url' => route('kot.print', $kot->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating ' . $request->type . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a specific KOT/BOT
     */
    public function show(Kot $kot)
    {
        $kot->load(['kotItems.item', 'branch', 'user', 'sale']);
        return view('kot.show', compact('kot'));
    }

    /**
     * Update KOT/BOT status
     */
    public function updateStatus(Request $request, Kot $kot)
    {
        $request->validate([
            'status' => 'required|in:Pending,Preparing,Ready,Served,Completed,Cancelled',
        ]);

        $kot->status = $request->status;

        if ($request->status === 'Ready') {
            $kot->prepared_at = now();
        } elseif ($request->status === 'Served') {
            $kot->served_at = now();
        } elseif ($request->status === 'Completed') {
            $kot->completed_at = now();
        }

        $kot->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    /**
     * Update individual item status
     */
    public function updateItemStatus(Request $request, KotItem $kotItem)
    {
        $request->validate([
            'status' => 'required|in:Pending,Preparing,Ready',
        ]);

        $kotItem->status = $request->status;
        $kotItem->save();

        // Check if all items are ready, update KOT status
        $kot = $kotItem->kot;
        $allReady = $kot->kotItems()->where('status', '!=', 'Ready')->count() === 0;
        
        if ($allReady && $kot->status === 'Preparing') {
            $kot->status = 'Ready';
            $kot->prepared_at = now();
            $kot->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Item status updated successfully',
        ]);
    }

    /**
     * Convert KOT/BOT to sale
     */
    public function convertToSale(Request $request, Kot $kot)
    {
        $request->validate([
            'payment_method' => 'required|in:CASH,CARD,CARD & CASH,CREDIT,COMPLIMENTARY,ONLINE',
            'customer_payment' => 'nullable|numeric|min:0',
            'card_payment' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate receipt number
            $today = now();
            $datePrefix = $today->format('ymd');
            $counter = Sale::whereDate('created_at', $today->toDateString())->count() + 1;
            $receiptNo = 'RCP' . $datePrefix . str_pad($counter, 4, '0', STR_PAD_LEFT);

            // Calculate totals
            $subtotal = $kot->kotItems->sum('total_price');
            $total = $subtotal;

            // Initialize payment variables
            $customerPayment = $request->customer_payment ?? 0;
            $cardPayment = $request->card_payment ?? 0;
            $balance = 0;
            $creditBalance = 0;

            // Handle payment calculations
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
                    $cardPayment = 0;
                    $balance = 0;
            }

            // Create sale
            $sale = Sale::create([
                'receipt_no' => $receiptNo,
                'terminal' => '01',
                'user_id' => $kot->user_id,
                'branch_id' => $kot->branch_id,
                'user_name' => $kot->user_name,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'customer_payment' => $customerPayment,
                'card_payment' => $cardPayment,
                'balance' => $balance,
                'credit_balance' => $creditBalance,
            ]);

            // Create sale items and update inventory
            foreach ($kot->kotItems as $kotItem) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_id' => $kotItem->item_id,
                    'item_name' => $kotItem->item_name,
                    'quantity' => $kotItem->quantity,
                    'unit_price' => $kotItem->unit_price,
                    'total_price' => $kotItem->total_price,
                ]);

                // Update inventory if staff with branch
                $user = Auth::user();
                if ($user->role === 'staff' && $user->branch_id) {
                    $inventory = Inventory::where('item_id', $kotItem->item_id)
                        ->where('branch_id', $user->branch_id)
                        ->first();
                    
                    if ($inventory) {
                        $inventory->decrement('current_stock', $kotItem->quantity);
                    } else {
                        $mainInventory = Inventory::where('item_id', $kotItem->item_id)
                            ->whereHas('branch', function($q) {
                                $q->where('name', 'Main Branch');
                            })
                            ->first();

                        Inventory::create([
                            'item_id' => $kotItem->item_id,
                            'branch_id' => $user->branch_id,
                            'current_stock' => -$kotItem->quantity,
                            'low_stock_alert' => $mainInventory->low_stock_alert ?? 10,
                        ]);
                    }
                }
            }

            // Update KOT status
            $kot->sale_id = $sale->id;
            $kot->status = 'Completed';
            $kot->completed_at = now();
            $kot->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'KOT/BOT converted to sale successfully',
                'sale_id' => $sale->id,
                'receipt_url' => route('pos.receipt', $sale->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error converting to sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print KOT/BOT
     */
    public function print(Kot $kot)
    {
        $kot->load(['kotItems', 'branch', 'user']);
        return view('kot.print', compact('kot'));
    }

    /**
     * Print KOT/BOT to thermal printer
     */
    public function printThermal(Kot $kot, PrinterService $printerService)
    {
        try {
            $kot->load(['kotItems', 'branch', 'user', 'sale']);
            
            // Print based on type
            if ($kot->type === 'KOT') {
                $result = $printerService->printKOT($kot);
            } else {
                $result = $printerService->printBOT($kot);
            }

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => "{$kot->type} sent to printer successfully"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to print {$kot->type}. Check printer connection."
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
     * Test printer connection
     */
    public function testPrinter(Request $request, PrinterService $printerService)
    {
        $printerType = $request->input('type', 'kot'); // kot, bot, or pos
        
        $result = $printerService->testPrinter($printerType);
        
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => ucfirst($printerType) . ' printer test successful'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => ucfirst($printerType) . ' printer test failed. Check connection and settings.'
            ], 500);
        }
    }

    /**
     * Kitchen/Bar display view
     */
    public function kitchen(Request $request)
    {
        $type = $request->get('type', 'KOT'); // KOT or BOT
        
        $kots = Kot::with(['kotItems.item', 'branch'])
            ->where('type', $type)
            ->whereIn('status', ['Pending', 'Preparing', 'Ready'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kot.kitchen', compact('kots', 'type'));
    }

    /**
     * Get pending KOTs/BOTs for real-time updates (AJAX)
     */
    public function getPending(Request $request)
    {
        $type = $request->get('type', 'KOT');
        
        $kots = Kot::with(['kotItems.item'])
            ->where('type', $type)
            ->whereIn('status', ['Pending', 'Preparing', 'Ready'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'kots' => $kots
        ]);
    }
}

