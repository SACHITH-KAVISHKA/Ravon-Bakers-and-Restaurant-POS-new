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

    /**
     * Export item details (individual transactions) as Excel
     */
    public function exportItemDetails(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $itemId = $request->input('item_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Get item details
        $item = Item::find($itemId);

        // Get all branches
        $branches = Branch::where('status', 1)
            ->where('name', '!=', 'Main Branch')
            ->orderBy('name')
            ->get();

        // Get all sales for this item within date range
        $salesItems = SaleItem::with(['sale.branch'])
            ->where('item_id', $itemId)
            ->whereHas('sale', function ($query) use ($fromDate, $toDate) {
                $query->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate)
                    ->where('status', 1);
            })
            ->get();

        // Group by branch
        $branchData = [];
        $grandTotal = 0;

        foreach ($salesItems as $saleItem) {
            $sale = $saleItem->sale;
            $branchId = $sale->branch_id;

            if (!$branchId) continue;

            $branch = $branches->firstWhere('id', $branchId);
            if (!$branch) continue;

            if (!isset($branchData[$branch->name])) {
                $branchData[$branch->name] = [
                    'branch_name' => $branch->name,
                    'transactions' => [],
                    'total_quantity' => 0,
                ];
            }

            $branchData[$branch->name]['transactions'][] = [
                'receipt_no' => $sale->receipt_no,
                'quantity' => $saleItem->quantity,
                'unit_price' => $saleItem->unit_price,
                'total_price' => $saleItem->total_price,
                'date' => $sale->created_at->format('Y-m-d H:i:s'),
            ];

            $branchData[$branch->name]['total_quantity'] += $saleItem->quantity;
            $grandTotal += $saleItem->quantity;
        }

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'Item Transaction Details');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Item information
        $sheet->setCellValue('A2', 'Item Code:');
        $sheet->setCellValue('B2', $item->item_code ?? 'N/A');
        $sheet->setCellValue('A3', 'Item Name:');
        $sheet->setCellValue('B3', $item->item_name ?? 'Unknown');
        $sheet->setCellValue('A4', 'Date Range:');
        $sheet->setCellValue('B4', $fromDate . ' to ' . $toDate);
        
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);

        $currentRow = 6;

        // Add data for each branch
        foreach ($branchData as $data) {
            // Branch header
            $sheet->setCellValue('A' . $currentRow, $data['branch_name']);
            $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A' . $currentRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E3F2FD');
            $currentRow++;

            // Column headers
            $headers = ['Receipt No', 'Quantity', 'Unit Price', 'Total Price', 'Date'];
            $sheet->fromArray([$headers], null, 'A' . $currentRow);
            $sheet->getStyle('A' . $currentRow . ':E' . $currentRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $currentRow . ':E' . $currentRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('BBDEFB');
            $currentRow++;

            // Transaction rows
            foreach ($data['transactions'] as $transaction) {
                $row = [
                    $transaction['receipt_no'],
                    $transaction['quantity'],
                    'LKR ' . number_format($transaction['unit_price'], 2),
                    'LKR ' . number_format($transaction['total_price'], 2),
                    $transaction['date'],
                ];
                $sheet->fromArray([$row], null, 'A' . $currentRow);
                $currentRow++;
            }

            // Branch total
            $sheet->setCellValue('A' . $currentRow, 'Branch Total:');
            $sheet->setCellValue('B' . $currentRow, $data['total_quantity']);
            $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $currentRow . ':E' . $currentRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFF9C4');
            $currentRow += 2; // Add spacing
        }

        // Grand total
        $sheet->setCellValue('A' . $currentRow, 'GRAND TOTAL QUANTITY:');
        $sheet->setCellValue('B' . $currentRow, $grandTotal);
        $sheet->mergeCells('A' . $currentRow . ':A' . $currentRow);
        $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4CAF50');
        $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Add border to remaining cells for visual consistency
        $sheet->getStyle('C' . $currentRow . ':E' . $currentRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4CAF50');

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create filename
        $itemCode = $item->item_code ?? 'item';
        $fileName = 'item-details-' . $itemCode . '-' . now()->format('Ymd-His') . '.xlsx';

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
