<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
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
        
        $items = Item::with('inventory')
            ->where('is_active', true)
            ->orderBy('category')
            ->get()
            ->groupBy('category');
            
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
        ]);

        // Generate receipt number: RCP + year(last digit) + month + date + auto number (0001-9999)
        // The counter resets to 0001 every day
        $today = now();
        $datePrefix = $today->format('ymd'); // Last 2 digits of year + month + date
        $dailyCount = Sale::whereDate('created_at', $today->toDateString())->count() + 1;
        $receiptNo = 'RCP' . $datePrefix . str_pad($dailyCount, 4, '0', STR_PAD_LEFT);
        $subtotal = 0;
        $saleItems = [];

        // Calculate subtotal and prepare sale items
        foreach ($request->items as $requestItem) {
            $item = Item::find($requestItem['id']);
            
            // Note: Stock validation removed - allow all items to be processed
            
            $quantity = $requestItem['quantity'];
            $unitPrice = $item->price;
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
        
        // Handle customer payment and balance based on payment method
        if (in_array($request->payment_method, ['CASH', 'CARD & CASH'])) {
            $customerPayment = $request->customer_payment ?? 0;
            
            // Validate payment amount for cash transactions
            if ($customerPayment < $total) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient payment amount'
                ]);
            }
            
            $balance = $customerPayment - $total;
        } else {
            // For card, credit, complimentary, and online payments
            $customerPayment = $total; // Set customer payment equal to total
            $balance = 0; // No balance for these payment methods
        }

        $saleId = null;

        DB::transaction(function () use ($receiptNo, $subtotal, $discount, $tax, $total, $request, $saleItems, $customerPayment, $balance, &$saleId) {
            // Create sale record
            $sale = Sale::create([
                'receipt_no' => $receiptNo,
                'terminal' => '01',
                'user_name' => Auth::user()->name,
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
                'balance' => $balance,
            ]);

            $saleId = $sale->id;

            // Create sale items and update inventory
            foreach ($saleItems as $saleItem) {
                $saleItem['sale_id'] = $sale->id;
                SaleItem::create($saleItem);

                // Update inventory (allow negative stock)
                $item = Item::with('inventory')->find($saleItem['item_id']);
                if ($item && $item->inventory) {
                    $newStock = $item->inventory->current_stock - $saleItem['quantity'];
                    $item->inventory->update(['current_stock' => $newStock]);
                    $item->update(['stock_quantity' => $newStock]);
                }
            }

            session(['sale_id' => $sale->id]);
        });

        return response()->json([
            'success' => true,
            'receipt_no' => $receiptNo,
            'user_name' => Auth::user()->name,
            'subtotal' => number_format($subtotal, 2),
            'total' => number_format($total, 2),
            'customer_payment' => number_format($customerPayment, 2),
            'balance' => number_format($balance, 2),
            'redirect_url' => route('pos.receipt', $saleId)
        ]);
    }

    public function receipt(Sale $sale)
    {
        $sale->load('saleItems');
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
