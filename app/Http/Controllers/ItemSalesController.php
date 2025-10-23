<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Branch;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemSalesController extends Controller
{
    /**
     * Display the item sales summary page
     */
    public function itemSales(Request $request)
    {
        // Get all branches except Main Branch
        $branches = Branch::where('status', 1)
            ->where('name', '!=', 'Main Branch')
            ->orderBy('name')
            ->get();

        // Default to today's date
        $fromDate = $request->input('from_date', Carbon::today()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::today()->format('Y-m-d'));

        // Get sales data
        $salesData = $this->getSalesData($fromDate, $toDate, $branches);

        return view('reports.item-sales', compact('branches', 'salesData', 'fromDate', 'toDate'));
    }

    /**
     * Filter item sales via AJAX
     */
    public function filterItemSales(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Get all branches except Main Branch
        $branches = Branch::where('status', 1)
            ->where('name', '!=', 'Main Branch')
            ->orderBy('name')
            ->get();

        // Get sales data
        $salesData = $this->getSalesData($fromDate, $toDate, $branches);

        return response()->json([
            'success' => true,
            'data' => $salesData,
            'branches' => $branches->map(function($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name
                ];
            })
        ]);
    }

    /**
     * Get sales data grouped by item and branch
     */
    private function getSalesData($fromDate, $toDate, $branches)
    {
        // Query to get all sales within date range
        $sales = Sale::with(['saleItems.item', 'user.branch'])
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->where('status', '!=', 'cancelled')
            ->get();

        // Group sales by item
        $itemSales = [];
        
        foreach ($sales as $sale) {
            $branchId = $sale->user->branch_id ?? null;
            
            // Skip if no branch or is Main Branch
            if (!$branchId) continue;
            
            $branch = $branches->firstWhere('id', $branchId);
            if (!$branch) continue;

            foreach ($sale->saleItems as $saleItem) {
                $itemId = $saleItem->item_id;
                $itemCode = $saleItem->item->item_code ?? 'N/A';
                $itemName = $saleItem->item->item_name ?? 'Unknown';

                // Initialize item if not exists
                if (!isset($itemSales[$itemId])) {
                    $itemSales[$itemId] = [
                        'item_id' => $itemId,
                        'item_code' => $itemCode,
                        'item_name' => $itemName,
                        'total_quantity' => 0,
                        'branches' => []
                    ];
                }

                // Add to total quantity
                $itemSales[$itemId]['total_quantity'] += $saleItem->quantity;

                // Add to branch quantity
                if (!isset($itemSales[$itemId]['branches'][$branch->name])) {
                    $itemSales[$itemId]['branches'][$branch->name] = 0;
                }
                $itemSales[$itemId]['branches'][$branch->name] += $saleItem->quantity;
            }
        }

        // Fill in zero values for branches with no sales
        foreach ($itemSales as &$item) {
            foreach ($branches as $branch) {
                if (!isset($item['branches'][$branch->name])) {
                    $item['branches'][$branch->name] = 0;
                }
            }
        }

        // Convert to indexed array and sort by item name
        $result = array_values($itemSales);
        usort($result, function($a, $b) {
            return strcmp($a['item_name'], $b['item_name']);
        });

        return $result;
    }

    /**
     * Export the item sales data as CSV for the given date range
     */
    public function exportExcel(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::today()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::today()->format('Y-m-d'));

        $branches = Branch::where('status', 1)
            ->where('name', '!=', 'Main Branch')
            ->orderBy('name')
            ->get();

        $salesData = $this->getSalesData($fromDate, $toDate, $branches);

        $fileName = 'item-sales-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $columns = ['Item Code', 'Item Name', 'Total Quantity'];
        foreach ($branches as $branch) {
            $columns[] = $branch->name;
        }

        $callback = function() use ($salesData, $columns, $branches) {
            $f = fopen('php://output', 'w');
            // BOM for Excel
            fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($f, $columns);

            foreach ($salesData as $item) {
                $row = [
                    $item['item_code'] ?? '',
                    $item['item_name'] ?? '',
                    $item['total_quantity'] ?? 0,
                ];

                foreach ($branches as $branch) {
                    $row[] = $item['branches'][$branch->name] ?? 0;
                }

                fputcsv($f, $row);
            }

            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }
}
