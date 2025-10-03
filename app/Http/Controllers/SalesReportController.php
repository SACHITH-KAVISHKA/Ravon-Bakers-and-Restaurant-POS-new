<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
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
        $searchTerm = $request->get('search');

        // Apply date filter
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Apply search filter
        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('receipt_no', 'like', "%{$searchTerm}%")
                  ->orWhere('user_name', 'like', "%{$searchTerm}%")
                  ->orWhere('payment_method', 'like', "%{$searchTerm}%");
            });
        }

        // Get sales with pagination
        $sales = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate totals for the filtered data
        $totals = Sale::query()
            ->where('status', 1)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($searchTerm, function($q) use ($searchTerm) {
                $q->where(function($query) use ($searchTerm) {
                    $query->where('receipt_no', 'like', "%{$searchTerm}%")
                          ->orWhere('user_name', 'like', "%{$searchTerm}%")
                          ->orWhere('payment_method', 'like', "%{$searchTerm}%");
                });
            })
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(subtotal) as total_subtotal,
                SUM(customer_payment) as total_customer_payment,
                SUM(balance) as total_balance
            ')
            ->first();

        return view('sales-report.index', compact('sales', 'totals', 'startDate', 'endDate', 'searchTerm'));
    }

    /**
     * Get sale items for a specific sale (AJAX)
     */
    public function getSaleItems(Sale $sale)
    {
        $saleItems = $sale->saleItems()->with('item')->get();
        
        return response()->json([
            'sale' => [
                'receipt_no' => $sale->receipt_no,
                'user_name' => $sale->user_name,
                'subtotal' => $sale->subtotal,
                'discount' => $sale->discount,
                'tax' => $sale->tax,
                'total' => $sale->total,
                'payment_method' => $sale->payment_method,
                'customer_payment' => $sale->customer_payment,
                'balance' => $sale->balance,
                'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
            ],
            'items' => $saleItems->map(function($saleItem) {
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
        $searchTerm = $request->get('search');

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

        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('receipt_no', 'like', "%{$searchTerm}%")
                  ->orWhere('user_name', 'like', "%{$searchTerm}%")
                  ->orWhere('payment_method', 'like', "%{$searchTerm}%");
            });
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'A1' => 'Receipt No',
            'B1' => 'User Name',
            'C1' => 'Subtotal',
            'D1' => 'Payment Method',
            'E1' => 'Customer Payment',
            'F1' => 'Balance',
            'G1' => 'Date',
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Add data
        $row = 2;
        foreach ($sales as $sale) {
            $sheet->setCellValue('A' . $row, $sale->receipt_no);
            $sheet->setCellValue('B' . $row, $sale->user_name);
            $sheet->setCellValue('C' . $row, $sale->subtotal);
            $sheet->setCellValue('D' . $row, $sale->payment_method);
            $sheet->setCellValue('E' . $row, $sale->customer_payment);
            $sheet->setCellValue('F' . $row, $sale->balance);
            $sheet->setCellValue('G' . $row, $sale->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create filename
        $filename = 'sales_report_' . $startDate . '_to_' . $endDate . '.xlsx';

        // Create response
        return new StreamedResponse(function() use ($spreadsheet) {
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
        // Validate status (accepts '0' or '1' as strings or ints)
        $validated = $request->validate([
            'status' => 'required|in:0,1',
        ]);

        $status = (int) $validated['status'];

        $sale->status = $status;
        $sale->save();

        return response()->json(['success' => true, 'status' => $sale->status]);
    }
}