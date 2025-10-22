@php
// Load branches for the dropdown. It's small and safe to load here for now.
$branches = \App\Models\Branch::orderBy('name')->where('id', '!=', 1)->get();
@endphp

<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">Branch Prices</h6>
    </div>
    <div class="card-body">
        <div id="branchPricesContainer" data-branches='@json($branches->map(function($b){ return [' id'=> $b->id, 'name' => $b->name]; }))'></div>

        <div class="mt-3">
            <button type="button" id="addBranchPriceBtn" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Add Price
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const container = document.getElementById('branchPricesContainer');
        const branches = JSON.parse(container.getAttribute('data-branches') || '[]');

        function makeRow(index, selectedBranchId = '', price = '') {
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center mb-2 branch-price-row';
            row.innerHTML = `
                <div class="col-4 col-md-4">
                    <select name="branch_prices[${index}][branch_id]" class="form-select form-select-sm" required>
                        <option value="">Select Branch</option>
                        ${branches.map(b => `<option value="${b.id}" ${selectedBranchId==b.id? 'selected':''}>${b.name}</option>`).join('')}
                    </select>
                </div>
                <div class="col-3 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">LKR</span>
                        <input type="number" step="0.01" min="0" name="branch_prices[${index}][price]" class="form-control form-control-sm" value="${price}">
                    </div>
                </div>
                <div class="col-2 col-md-2">
                    <button type="button" class="btn btn-sm btn-danger remove-branch-price">Remove</button>
                </div>
            `;
            return row;
        }

        function reindex() {
            const rows = container.querySelectorAll('.branch-price-row');
            rows.forEach((r, i) => {
                const selects = r.querySelectorAll('select');
                const inputs = r.querySelectorAll('input[type="number"]');
                selects.forEach(s => s.name = `branch_prices[${i}][branch_id]`);
                inputs.forEach(inp => inp.name = `branch_prices[${i}][price]`);
            });
        }

        document.getElementById('addBranchPriceBtn').addEventListener('click', function() {
            const idx = container.querySelectorAll('.branch-price-row').length;
            container.appendChild(makeRow(idx));
        });

        container.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-branch-price')) {
                const row = e.target.closest('.branch-price-row');
                if (row) {
                    row.remove();
                    reindex();
                }
            }
        });

        // Prepopulate from server-rendered JSON if provided
        try {
            const existing = window.__branchPricesForItem || [];
            if (Array.isArray(existing) && existing.length > 0) {
                existing.forEach((bp, i) => {
                    container.appendChild(makeRow(i, bp.branch_id, bp.price));
                });
            }
        } catch (e) {
            console.error('Failed to prepopulate branch prices', e);
        }
    })();
</script>
@endpush