<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <div class="text-muted">
            <i class="bi bi-calendar3"></i>
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Items</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $totalItems ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-seam fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Purchases</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $totalPurchases ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart-plus fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Stock Value</div>
                            <div class="h3 mb-0 font-weight-bold">Rs. {{ number_format($stockValue ?? 0, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-boxes fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Today's Sales</div>
                            <div class="h3 mb-0 font-weight-bold">Rs. {{ number_format($todaySales ?? 0, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-currency-dollar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <!-- <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-lightning-charge"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('items.create') }}" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i>
                                Add New Item
                            </a>
                        </div>
                   
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('pos.index') }}" class="btn btn-warning w-100">
                                <i class="bi bi-calculator"></i>
                                Open POS
                            </a>
                        </div>
                        @can('manage-users')
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('users.create') }}" class="btn btn-info w-100">
                                <i class="bi bi-person-plus"></i>
                                Add User
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div> -->
</x-app-layout>
