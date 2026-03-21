<x-app-layout>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold mb-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; color: transparent;">
                <i class="bi bi-file-earmark-bar-graph me-2"></i> VAT Sales Report
            </h1>
            <p class="text-muted mb-0 d-none d-md-block">View and export VAT Sales transactions</p>
        </div>
        <div class="text-muted">
            <i class="bi bi-calendar3"></i>
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    <div class="card mb-4 text-dark">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.vat') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Search Customer / VAT No / Receipt</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search details...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Filter Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
        <h5 class="mb-0">VAT Sales Transactions</h5>
        <a href="{{ route('reports.vat.export', request()->all()) }}" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-2"></i>Export to Excel
        </a>
    </div>

    <div class="card text-dark">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Receipt No</th>
                            <th>Customer Name</th>
                            <th>VAT Number</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">SSCL</th>
                            <th class="text-end">VAT Amount</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                            <td><code>{{ $sale->receipt_no }}</code></td>
                            <td>{{ $sale->customer_name ?? 'N/A' }}</td>
                            <td><span class="badge bg-info text-dark">{{ $sale->customer_vat }}</span></td>
                            <td class="text-end">{{ number_format($sale->subtotal, 2) }}</td>
                            <td class="text-end">{{ number_format($sale->sscl_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($sale->vat_amount, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($sale->total, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('sales-report.receipt', $sale->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            {{-- Column 9ක් ඇති නිසා colspan 9 විය යුතුය --}}
                            <td colspan="9" class="text-center py-4 text-muted">No VAT registered customers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            {{-- colspan 4 + Amount columns 4 + Action 1 = 9 --}}
                            <td colspan="4" class="text-end">Total Summary (Filtered Data):</td>
                            <td class="text-end">LKR {{ number_format($totalSubtotal, 2) }}</td>
                            <td class="text-end text-dark">LKR {{ number_format($totalSscl, 2) }}</td>
                            <td class="text-end text-danger">LKR {{ number_format($totalVat, 2) }}</td>
                            <td class="text-end text-primary">LKR {{ number_format($totalGrand, 2) }}</td>
                            <td></td> {{-- Action column එක සඳහා හිස් cell එකක් --}}
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-3">
                {{ $sales->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
