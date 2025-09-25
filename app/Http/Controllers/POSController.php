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
            'card_payment' => 'nullable|numeric|min:0',
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
        
        // Initialize payment variables
        $customerPayment = $request->customer_payment ?? 0;
        $cardPayment = $request->card_payment ?? 0;
        $balance = 0;
        $creditBalance = 0;
        
        // Handle payment calculations based on payment method
        switch ($request->payment_method) {
            case 'CASH':
                // Validate payment amount for cash transactions
                if ($customerPayment < $total) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient payment amount'
                    ]);
                }
                $balance = $customerPayment - $total;
                break;
                
            case 'CARD':
                // For card only, set card payment to total, no customer payment needed
                $cardPayment = $total;
                $customerPayment = 0;
                $balance = 0;
                break;
                
            case 'CARD & CASH':
                // Validate combined payment
                if (($customerPayment + $cardPayment) < $total) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient payment amount'
                    ]);
                }
                // Balance = customer payment - (subtotal - card payment)
                $remainingAfterCard = $total - $cardPayment;
                $balance = $customerPayment - $remainingAfterCard;
                break;
                
            case 'CREDIT':
                // For credit, no payment made, negative balance
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
                'card_payment' => $cardPayment,
                'balance' => $balance,
                'credit_balance' => $creditBalance,
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
            'card_payment' => number_format($cardPayment, 2),
            'balance' => number_format($balance, 2),
            'payment_method' => $request->payment_method,
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
