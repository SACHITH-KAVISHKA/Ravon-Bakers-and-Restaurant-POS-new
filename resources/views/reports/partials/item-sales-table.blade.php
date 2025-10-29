<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table"></i> Item Sales Summary
                    <span class="badge bg-light text-dark ms-2">{{ count($salesData) }} items</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 sales-table">
                        <thead>
                            <tr>
                                <th class="border-0 px-3 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff;">Item No</th>
                                <th class="border-0 px-3 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff;">Item Name</th>
                                <th class="border-0 px-3 py-3 text-center" style="background-color: #e3f2fd; color: #1976d2;">
                                    Total Qty
                                </th>
                                @foreach($branches as $branch)
                                    <th class="border-0 px-3 py-3 text-center" style="background-color: #fff3e0; color: #e65100;">
                                        {{ $branch->name }}
                                    </th>
                                @endforeach
                                <th class="border-0 px-3 py-3 text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($salesData) === 0)
                                <tr>
                                    <td colspan="{{ 4 + count($branches) }}" class="text-center py-5">
                                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-3">No sales data available for the selected date range.</p>
                                    </td>
                                </tr>
                            @else
                                @foreach($salesData as $item)
                                    <tr class="{{ $item['total_quantity'] === 0 ? 'table-secondary' : '' }}">
                                        <td class="px-3 py-3" style="background-color: #f3e8ff;">
                                            <code class="text-dark" style="font-weight:700; color: #6a4bc0;">{{ $item['item_code'] }}</code>
                                        </td>
                                        <td class="px-3 py-3" style="background-color: #faf5ff;">
                                            <span class="fw-bold" style="color: #6a4bc0; font-size: 0.98rem;">{{ $item['item_name'] }}</span>
                                        </td>
                                        <td class="px-3 py-3 text-center" style="background-color: #f5f9ff;">
                                            <span class="badge badge-quantity" style="background-color: #2196f3; color: white;">
                                                {{ $item['total_quantity'] }}
                                            </span>
                                        </td>
                                        @foreach($branches as $branch)
                                            @php
                                                $qty = $item['branches'][$branch->name] ?? 0;
                                            @endphp
                                            <td class="px-3 py-3 text-center" style="background-color: #fffbf5;">
                                                @if($qty > 0)
                                                    <span class="badge badge-quantity" style="background-color: #ff9800; color: white;">
                                                        {{ $qty }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-quantity bg-secondary text-dark">
                                                        {{ $qty }}
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-3 py-3 text-center" style="background-color: #f8f9fa;">
                                            <button class="btn btn-sm btn-outline-primary view-details-btn" 
                                                    data-item-id="{{ $item['item_id'] }}"
                                                    data-item-code="{{ $item['item_code'] }}"
                                                    data-item-name="{{ $item['item_name'] }}"
                                                    title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
