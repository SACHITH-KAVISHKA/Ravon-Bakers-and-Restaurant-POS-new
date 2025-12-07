<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Branch;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Get all branches for the filter dropdown
        $branches = Branch::all();

        // Start building the query
        $query = Order::with(['branch', 'user']);

        // 1. Filter by Branch ID
        if ($request->has('branch_id') && $request->branch_id != '') {
            $query->where('branch_id', $request->branch_id);
        }

        // 2. Filter by Date Range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date_time', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Get the filtered and paginated orders
        $orders = $query->where('order_status', '!=', 0) // 1. order_status 0 නොවන ඒවා
                        ->where(function ($q) {
                            // 2. status සහ order_status දෙකම 2 වන අවස්ථා ඉවත් කිරීම
                            // තර්කය: Status 2 නෙවෙයි හෝ Order Status 2 නෙවෙයි නම් ගන්න (De Morgan's Law)
                            $q->where('status', '!=', 2)
                              ->orWhere('order_status', '!=', 2);
                        })
                        ->orderBy('date_time', 'desc')
                        ->paginate(15)
                        ->withQueryString();

        return view('staff.orders.index', compact('orders', 'branches'));
    }

    public function create()
    {
        $user = Auth::user();
        $branches = Branch::all();

        // Items වගුව item_branch_prices සමග JOIN කර මිල ලබා ගැනීම
        // මෙය "Unknown column" සහ "Undefined property" දෝෂ විසඳයි
        $items = DB::table('items')
            ->join('item_branch_prices', 'items.id', '=', 'item_branch_prices.item_id')
            ->where('items.is_active', 1)
            ->where('item_branch_prices.branch_id', $user->branch_id)
            ->select(
                'items.id',
                'items.item_name',
                'items.item_code', // මෙය create view එකේදී අවශ්‍ය වේ
                'item_branch_prices.price' // මිල ලබා ගන්නේ මෙතැනින්
            )
            ->get();

        return view('staff.orders.create', compact('branches', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_time' => 'required|date',
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $calculatedTotal = 0;
            $orderItemsData = [];

            foreach ($request->items as $itemData) {
                // Database එකෙන් කෙලින්ම මිල පරීක්ෂා කිරීම (Backend Validation)
                $priceData = DB::table('item_branch_prices')
                    ->where('item_id', $itemData['item_id'])
                    ->where('branch_id', $request->branch_id)
                    ->first();

                if (!$priceData) {
                    throw new \Exception("Item ID {$itemData['item_id']} සඳහා මෙම ශාඛාවේ මිලක් හමු නොවීය.");
                }

                $price = $priceData->price;
                $subtotal = $price * $itemData['quantity'];
                $calculatedTotal += $subtotal;

                $orderItemsData[] = [
                    'item_id' => $itemData['item_id'],
                    'price' => $price,
                    'quantity' => $itemData['quantity'],
                    'subtotal' => $subtotal,
                ];
            }

            // Balance සහ Status ගණනය කිරීම
            $balance = $calculatedTotal - $request->paid_amount;
            $status = ($balance <= 0) ? 2 : 1; // 2=Paid, 1=Unpaid

            $order = Order::create([
                'date_time' => $request->date_time,
                'branch_id' => $request->branch_id,
                'user_id' => Auth::id(),
                'customer_name' => $request->customer_name,
                'total_amount' => $calculatedTotal,
                'paid_amount' => $request->paid_amount,
                'balance_amount' => $balance,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'status' => $status,
                'order_status' => 1,
            ]);

            foreach ($orderItemsData as $data) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $data['item_id'],
                    'price' => $data['price'],
                    'quantity' => $data['quantity'],
                    'subtotal' => $data['subtotal'],
                    'status' => 1,
                ]);
            }

            DB::commit();
            // return redirect()->route('staff.orders.index')->with('success', 'Order created successfully.');
            // Redirect to Show page with a flag to trigger printing
            return redirect()->route('staff.orders.show', $order->id)
                ->with('success', 'Order created successfully.')
                ->with('print_receipt', true);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
    // New Show Method (View Details)
    public function show($id)
    {
        // Load order with related items, branch, and user
        $order = Order::with(['items.item', 'branch', 'user'])
                      ->findOrFail($id);

        return view('staff.orders.show', compact('order'));
    }
}
