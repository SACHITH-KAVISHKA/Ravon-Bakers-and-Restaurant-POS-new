<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Branch;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $sales = Sale::with(['saleItems.item', 'branch'])
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->where('status', 1) // Only active sales
            ->get();

        // Group sales by item
        $itemSales = [];
        
        foreach ($sales as $sale) {
            $branchId = $sale->branch_id;
            
            // Skip if no branch
            if (!$branchId) continue;
            
            $branch = $branches->firstWhere('id', $branchId);
            if (!$branch) continue;

            foreach ($sale->saleItems as $saleItem) {
                $itemId = $saleItem->item_id;
                $itemCode = $saleItem->item->item_code ?? 'N/A';
                $itemName = $saleItem->item->item_name ?? 'Unknown';
                $category = $saleItem->item->category->name ?? 'Uncategorized';

                // Initialize item if not exists
                if (!isset($itemSales[$itemId])) {
                    $itemSales[$itemId] = [
                        'item_id' => $itemId,
                        'item_code' => $itemCode,
                        'item_name' => $itemName,
                        'category' => $category,
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
     * Get detailed transaction data for a specific item
     */
    public function getItemDetails(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);

        $itemId = $request->input('item_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Get all sale items for this item within date range
        $saleItems = SaleItem::with(['sale.branch'])
            ->where('item_id', $itemId)
            ->whereHas('sale', function($query) use ($fromDate, $toDate) {
                $query->where('status', 1)
                      ->whereDate('created_at', '>=', $fromDate)
                      ->whereDate('created_at', '<=', $toDate);
            })
            ->get();

        // Group by branch
        $branchData = [];
        $totalQuantity = 0;

        foreach ($saleItems as $saleItem) {
            $sale = $saleItem->sale;
            if (!$sale || !$sale->branch) continue;

            $branchName = $sale->branch->name;
            
            // Skip Main Branch
            if ($branchName === 'Main Branch') continue;

            if (!isset($branchData[$branchName])) {
                $branchData[$branchName] = [
                    'branch_name' => $branchName,
                    'transactions' => [],
                    'total_quantity' => 0,
                ];
            }

            $branchData[$branchName]['transactions'][] = [
                'receipt_no' => $sale->receipt_no,
                'quantity' => $saleItem->quantity,
                'unit_price' => $saleItem->unit_price,
                'total_price' => $saleItem->total_price,
                'date' => $sale->created_at->format('M d, Y H:i'),
            ];

            $branchData[$branchName]['total_quantity'] += $saleItem->quantity;
            $totalQuantity += $saleItem->quantity;
        }

        // Sort branches by name
        ksort($branchData);

        return response()->json([
            'success' => true,
            'branches' => array_values($branchData),
            'total_quantity' => $totalQuantity,
        ]);
    }

    /**
     * Export the item sales data as Excel for the given date range
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

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $columns = ['Item Code', 'Item Name', 'Total Quantity'];
        foreach ($branches as $branch) {
            $columns[] = $branch->name;
        }

        // Write header row
        $sheet->fromArray([$columns], null, 'A1');

        // Style header row
        $headerStyle = $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4CAF50');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');

        // Write data rows
        $rowIndex = 2;
        foreach ($salesData as $item) {
            $row = [
                $item['item_code'] ?? '',
                $item['item_name'] ?? '',
                $item['total_quantity'] ?? 0,
            ];

            foreach ($branches as $branch) {
                $row[] = $item['branches'][$branch->name] ?? 0;
            }

            $sheet->fromArray([$row], null, 'A' . $rowIndex);
            $rowIndex++;
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create filename
        $fileName = 'item-sales-' . now()->format('Ymd-His') . '.xlsx';

        // Create response
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
