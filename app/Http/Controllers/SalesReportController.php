<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{

    public function index(Request $request)
    {
        $query = Sale::query();
        // Only show active sales (status = 1)
        $query->where('status', 1);

        // Default to today's date
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        // Apply date filter
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Apply branch filter
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Get sales with pagination
        $sales = $query->with('branch')->orderBy('created_at', 'desc')->paginate(100);

        // Calculate totals - get all sales for filtering, then calculate in PHP
        $allSales = Sale::query()
            ->where('status', 1)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get();

        // Calculate totals with overpayment trimming
        $totalSubtotal = 0;
        $totalCash = 0;
        $totalCard = 0;
        $totalCredit = 0;

        foreach ($allSales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            $totalSubtotal += $total;
            $totalCredit += $sale->credit_balance ?? 0;

            // Apply payment logic - PRIORITY: Card first, then Cash
            if ($paymentMethod === 'cash') {
                $totalCash += min($customerPayment, $total);
            } elseif ($paymentMethod === 'card') {
                $totalCard += min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                // Card gets priority
                if ($cardPayment >= $total) {
                    $totalCard += $total;
                    $totalCash += 0;
                } else {
                    $totalCard += $cardPayment;
                    $remaining = $total - $cardPayment;
                    $totalCash += min($customerPayment, $remaining);
                }
            }
        }

        // Create totals object
        $totals = (object) [
            'total_transactions' => $allSales->count(),
            'total_subtotal' => $totalSubtotal,
            'total_cash' => $totalCash,
            'total_card_payment' => $totalCard,
            'total_credit_balance' => $totalCredit,
        ];

        // Get available branches for the dropdown
        $branches = Branch::active()->orderBy('name')->get();

        return view('sales-report.index', compact('sales', 'totals', 'startDate', 'endDate', 'branchId', 'branches'));
    }

    /**
     * Export sales report to Excel
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        $query = Sale::query();
        // Only export active sales
        $query->where('status', 1);

        // Apply filters
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->with('branch')->orderBy('created_at', 'desc')->get();

        // Calculate totals with overpayment trimming
        $totalSubtotal = 0;
        $totalCash = 0;
        $totalCard = 0;
        $totalCredit = 0;

        foreach ($sales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            $totalSubtotal += $total;
            $totalCredit += $sale->credit_balance ?? 0;

            // Apply payment logic - PRIORITY: Card first, then Cash
            if ($paymentMethod === 'cash') {
                $totalCash += min($customerPayment, $total);
            } elseif ($paymentMethod === 'card') {
                $totalCard += min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                // Card gets priority
                if ($cardPayment >= $total) {
                    $totalCard += $total;
                    $totalCash += 0;
                } else {
                    $totalCard += $cardPayment;
                    $remaining = $total - $cardPayment;
                    $totalCash += min($customerPayment, $remaining);
                }
            }
        }

        // Create totals object
        $totals = (object) [
            'total_subtotal' => $totalSubtotal,
            'total_cash' => $totalCash,
            'total_card_payment' => $totalCard,
            'total_credit_balance' => $totalCredit,
        ];

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'A1' => 'Receipt No',
            'B1' => 'Branch Name',
            'C1' => 'Subtotal',
            'D1' => 'Payment Method',
            'E1' => 'Cash',
            'F1' => 'Card',
            'G1' => 'Credit',
            'H1' => 'Date',
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Add data
        $row = 2;
        foreach ($sales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            // Apply payment logic - PRIORITY: Card first, then Cash
            if ($paymentMethod === 'cash') {
                $cashAmount = min($customerPayment, $total);
                $cardAmount = 0;
            } elseif ($paymentMethod === 'card') {
                $cashAmount = 0;
                $cardAmount = min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                // Card gets priority
                if ($cardPayment >= $total) {
                    $cardAmount = $total;
                    $cashAmount = 0;
                } else {
                    $cardAmount = $cardPayment;
                    $remaining = $total - $cardPayment;
                    $cashAmount = min($customerPayment, $remaining);
                }
            } else {
                $cashAmount = 0;
                $cardAmount = 0;
            }

            $sheet->setCellValue('A' . $row, $sale->receipt_no);
            $sheet->setCellValue('B' . $row, $sale->branch->name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $sale->subtotal);
            $sheet->setCellValue('D' . $row, $sale->payment_method);
            $sheet->setCellValue('E' . $row, $cashAmount);
            $sheet->setCellValue('F' . $row, $cardAmount);
            $sheet->setCellValue('G' . $row, $sale->credit_balance ?? 0);
            $sheet->setCellValue('H' . $row, $sale->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        // Add totals row
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, $totals->total_subtotal ?? 0);
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, $totals->total_cash ?? 0);
        $sheet->setCellValue('F' . $row, $totals->total_card_payment ?? 0);
        $sheet->setCellValue('G' . $row, $totals->total_credit_balance ?? 0);
        $sheet->setCellValue('H' . $row, '');

        // Style the totals row
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');

        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create filename
        $filename = 'sales_report_' . $startDate . '_to_' . $endDate . '.xlsx';

        // Create response
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Display the sales report index page
     */
    public function index2(Request $request)
    {
        // Default to today's date
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        $user = auth()->user();

        // Admin H should only see Holding branches, Admin D should only see Delight branches, other Admins see all active branches
        if ($user->name === 'Admin H' || $user->username === 'Admin H') {
            $allowedBranchIds = [6, 7]; // Holding
        } elseif ($user->name === 'Admin D' || $user->username === 'Admin D') {
            $allowedBranchIds = [2, 3, 4, 5]; // Delight
        } else {
            // අනෙකුත් සාමාන්‍ය Admin ලාට සියලුම active branches පෙන්වීමට
            $allowedBranchIds = Branch::active()->pluck('id')->toArray();
        }

        $minAllowedDate = '2026-01-01';
        // Filter Branch dropdown
        $branches = Branch::whereIn('id', $allowedBranchIds)->active()->orderBy('name')->get();

        $lastTenSalesIds = Sale::query()
            ->where('status', 1)
            ->whereDate('created_at', '>=', $minAllowedDate)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            // Branch filter removed from here so we get the global last 10
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->pluck('id'); // Get the IDs into a collection/array

        $query = Sale::query();
        $query->where('status', 1);
        $query->whereDate('created_at', '>=', $minAllowedDate);
        $query->whereIn('branch_id', $allowedBranchIds);

        $query->where(function ($q) use ($lastTenSalesIds) {
            $q->where(function ($sub) { // The odd/card/credit group
                $sub->whereRaw('id % 100 = 0')
                    ->orWhere('payment_method', 'card')
                    ->orWhere('payment_method', 'card_and_cash')
                    ->orWhere('credit_balance', '>', 0);
            })
            ->orWhereIn('id', $lastTenSalesIds); // The "last 10" group
        });

        // Apply date filter
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Apply branch filter
        if ($branchId && in_array($branchId, $allowedBranchIds)) {
                $query->where('branch_id', $branchId);
            }

        // [MODIFIED] Get sales with pagination (10 results)
        $sales = $query->with('branch')->orderBy('created_at', 'desc')->paginate(100);

        $allSalesQuery = Sale::query()
            ->where('status', 1)
            ->whereDate('created_at', '>=', $minAllowedDate)
            ->whereIn('branch_id', $allowedBranchIds)
            ->where(function ($q) use ($lastTenSalesIds) {
                $q->where(function ($sub) { // The odd/card/credit group
                    $sub->whereRaw('id % 100 = 0')
                        ->orWhere('payment_method', 'card')
                        ->orWhere('payment_method', 'card_and_cash')
                        ->orWhere('credit_balance', '>', 0);
                })
                ->orWhereIn('id', $lastTenSalesIds); // The "last 10" group
            })
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($branchId && in_array($branchId, $allowedBranchIds), fn($q) => $q->where('branch_id', $branchId));

        $allSales = $allSalesQuery->get();

        // Calculate totals with overpayment trimming
        $totalSubtotal = 0;
        $totalCash = 0;
        $totalCard = 0;
        $totalCredit = 0;

        foreach ($allSales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            $totalSubtotal += $total;
            $totalCredit += $sale->credit_balance ?? 0;

            // Apply payment logic - PRIORITY: Card first, then Cash
            if ($paymentMethod === 'cash') {
                $totalCash += min($customerPayment, $total);
            } elseif ($paymentMethod === 'card') {
                $totalCard += min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                // Card gets priority
                if ($cardPayment >= $total) {
                    $totalCard += $total;
                    $totalCash += 0;
                } else {
                    $totalCard += $cardPayment;
                    $remaining = $total - $cardPayment;
                    $totalCash += min($customerPayment, $remaining);
                }
            }
        }

        // Create totals object
        $totals = (object) [
            'total_transactions' => $allSales->count(),
            'total_subtotal' => $totalSubtotal,
            'total_cash' => $totalCash,
            'total_card_payment' => $totalCard,
            'total_credit_balance' => $totalCredit,
        ];

        return view('sales-report.index2', compact('sales', 'totals', 'startDate', 'endDate', 'branchId', 'branches'));
    }

    /**
     * Get sale items for a specific sale (AJAX)
     */
    public function getSaleItems(Sale $sale)
    {
        $sale->load('branch');
        $saleItems = $sale->saleItems()->with('item')->get();

        return response()->json([
            'sale' => [
                'receipt_no' => $sale->receipt_no,
                'user_name' => $sale->user_name,
                'branch_name' => $sale->branch->name ?? 'N/A',
                'subtotal' => $sale->subtotal,
                'discount' => $sale->discount,
                'tax' => $sale->tax,
                'total' => $sale->total,
                'payment_method' => $sale->payment_method,
                'customer_payment' => $sale->customer_payment,
                'balance' => $sale->balance,
                'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
            ],
            'items' => $saleItems->map(function ($saleItem) {
                return [
                    'item_name' => $saleItem->item_name,
                    'quantity' => $saleItem->quantity,
                    'unit_price' => $saleItem->unit_price,
                    'total_price' => $saleItem->total_price,
                ];
            })
        ]);
    }

    /**
     * Export sales report to Excel
     */
    public function exportExcel2(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        $user = auth()->user();

        if ($user->name === 'Admin H' || $user->username === 'Admin H') {
            $allowedBranchIds = [6, 7]; // Holding
        } elseif ($user->name === 'Admin D' || $user->username === 'Admin D') {
            $allowedBranchIds = [2, 3, 4, 5]; // Delight
        } else {
            $allowedBranchIds = Branch::active()->pluck('id')->toArray();
        }

        $minAllowedDate = '2026-01-01';

        $lastTenSalesIds = Sale::query()
            ->where('status', 1)
            ->whereDate('created_at', '>=', $minAllowedDate)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            // Branch filter removed from here so we get the global last 10
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->pluck('id'); // Get the IDs into a collection/array

        $query = Sale::query();
        $query->where('status', 1);
        $query->whereDate('created_at', '>=', $minAllowedDate);
        $query->whereIn('branch_id', $allowedBranchIds);

        // [MODIFIED] Filter logic: Odd/Card/Credit OR Last 10
        $query->where(function ($q) use ($lastTenSalesIds) {
            $q->where(function ($sub) { // The odd/card/credit group
                $sub->whereRaw('id % 100 = 0')
                    ->orWhere('payment_method', 'card')
                    ->orWhere('payment_method', 'card_and_cash')
                    ->orWhere('credit_balance', '>', 0);
            })
            ->orWhereIn('id', $lastTenSalesIds); // The "last 10" group
        });

        // Apply filters
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($branchId && in_array($branchId, $allowedBranchIds)) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->with('branch')->orderBy('created_at', 'desc')->get();

        // Calculate totals with overpayment trimming
        $totalSubtotal = 0;
        $totalCash = 0;
        $totalCard = 0;
        $totalCredit = 0;

        foreach ($sales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            $totalSubtotal += $total;
            $totalCredit += $sale->credit_balance ?? 0;

            // Apply payment logic - PRIORITY: Card first, then Cash
            if ($paymentMethod === 'cash') {
                $totalCash += min($customerPayment, $total);
            } elseif ($paymentMethod === 'card') {
                $totalCard += min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                // Card gets priority
                if ($cardPayment >= $total) {
                    $totalCard += $total;
                    $totalCash += 0;
                } else {
                    $totalCard += $cardPayment;
                    $remaining = $total - $cardPayment;
                    $totalCash += min($customerPayment, $remaining);
                }
            }
        }

        // Create totals object
        $totals = (object) [
            'total_subtotal' => $totalSubtotal,
            'total_cash' => $totalCash,
            'total_card_payment' => $totalCard,
            'total_credit_balance' => $totalCredit,
        ];

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            // 'A1' => 'Receipt No',
            'B1' => 'Branch Name',
            'C1' => 'Subtotal',
            'D1' => 'Payment Method',
            'E1' => 'Cash',
            'F1' => 'Card',
            'G1' => 'Credit',
            'H1' => 'Date',
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Add data
        $row = 2;
        foreach ($sales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            // Apply payment logic - PRIORITY: Card first, then Cash
            if ($paymentMethod === 'cash') {
                $cashAmount = min($customerPayment, $total);
                $cardAmount = 0;
            } elseif ($paymentMethod === 'card') {
                $cashAmount = 0;
                $cardAmount = min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                // Card gets priority
                if ($cardPayment >= $total) {
                    $cardAmount = $total;
                    $cashAmount = 0;
                } else {
                    $cardAmount = $cardPayment;
                    $remaining = $total - $cardPayment;
                    $cashAmount = min($customerPayment, $remaining);
                }
            } else {
                $cashAmount = 0;
                $cardAmount = 0;
            }

            // $sheet->setCellValue('A' . $row, $sale->receipt_no);
            $sheet->setCellValue('B' . $row, $sale->branch->name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $sale->subtotal);
            $sheet->setCellValue('D' . $row, $sale->payment_method);
            $sheet->setCellValue('E' . $row, $cashAmount);
            $sheet->setCellValue('F' . $row, $cardAmount);
            $sheet->setCellValue('G' . $row, $sale->credit_balance ?? 0);
            $sheet->setCellValue('H' . $row, $sale->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        // Add totals row
        // $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('B' . $row, 'TOTAL');
        $sheet->setCellValue('C' . $row, $totals->total_subtotal ?? 0);
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, $totals->total_cash ?? 0);
        $sheet->setCellValue('F' . $row, $totals->total_card_payment ?? 0);
        $sheet->setCellValue('G' . $row, $totals->total_credit_balance ?? 0);
        $sheet->setCellValue('H' . $row, '');

        // Style the totals row
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');

        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create filename
        $filename = 'sales_report_' . $startDate . '_to_' . $endDate . '.xlsx';

        // Create response
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Update sale status (soft-delete via status flag)
     */
    public function updateStatus(Request $request, Sale $sale)
    {

        $validated = $request->validate([
            'status' => 'required|in:0,1',
        ]);

        $status = (int) $validated['status'];

        // Cast sale status to int for comparison (it might be stored as string)
        $currentStatus = (int) $sale->status;
        $wasActive = $currentStatus === 1;

        // Update sale status
        $sale->status = $status;
        $sale->save();

        // Check if we should restore inventory
        if ($status === 0 && $wasActive) {

            // Load sale items
            $sale->load('saleItems');
            $itemCount = $sale->saleItems->count();

            if ($itemCount === 0) {
                return response()->json(['success' => true, 'status' => $sale->status]);
            }

            try {
                DB::beginTransaction();
                $restoredCount = 0;
                $skippedCount = 0;
                $errors = [];

                foreach ($sale->saleItems as $index => $saleItem) {
                    // Resolve branch ID
                    $branchId = $sale->branch_id;

                    if (!$branchId && !empty($sale->user_id)) {
                        $user = \App\Models\User::find($sale->user_id);
                        if ($user && $user->branch_id) {
                            $branchId = (int) $user->branch_id;
                        }
                    }

                    if (!$branchId && !empty($sale->user_name)) {
                        $user = \App\Models\User::where('name', $sale->user_name)->first();
                        if ($user && $user->branch_id) {
                            $branchId = (int) $user->branch_id;
                        }
                    }

                    if (!$branchId) {
                        $error = "Could not resolve branch for sale #{$sale->id}, item #{$saleItem->item_id}";
                        $errors[] = $error;
                        $skippedCount++;
                        continue;
                    }

                    $quantityToRestore = intval($saleItem->quantity);

                    if ($quantityToRestore <= 0) {
                        logger()->warning("Invalid quantity, skipping");
                        $skippedCount++;
                        continue;
                    }

                    // Check if inventory exists
                    $inventory = \App\Models\Inventory::where('item_id', $saleItem->item_id)
                        ->where('branch_id', $branchId)
                        ->first();
                    if ($inventory) {
                        $oldStock = $inventory->current_stock;

                        // Try direct update instead of increment
                        $newStock = $oldStock + $quantityToRestore;
                        $updated = \App\Models\Inventory::where('id', $inventory->id)
                            ->update(['current_stock' => $newStock]);

                        // Verify the update
                        $inventory->refresh();

                        if ((float)$inventory->current_stock === (float)$newStock) {
                            $restoredCount++;
                        } else {
                            $error = "Stock mismatch for inventory #{$inventory->id}";

                            $errors[] = $error;
                        }
                    } else {
                        $inventory = \App\Models\Inventory::create([
                            'item_id' => $saleItem->item_id,
                            'branch_id' => $branchId,
                            'current_stock' => $quantityToRestore,
                            'low_stock_alert' => 10
                        ]);
                        $restoredCount++;
                    }
                }

                DB::commit();

                if (count($errors) > 0) {
                    logger()->error("Errors encountered: " . json_encode($errors));
                }
            } catch (\Exception $e) {
                DB::rollBack();
                logger()->error("Transaction rolled back");
                logger()->error("Exception: " . $e->getMessage());
                logger()->error("File: " . $e->getFile() . " Line: " . $e->getLine());
                logger()->error("Stack trace: " . $e->getTraceAsString());

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to restore inventory: ' . $e->getMessage()
                ], 500);
            }
        } else {
            logger()->info("Skipping inventory restoration - conditions not met");
        }

        logger()->info("=== Sale Status Update Completed ===");

        return response()->json([
            'success' => true,
            'status' => $sale->status,
            'message' => 'Check logs for detailed information'
        ]);
    }

    /**
     * Display receipt for a sale from sales report
     */
    public function receipt(Sale $sale)
    {
        // Load the sale with its related items and branch
        $sale->load(['saleItems', 'branch']);

        // Give the view access to VAT and SSCL rates from settings
        $vatRate = \App\Models\Setting::where('key', 'vat_rate')->value('value') ?? 18;
        $ssclRate = \App\Models\Setting::where('key', 'sscl_rate')->value('value') ?? 2.5;

        // Return the sales report receipt view
        return view('sales-report.receipt', compact('sale', 'vatRate', 'ssclRate' ));
    }

    public function delete(Request $request)
    {
        $query = Sale::query();
       // Only show deleted sales (status = 0)
        $query->where('status', 0);

        // Default to today's date
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        // Apply date filter
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Apply branch filter
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Get sales with pagination
        $sales = $query->with('branch')->orderBy('created_at', 'desc')->paginate(100);

        // Calculate totals - get all sales for filtering, then calculate in PHP
        $allSales = Sale::query()
            ->where('status', 0)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get();

        // Calculate totals with overpayment trimming
        $totalSubtotal = 0;
        $totalCash = 0;
        $totalCard = 0;
        $totalCredit = 0;

        foreach ($allSales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            $totalSubtotal += $total;
            $totalCredit += $sale->credit_balance ?? 0;

            // Apply payment logic - PRIORITY: Card first, then Cash
            if ($paymentMethod === 'cash') {
                $totalCash += min($customerPayment, $total);
            } elseif ($paymentMethod === 'card') {
                $totalCard += min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                // Card gets priority
                if ($cardPayment >= $total) {
                    $totalCard += $total;
                    $totalCash += 0;
                } else {
                    $totalCard += $cardPayment;
                    $remaining = $total - $cardPayment;
                    $totalCash += min($customerPayment, $remaining);
                }
            }
        }

        // Create totals object
        $totals = (object) [
            'total_transactions' => $allSales->count(),
            'total_subtotal' => $totalSubtotal,
            'total_cash' => $totalCash,
            'total_card_payment' => $totalCard,
            'total_credit_balance' => $totalCredit,
        ];

        // Get available branches for the dropdown
        $branches = Branch::active()->orderBy('name')->get();

        return view('reports.delete.delete-receipt', compact('sales', 'totals', 'startDate', 'endDate', 'branchId', 'branches'));
    }


    /**
     * Export DELETED sales report to Excel
     */
    public function exportDeleted(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        $query = Sale::query();
        // Only export DELETED sales
        $query->where('status', 0);

        // Apply filters
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->with('branch')->orderBy('created_at', 'desc')->get();

        // Calculate totals with overpayment trimming
        $totalSubtotal = 0;
        $totalCash = 0;
        $totalCard = 0;
        $totalCredit = 0;

        foreach ($sales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            $totalSubtotal += $total;
            $totalCredit += $sale->credit_balance ?? 0;

            // Apply payment logic - PRIORITY: Card first, then Cash
            if ($paymentMethod === 'cash') {
                $totalCash += min($customerPayment, $total);
            } elseif ($paymentMethod === 'card') {
                $totalCard += min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                // Card gets priority
                if ($cardPayment >= $total) {
                    $totalCard += $total;
                    $totalCash += 0;
                } else {
                    $totalCard += $cardPayment;
                    $remaining = $total - $cardPayment;
                    $totalCash += min($customerPayment, $remaining);
                }
            }
        }

        // Create totals object
        $totals = (object) [
            'total_subtotal' => $totalSubtotal,
            'total_cash' => $totalCash,
            'total_card_payment' => $totalCard,
            'total_credit_balance' => $totalCredit,
        ];

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'A1' => 'Receipt No',
            'B1' => 'Branch Name',
            'C1' => 'Subtotal',
            'D1' => 'Payment Method',
            'E1' => 'Cash',
            'F1' => 'Card',
            'G1' => 'Credit',
            'H1' => 'Created At',
            'I1' => 'Deleted At',
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Add data
        $row = 2;
        foreach ($sales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            // Apply payment logic - PRIORITY: Card first, then Cash
            if ($paymentMethod === 'cash') {
                $cashAmount = min($customerPayment, $total);
                $cardAmount = 0;
            } elseif ($paymentMethod === 'card') {
                $cashAmount = 0;
                $cardAmount = min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                // Card gets priority
                if ($cardPayment >= $total) {
                    $cardAmount = $total;
                    $cashAmount = 0;
                } else {
                    $cardAmount = $cardPayment;
                    $remaining = $total - $cardPayment;
                    $cashAmount = min($customerPayment, $remaining);
                }
            } else {
                $cashAmount = 0;
                $cardAmount = 0;
            }

            $sheet->setCellValue('A' . $row, $sale->receipt_no);
            $sheet->setCellValue('B' . $row, $sale->branch->name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $sale->subtotal);
            $sheet->setCellValue('D' . $row, $sale->payment_method);
            $sheet->setCellValue('E' . $row, $cashAmount);
            $sheet->setCellValue('F' . $row, $cardAmount);
            $sheet->setCellValue('G' . $row, $sale->credit_balance ?? 0);
            $sheet->setCellValue('H' . $row, $sale->created_at->format('Y-m-d H:i:s'));
            $sheet->setCellValue('I' . $row, $sale->updated_at->format('Y-m-d H:i:s'));
            $row++;
        }

        // Add totals row
        $sheet->setCellValue('A' . $row, 'TOTAL DELETED');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, $totals->total_subtotal ?? 0);
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, $totals->total_cash ?? 0);
        $sheet->setCellValue('F' . $row, $totals->total_card_payment ?? 0);
        $sheet->setCellValue('G' . $row, $totals->total_credit_balance ?? 0);
        $sheet->setCellValue('H' . $row, '');
        $sheet->setCellValue('I' . $row, '');

        // Style the totals row
        $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFCDD2'); // Red tint for deleted report

        // Auto-size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create filename
        $filename = 'deleted_sales_report_' . $startDate . '_to_' . $endDate . '.xlsx';

        // Create response
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Display last 5 invoices for the logged-in staff member
     */
    public function staffRecentInvoices()
    {
        $user = auth()->user();

        $sales = Sale::where('user_id', $user->id)
            ->where('status', 1) // Active sales only
            ->orderBy('created_at', 'desc')
            ->take(5) // Limit to last 5
            ->with('branch')
            ->get();

        return view('staff.recent-invoices', compact('sales'));
    }

    public function specialIndex(Request $request)
    {
        $user = auth()->user();

        // Role එක අනුව Branch IDs
        $allowedBranchIds = [];
        if ($user->role === 'holding') {
            $allowedBranchIds = [6, 7];
        } elseif ($user->role === 'delight') {
            $allowedBranchIds = [2, 3, 4, 5];
        } else {
            abort(403, 'Unauthorized action.');
        }

        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        // Filter Branch dropdown
        $branches = Branch::whereIn('id', $allowedBranchIds)->active()->orderBy('name')->get();

        $minAllowedDate = '2026-01-01';

        // [CORRECTION] Use GLOBAL Last 10 logic to match index2 exactly
        // Do NOT filter by branch here. This ensures consistency with index2.
        $globalLastTenSalesIds = Sale::query()
            ->where('status', 1)
            ->whereDate('created_at', '>=', $minAllowedDate)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->pluck('id');

        $query = Sale::query();
        $query->where('status', 1);
        $query->whereDate('created_at', '>=', $minAllowedDate);

        // 1. Security Filter: අදාල branches වල දත්ත පමණක් ලබා ගැනීම
        $query->whereIn('branch_id', $allowedBranchIds);

        // 2. Filter Logic: Odd/Card/Credit OR Global Last 10
        $query->where(function ($q) use ($globalLastTenSalesIds) {
            $q->where(function ($sub) {
                $sub->whereRaw('id % 100 = 0')
                    ->orWhere('payment_method', 'card')
                    ->orWhere('payment_method', 'card_and_cash')
                    ->orWhere('credit_balance', '>', 0);
            })
            ->orWhereIn('id', $globalLastTenSalesIds); // Now checking against Global IDs
        });

        // Date Filters
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($branchId && in_array($branchId, $allowedBranchIds)) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->with('branch')->orderBy('created_at', 'desc')->paginate(100);

        // Totals Calculation (Filtered)
        $allSalesQuery = Sale::query()
            ->where('status', 1)
            ->whereDate('created_at', '>=', $minAllowedDate)
            ->whereIn('branch_id', $allowedBranchIds)
            ->where(function ($q) use ($globalLastTenSalesIds) {
                $q->where(function ($sub) {
                    $sub->whereRaw('id % 100 = 0')
                        ->orWhere('payment_method', 'card')
                        ->orWhere('payment_method', 'card_and_cash')
                        ->orWhere('credit_balance', '>', 0);
                })
                ->orWhereIn('id', $globalLastTenSalesIds);
            })
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($branchId && in_array($branchId, $allowedBranchIds), fn($q) => $q->where('branch_id', $branchId));

        $allSales = $allSalesQuery->get();

        // Calculate totals logic
        $totalSubtotal = 0;
        $totalCash = 0;
        $totalCard = 0;
        $totalCredit = 0;

        foreach ($allSales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            $totalSubtotal += $total;
            $totalCredit += $sale->credit_balance ?? 0;

            if ($paymentMethod === 'cash') {
                $totalCash += min($customerPayment, $total);
            } elseif ($paymentMethod === 'card') {
                $totalCard += min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                if ($cardPayment >= $total) {
                    $totalCard += $total;
                    $totalCash += 0;
                } else {
                    $totalCard += $cardPayment;
                    $remaining = $total - $cardPayment;
                    $totalCash += min($customerPayment, $remaining);
                }
            }
        }

        $totals = (object) [
            'total_transactions' => $allSales->count(),
            'total_subtotal' => $totalSubtotal,
            'total_cash' => $totalCash,
            'total_card_payment' => $totalCard,
            'total_credit_balance' => $totalCredit,
        ];

        return view('sales-report.index2', compact('sales', 'totals', 'startDate', 'endDate', 'branchId', 'branches'));
    }

    // SalesReportController.php

    public function exportSpecial(Request $request)
    {
        $user = auth()->user();

        // Role based allowed branches
        $allowedBranchIds = [];
        if ($user->role === 'holding') {
            $allowedBranchIds = [6, 7];
        } elseif ($user->role === 'delight') {
            $allowedBranchIds = [2, 3, 4, 5];
        } else {
            abort(403, 'Unauthorized action.');
        }

        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        // 1. Get last 10 sales IDs within allowed branches
        $globalLastTenSalesIds = Sale::query()
        ->where('status', 1)
        ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
        ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->pluck('id');

        $query = Sale::query();
        $query->where('status', 1);

        $query->whereIn('branch_id', $allowedBranchIds);

        $query->where(function ($q) use ($globalLastTenSalesIds) {
            $q->where(function ($sub) {
                $sub->whereRaw('id % 100 = 0')
                    ->orWhere('payment_method', 'card')
                    ->orWhere('payment_method', 'card_and_cash')
                    ->orWhere('credit_balance', '>', 0);
            })
            ->orWhereIn('id', $globalLastTenSalesIds);
        });

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if ($branchId && in_array($branchId, $allowedBranchIds)) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->with('branch')->orderBy('created_at', 'desc')->get();

        // Calculate totals logic (Same as original)
        $totalSubtotal = 0;
        $totalCash = 0;
        $totalCard = 0;
        $totalCredit = 0;

        foreach ($sales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            $totalSubtotal += $total;
            $totalCredit += $sale->credit_balance ?? 0;

            if ($paymentMethod === 'cash') {
                $totalCash += min($customerPayment, $total);
            } elseif ($paymentMethod === 'card') {
                $totalCard += min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                if ($cardPayment >= $total) {
                    $totalCard += $total;
                    $totalCash += 0;
                } else {
                    $totalCard += $cardPayment;
                    $remaining = $total - $cardPayment;
                    $totalCash += min($customerPayment, $remaining);
                }
            }
        }

        $totals = (object) [
            'total_subtotal' => $totalSubtotal,
            'total_cash' => $totalCash,
            'total_card_payment' => $totalCard,
            'total_credit_balance' => $totalCredit,
        ];

        // Create Excel Logic
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'B1' => 'Branch Name',
            'C1' => 'Subtotal',
            'D1' => 'Payment Method',
            'E1' => 'Cash',
            'F1' => 'Card',
            'G1' => 'Credit',
            'H1' => 'Date',
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        $row = 2;
        foreach ($sales as $sale) {
            $paymentMethod = strtolower($sale->payment_method);
            $customerPayment = $sale->customer_payment ?? 0;
            $cardPayment = $sale->card_payment ?? 0;
            $total = $sale->subtotal ?? 0;

            if ($paymentMethod === 'cash') {
                $cashAmount = min($customerPayment, $total);
                $cardAmount = 0;
            } elseif ($paymentMethod === 'card') {
                $cashAmount = 0;
                $cardAmount = min($cardPayment, $total);
            } elseif ($paymentMethod === 'card_and_cash') {
                if ($cardPayment >= $total) {
                    $cardAmount = $total;
                    $cashAmount = 0;
                } else {
                    $cardAmount = $cardPayment;
                    $remaining = $total - $cardPayment;
                    $cashAmount = min($customerPayment, $remaining);
                }
            } else {
                $cashAmount = 0;
                $cardAmount = 0;
            }

            $sheet->setCellValue('B' . $row, $sale->branch->name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $sale->subtotal);
            $sheet->setCellValue('D' . $row, $sale->payment_method);
            $sheet->setCellValue('E' . $row, $cashAmount);
            $sheet->setCellValue('F' . $row, $cardAmount);
            $sheet->setCellValue('G' . $row, $sale->credit_balance ?? 0);
            $sheet->setCellValue('H' . $row, $sale->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        // Totals Row
        $sheet->setCellValue('B' . $row, 'TOTAL');
        $sheet->setCellValue('C' . $row, $totals->total_subtotal ?? 0);
        $sheet->setCellValue('E' . $row, $totals->total_cash ?? 0);
        $sheet->setCellValue('F' . $row, $totals->total_card_payment ?? 0);
        $sheet->setCellValue('G' . $row, $totals->total_credit_balance ?? 0);

        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'group_sales_report_' . $startDate . '_to_' . $endDate . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function editSale(Sale $sale)
    {
        // අවසර පරීක්ෂාව
        if (!in_array(auth()->user()->role, ['admin', 'director'])) {
            abort(403);
        }

        $customers = \App\Models\Customer::orderBy('name')->get();
        return view('sales-report.edit-sale', compact('sale', 'customers'));
    }

    public function updateSale(Request $request, Sale $sale)
    {
        if (!in_array(auth()->user()->role, ['admin', 'director'])) {
            abort(403);
        }

        $request->validate([
            'receipt_no' => 'required|string|unique:sales,receipt_no,' . $sale->id,
            'customer_name' => 'nullable|string',
            'customer_vat' => 'nullable|string',
        ]);

        $sale->update([
            'receipt_no' => $request->receipt_no,
            'customer_name' => $request->customer_name,
            'customer_vat' => $request->customer_vat,
        ]);

       return redirect()->route('sales-report.index')->with('success', 'Sale updated successfully.');
    }


}
