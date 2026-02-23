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
        // // Get all branches except Main Branch
        // $branches = Branch::where('status', 1)
        //     ->where('name', '!=', 'Main Branch')
        //     ->orderBy('name')
        //     ->get();

        // Relavent branches based on user role
        $branches = $this->getAllowedBranches();

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
    // public function filterItemSales(Request $request)
    // {
    //     $request->validate([
    //         'from_date' => 'required|date',
    //         'to_date' => 'required|date|after_or_equal:from_date',
    //     ]);

    //     $fromDate = $request->input('from_date');
    //     $toDate = $request->input('to_date');

    //     // Get all branches except Main Branch
    //     $branches = Branch::where('status', 1)
    //         ->where('name', '!=', 'Main Branch')
    //         ->orderBy('name')
    //         ->get();

    //     // Get sales data
    //     $salesData = $this->getSalesData($fromDate, $toDate, $branches);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $salesData,
    //         'branches' => $branches->map(function($branch) {
    //             return [
    //                 'id' => $branch->id,
    //                 'name' => $branch->name
    //             ];
    //         })
    //     ]);
    // }

    /**
     * Filter item sales via AJAX
     */
    public function filterItemSales(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $branches = $this->getAllowedBranches();
        $salesData = $this->getSalesData($request->from_date, $request->to_date, $branches);

        // Blade එකට HTML නොයවා, JS එකට අවශ්‍ය පරිදි නැවත JSON යැවීම
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
    // private function getSalesData($fromDate, $toDate, $branches)
    // {
    //     // Query to get all sales within date range
    //     $sales = Sale::with(['saleItems.item', 'branch'])
    //         ->whereDate('created_at', '>=', $fromDate)
    //         ->whereDate('created_at', '<=', $toDate)
    //         ->where('status', 1) // Only active sales
    //         ->get();

    //     // Group sales by item
    //     $itemSales = [];

    //     foreach ($sales as $sale) {
    //         $branchId = $sale->branch_id;

    //         // Skip if no branch
    //         if (!$branchId) continue;

    //         $branch = $branches->firstWhere('id', $branchId);
    //         if (!$branch) continue;

    //         foreach ($sale->saleItems as $saleItem) {
    //             $itemId = $saleItem->item_id;
    //             $itemCode = $saleItem->item->item_code ?? 'N/A';
    //             $itemName = $saleItem->item->item_name ?? 'Unknown';
    //             $category = $saleItem->item->category->name ?? 'Uncategorized';

    //             // Initialize item if not exists
    //             if (!isset($itemSales[$itemId])) {
    //                 $itemSales[$itemId] = [
    //                     'item_id' => $itemId,
    //                     'item_code' => $itemCode,
    //                     'item_name' => $itemName,
    //                     'category' => $category,
    //                     'total_quantity' => 0,
    //                     'branches' => []
    //                 ];
    //             }

    //             // Add to total quantity
    //             $itemSales[$itemId]['total_quantity'] += $saleItem->quantity;

    //             // Add to branch quantity
    //             if (!isset($itemSales[$itemId]['branches'][$branch->name])) {
    //                 $itemSales[$itemId]['branches'][$branch->name] = 0;
    //             }
    //             $itemSales[$itemId]['branches'][$branch->name] += $saleItem->quantity;
    //         }
    //     }

    //     // Fill in zero values for branches with no sales
    //     foreach ($itemSales as &$item) {
    //         foreach ($branches as $branch) {
    //             if (!isset($item['branches'][$branch->name])) {
    //                 $item['branches'][$branch->name] = 0;
    //             }
    //         }
    //     }

    //     // Convert to indexed array and sort by item name
    //     $result = array_values($itemSales);
    //     usort($result, function($a, $b) {
    //         return strcmp($a['item_name'], $b['item_name']);
    //     });

    //     return $result;
    // }

    /**
     * Get sales data grouped by item and branch
     */
    private function getSalesData($fromDate, $toDate, $branches)
    {
        $branchIds = $branches->pluck('id')->toArray();
        // ID එකට අදාල Branch Name එක සොයාගැනීම සඳහා Map එකක් සෑදීම
        $branchNamesMap = $branches->pluck('name', 'id')->toArray();

        if (empty($branchIds)) {
            return collect();
        }

        $sales = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('items', 'sale_items.item_id', '=', 'items.id')
            ->select(
                'items.id as item_id',
                'items.item_code',
                'items.item_name',
                'sales.branch_id',
                DB::raw('SUM(sale_items.quantity) as db_total_qty')
            )
            ->whereBetween('sales.created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->where('sales.status', '!=', '0')
            ->whereIn('sales.branch_id', $branchIds)
            ->groupBy('items.id', 'items.item_code', 'items.item_name', 'sales.branch_id')
            ->get();

        $itemsData = [];
        foreach ($sales as $sale) {
            if (!isset($itemsData[$sale->item_id])) {
                $itemsData[$sale->item_id] = [
                    'item_id' => $sale->item_id,
                    'item_code' => $sale->item_code,
                    'item_name' => $sale->item_name,
                    'total_quantity' => 0, // Blade එකට අවශ්‍ය පරිදි total_quantity ලෙස නම වෙනස් කරන ලදී
                    'branches' => []
                ];

                // Blade එකට අවශ්‍ය පරිදි ආයතනයේ නම (Branch Name) key එක ලෙස භාවිතා කිරීම
                foreach ($branches as $branch) {
                    $itemsData[$sale->item_id]['branches'][$branch->name] = 0;
                }
            }

            $branchName = $branchNamesMap[$sale->branch_id] ?? null;

            if ($branchName) {
                $itemsData[$sale->item_id]['branches'][$branchName] += $sale->db_total_qty;
            }

            // මුළු එකතුවට අගය එකතු කිරීම
            $itemsData[$sale->item_id]['total_quantity'] += $sale->db_total_qty;
        }

        return collect($itemsData)->sortBy('item_name')->values();
    }

    /**
     * Get detailed transaction data for a specific item
     */
    // public function getItemDetails(Request $request)
    // {
    //     $request->validate([
    //         'item_id' => 'required|integer',
    //         'from_date' => 'required|date',
    //         'to_date' => 'required|date',
    //     ]);

    //     $itemId = $request->input('item_id');
    //     $fromDate = $request->input('from_date');
    //     $toDate = $request->input('to_date');

    //     // Get all sale items for this item within date range
    //     $saleItems = SaleItem::with(['sale.branch'])
    //         ->where('item_id', $itemId)
    //         ->whereHas('sale', function($query) use ($fromDate, $toDate) {
    //             $query->where('status', 1)
    //                   ->whereDate('created_at', '>=', $fromDate)
    //                   ->whereDate('created_at', '<=', $toDate);
    //         })
    //         ->get();

    //     // Group by branch
    //     $branchData = [];
    //     $totalQuantity = 0;

    //     foreach ($saleItems as $saleItem) {
    //         $sale = $saleItem->sale;
    //         if (!$sale || !$sale->branch) continue;

    //         $branchName = $sale->branch->name;

    //         // Skip Main Branch
    //         if ($branchName === 'Main Branch') continue;

    //         if (!isset($branchData[$branchName])) {
    //             $branchData[$branchName] = [
    //                 'branch_name' => $branchName,
    //                 'transactions' => [],
    //                 'total_quantity' => 0,
    //             ];
    //         }

    //         $branchData[$branchName]['transactions'][] = [
    //             'receipt_no' => $sale->receipt_no,
    //             'quantity' => $saleItem->quantity,
    //             'unit_price' => $saleItem->unit_price,
    //             'total_price' => $saleItem->total_price,
    //             'date' => $sale->created_at->format('M d, Y H:i'),
    //         ];

    //         $branchData[$branchName]['total_quantity'] += $saleItem->quantity;
    //         $totalQuantity += $saleItem->quantity;
    //     }

    //     // Sort branches by name
    //     ksort($branchData);

    //     return response()->json([
    //         'success' => true,
    //         'branches' => array_values($branchData),
    //         'total_quantity' => $totalQuantity,
    //     ]);
    // }

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

        // අදාල පරිශීලකයාට (Admin H / Admin D) අවසර ඇති ආයතන ලබා ගැනීම
        $branches = $this->getAllowedBranches();
        $allowedBranchIds = $branches->pluck('id')->toArray();

        if (empty($allowedBranchIds)) {
            return response()->json([
                'success' => true,
                'branches' => [],
                'total_quantity' => 0,
            ]);
        }

        // Get all sale items for this item within date range and allowed branches
        $saleItems = SaleItem::with(['sale.branch'])
            ->where('item_id', $itemId)
            ->whereHas('sale', function($query) use ($fromDate, $toDate, $allowedBranchIds) {
                $query->where('status', 1)
                      ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                      ->whereIn('branch_id', $allowedBranchIds); // ෆිල්ටර් කිරීම මෙතනින් සිදුවේ
            })
            ->get();

        // Group by branch
        $branchData = [];
        $totalQuantity = 0;

        foreach ($saleItems as $saleItem) {
            $sale = $saleItem->sale;
            if (!$sale || !$sale->branch) continue;

            $branchName = $sale->branch->name;

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

        $branches = $this->getAllowedBranches();

        if ($branches->isEmpty()) {
            return redirect()->back()->with('error', 'No authorized branches found.');
        }

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

        // අදාල පරිශීලකයාට (Admin H / Admin D) අවසර ඇති ආයතන ලබා ගැනීම
        $branches = $this->getAllowedBranches();
        $allowedBranchIds = $branches->pluck('id')->toArray();

        if (empty($allowedBranchIds)) {
            return redirect()->back()->with('error', 'No authorized branches found.');
        }

        // Get all sales for this item within date range and allowed branches
        $salesItems = SaleItem::with(['sale.branch'])
            ->where('item_id', $itemId)
            ->whereHas('sale', function ($query) use ($fromDate, $toDate, $allowedBranchIds) {
                $query->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                      ->where('status', 1)
                      ->whereIn('branch_id', $allowedBranchIds); // ෆිල්ටර් කිරීම
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

    private function getAllowedBranches()
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $name = str_replace(' ', '', strtolower($user->name ?? ''));
        $username = str_replace(' ', '', strtolower($user->username ?? ''));

        $isAdminH = ($role === 'holding' || str_contains($name, 'adminh') || str_contains($username, 'adminh'));
        $isAdminD = ($role === 'delight' || str_contains($name, 'admind') || str_contains($username, 'admind'));

        $query = Branch::where('status', 1)->where('name', '!=', 'Main Branch');

        if ($isAdminH) {
            $query->whereIn('id', [6, 7]);
        } elseif ($isAdminD) {
            $query->whereIn('id', [2, 3, 4, 5]);
        }

        return $query->orderBy('name')->get();
    }
}
