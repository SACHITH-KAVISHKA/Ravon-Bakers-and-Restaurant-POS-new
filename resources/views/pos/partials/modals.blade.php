<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-credit-card-2-back me-2"></i>PAYMENT PROCESSING</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="row g-4">
                    <div class="col-md-5 border-end">
                        <h6 class="fw-bold text-secondary mb-3 text-uppercase small">Select Payment Type</h6>
                        <div class="d-grid gap-2 mb-4">
                            <button class="btn btn-outline-primary py-3 fw-bold payment-method-btn" id="btn-CASH" onclick="selectPaymentMethod('CASH')">CASH</button>
                            <button class="btn btn-outline-primary py-3 fw-bold payment-method-btn" id="btn-CARD" onclick="selectPaymentMethod('CARD')">CARD</button>
                            <button class="btn btn-outline-primary py-3 fw-bold payment-method-btn" id="btn-CARD_CASH" onclick="selectPaymentMethod('CARD & CASH')">CARD & CASH</button>
                            <button class="btn btn-outline-danger py-3 fw-bold payment-method-btn" id="btn-CREDIT" onclick="selectPaymentMethod('CREDIT')">CREDIT</button>
                        </div>

                        <h6 class="fw-bold text-secondary mb-3 text-uppercase small">Number Pad</h6>
                        <div class="bg-white p-3 rounded shadow-sm">
                            <div class="row g-2">
                                @foreach([7, 8, 9, 4, 5, 6, 1, 2, 3, 0, '.'] as $num)
                                    <div class="col-4"><button class="btn btn-dark w-100 py-3 fs-4" onclick="pressNumpad('{{ $num }}')">{{ $num }}</button></div>
                                @endforeach
                                <div class="col-4"><button class="btn btn-warning w-100 py-3 fs-4" onclick="pressNumpad('BACK')"><i class="bi bi-backspace"></i></button></div>
                                <div class="col-12"><button class="btn btn-danger w-100 py-2 fw-bold" onclick="pressNumpad('CLEAR')">C</button></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="bg-white p-4 rounded shadow-sm border h-100">
                            <div class="total-display mb-4 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between mb-2"><span>Sub Total</span><span id="modal-subtotal">0.00</span></div>
                                <div class="d-flex justify-content-between fw-bold h4 text-primary border-top pt-2">
                                    <span>Total ———></span><span id="modal-total-display">0.00</span>
                                </div>
                            </div>

                            <div class="payment-details mb-4">
                                <div class="d-flex justify-content-between mb-2"><span>Card</span><span id="display-card-amount">0.00</span></div>
                                <div class="d-flex justify-content-between mb-2"><span>Cash</span><span id="display-cash-amount">0.00</span></div>
                                <div class="d-flex justify-content-between fw-bold text-success border-top pt-2 h5">
                                    <span>Balance ———></span><span id="display-balance">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold text-danger mt-2 h5">
                                    <span>Credit ———></span><span id="display-credit">0.00</span>
                                </div>
                            </div>

                            <div class="input-section mt-auto">
                                <div class="mb-3" id="cash-input-group">
                                    <label class="small fw-bold text-muted">CASH AMOUNT</label>
                                    <input type="number" class="form-control form-control-lg text-center fw-bold bg-warning bg-opacity-10" id="modal-cash-input" readonly>
                                </div>
                                <div class="mb-3" id="card-input-group" style="display:none;">
                                    <label class="small fw-bold text-muted">CARD AMOUNT</label>
                                    <input type="number" class="form-control form-control-lg text-center fw-bold bg-info bg-opacity-10" id="modal-card-input" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal"><i class="bi bi-arrow-left me-2"></i>Back</button>
                <button class="btn btn-success px-5 py-2 fw-bold" onclick="finalizeTransaction()" id="print-receipt-btn" disabled>
                    <i class="bi bi-check-circle me-2"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>
