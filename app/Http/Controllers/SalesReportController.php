<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Branch;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    /**
     * Display the sales report index page
     */
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
}
