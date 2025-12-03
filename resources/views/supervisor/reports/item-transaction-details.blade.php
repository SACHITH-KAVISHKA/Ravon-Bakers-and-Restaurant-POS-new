@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; color: transparent;">
                    <i class="bi bi-journal-text" style="color: #667eea;"></i> Item Transaction Details
                </h1>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-filter me-1"></i>
            Filter Transactions
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('supervisor.reports.item-transaction') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="branch_id" class="form-label">Branch</label>
                        <select class="form-select" id="branch_id" name="branch_id" required>
                            <option value="" selected disabled>Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (request('branch_id') == $branch->id) ? 'selected' : '' }}>
                                    {{ $branch->name ?? $branch->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="item_id" class="form-label">Item</label>
                        <select class="form-select" id="item_id" name="item_id" required>
                            <option value="" selected disabled>Select Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ (request('item_id') == $item->id) ? 'selected' : '' }}>
                                    {{ $item->item_name }} ({{ $item->item_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" value="{{ request('from_date', date('Y-m-01')) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" value="{{ request('to_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Generate
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Only show table if a valid search has been performed --}}
    @if(isset($selectedItem) && isset($selectedBranch))
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-table me-1"></i>
            Transaction History for <strong>{{ $selectedItem->item_name }}</strong> at <strong>{{ $selectedBranch->name ?? $selectedBranch->display_name }}</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Performed By</th>
                            <th class="text-end">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 1. Opening Balance Row --}}
                        <tr class="table-secondary">
                            <td><strong>Before {{ request('from_date') }}</strong></td>
                            <td><strong>Opening Balance</strong></td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-end"><strong>{{ number_format($openingBalance ?? 0, 0) }}</strong></td>
                        </tr>

                        {{-- 2. Transactions Loop --}}
                        @php
                            $netChange = 0;
                            // If openingBalance isn't set, default to 0 to avoid errors
                            $runningBalance = $openingBalance ?? 0;
                        @endphp

                        @forelse($transactions as $transaction)
                            @php
                                $netChange += $transaction->quantity;
                                $runningBalance += $transaction->quantity;
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <span class="badge
                                        @if($transaction->type == 'Sale') bg-success
                                        @elseif($transaction->type == 'Wastage') bg-danger
                                        @elseif($transaction->type == 'Transfer In' || $transaction->type == 'Production') bg-primary
                                        @else bg-warning text-dark @endif">
                                        {{ $transaction->type }}
                                    </span>
                                </td>
                                <td>{{ $transaction->reference }}</td>
                                <td>{{ $transaction->performed_by }}</td>
                                <td class="text-end {{ $transaction->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity, 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No transactions found during this period.</td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- 3. Footer with Closing Balance --}}
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="4" class="text-end">Net Change in Period</td>
                            <td class="text-end">{{ $netChange > 0 ? '+' : '' }}{{ number_format($netChange, 0) }}</td>
                        </tr>
                        <tr class="table-dark">
                            <td colspan="4" class="text-end"><strong>Closing Balance</strong></td>
                            <td class="text-end"><strong>{{ number_format($runningBalance, 0) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
