<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VatReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage-users');
    }

    public function index(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $search = $request->input('search');

        // Filter: only not null and not empty VAT numbers, and status = 1
        $query = Sale::where('status', 1)
            ->whereNotNull('customer_vat')
            ->where('customer_vat', '!=', '')
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'LIKE', '%' . $search . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . $search . '%')
                ->orWhere('customer_vat', 'LIKE', '%' . $search . '%');
            });
        }

        // Calculate totals before pagination
        $totals = clone $query;
        $totalSubtotal = $totals->sum('subtotal');
        $totalSscl = $totals->sum('sscl_amount');
        $totalVat = $totals->sum('vat_amount');
        $totalGrand = $totals->sum('total');

        $sales = $query->orderBy('created_at', 'desc')->paginate(100);

        return view('reports.vat-report', compact('sales', 'fromDate', 'toDate', 'search', 'totalSubtotal', 'totalSscl', 'totalVat', 'totalGrand'));
    }


    public function exportExcel(Request $request)
    {
        // Set default date range to current month if not provided
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $search = $request->input('search');

        $query = Sale::where('status', 1)
            ->whereNotNull('customer_vat')
            ->where('customer_vat', '!=', '')
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'LIKE', '%' . $search . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . $search . '%')
                ->orWhere('customer_vat', 'LIKE', '%' . $search . '%');
            });
        }

        $sales = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Date', 'Receipt No', 'Customer Name', 'VAT Number', 'Subtotal', 'SSCL', 'VAT Amount', 'Total'];
        $sheet->fromArray([$headers], null, 'A1');

        $headerStyle = $sheet->getStyle('A1:H1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4CAF50');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');

        $rowIndex = 2;
        foreach ($sales as $sale) {
            $row = [
                $sale->created_at->format('Y-m-d H:i'),
                $sale->receipt_no,
                $sale->customer_name ?? 'N/A',
                $sale->customer_vat,
                $sale->subtotal,
                $sale->sscl_amount,
                $sale->vat_amount,
                $sale->total,
            ];
            $sheet->fromArray([$row], null, 'A' . $rowIndex);
            $rowIndex++;
        }

        // --- Totals Row add the below in excel export ---
        $lastDataRow = $rowIndex - 1;
        $totalRow = [
            'TOTALS', '', '', '',
            "=SUM(E2:E$lastDataRow)", // Subtotal Sum
            "=SUM(F2:F$lastDataRow)", // SSCL Sum
            "=SUM(G2:G$lastDataRow)", // VAT Sum
            "=SUM(H2:H$lastDataRow)"  // Grand Total Sum
        ];
        $sheet->fromArray([$totalRow], null, 'A' . $rowIndex);

        // Bold and style the totals row
        $sheet->getStyle("A$rowIndex:H$rowIndex")->getFont()->setBold(true);
        $sheet->getStyle("A$rowIndex:H$rowIndex")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2E3E5');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'vat-report-' . now()->format('Ymd-His') . '.xlsx';

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
