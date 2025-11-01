@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>
                    <i class="bi bi-receipt"></i> 
                    {{ $kot->type }} Details - {{ $kot->kot_no }}
                </h3>
                <div>
                    <a href="{{ route('kot.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('kot.print', $kot) }}" class="btn btn-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Instructions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kot->kotItems as $item)
                                <tr>
                                    <td><strong>{{ $item->item_name }}</strong></td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>LKR {{ number_format($item->unit_price, 2) }}</td>
                                    <td>LKR {{ number_format($item->total_price, 2) }}</td>
                                    <td>{{ $item->special_instructions ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="3">Total</th>
                                    <th colspan="2">LKR {{ number_format($kot->kotItems->sum('total_price'), 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>{{ $kot->type }} No:</th>
                            <td>{{ $kot->kot_no }}</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>
                                <span class="badge bg-{{ $kot->type === 'KOT' ? 'primary' : 'info' }}">
                                    {{ $kot->type }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Branch:</th>
                            <td>{{ $kot->branch->name }}</td>
                        </tr>
                        <tr>
                            <th>Waiter:</th>
                            <td>{{ $kot->user_name }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $kot->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($kot->prepared_at)
                        <tr>
                            <th>Prepared:</th>
                            <td>{{ $kot->prepared_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($kot->served_at)
                        <tr>
                            <th>Served:</th>
                            <td>{{ $kot->served_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($kot->completed_at)
                        <tr>
                            <th>Completed:</th>
                            <td>{{ $kot->completed_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>

                    @if($kot->notes)
                    <div class="alert alert-info mt-3">
                        <strong>Notes:</strong><br>
                        {{ $kot->notes }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
