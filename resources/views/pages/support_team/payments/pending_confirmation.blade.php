@extends('layouts.master')
@section('page_title', 'Pending Payment Confirmations')
@section('content')

<style>
    .confirmation-container {
        margin-top: 30px;
    }

    .payment-item {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 5px solid #667eea;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .payment-item:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .payment-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .payment-title {
        font-size: 16px;
        font-weight: 700;
        color: #333;
        margin: 0;
    }

    .payment-status {
        display: inline-block;
        padding: 5px 12px;
        background: #fff3cd;
        color: #856404;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .payment-details {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .detail {
        font-size: 14px;
    }

    .detail-label {
        color: #666;
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .detail-value {
        color: #333;
        font-size: 16px;
        font-weight: 700;
    }

    .amount-large {
        color: #667eea;
        font-size: 24px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .btn-confirm {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-reject {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(220, 53, 69, 0.3);
    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #333;
    }

    .modal-body {
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }

    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        resize: vertical;
        min-height: 100px;
    }

    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .modal-footer {
        display: flex;
        gap: 10px;
    }

    .btn-cancel {
        flex: 1;
        padding: 10px;
        background: #f0f0f0;
        color: #333;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-submit-reject {
        flex: 1;
        padding: 10px;
        background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #666;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 20px;
        opacity: 0.5;
    }
</style>

<div class="container-fluid confirmation-container">
    <div class="page-titles">
        <div class="row">
            <div class="col-sm-6">
                <h4>Pending Payment Confirmations</h4>
                <p style="color: #666; font-size: 14px;">Review and confirm or reject submitted payments</p>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('payments.index') }}" class="btn btn-outline-primary">
                    <i class="fa fa-arrow-left"></i> Back to Payments
                </a>
            </div>
        </div>
    </div>

    @include('includes.alerts')

    @if($payments->count() > 0)
        @foreach($payments as $payment)
            <div class="payment-item">
                <div class="payment-header">
                    <div>
                        <h4 class="payment-title">{{ $payment->payment->title }}</h4>
                        <small style="color: #999;">Student: <strong>{{ $payment->student->name }}</strong></small>
                    </div>
                    <span class="payment-status">Waiting Confirmation</span>
                </div>

                <div class="payment-details">
                    <div class="detail">
                        <div class="detail-label">Payment Amount</div>
                        <div class="detail-value amount-large">${{ number_format($payment->amt_paid , 2) }}</div>
                    </div>
                    <div class="detail">
                        <div class="detail-label">Transaction ID</div>
                        <div class="detail-value" style="font-size: 12px; word-break: break-all;">
                            {{ substr($payment->stripe_payment_intent_id, 0, 20) }}...
                        </div>
                    </div>
                    <div class="detail">
                        <div class="detail-label">Payment Date</div>
                        <div class="detail-value">{{ $payment->updated_at->format('M d, Y') }}</div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn-confirm" onclick="confirmPayment({{ $payment->id }})">
                        <i class="fa fa-check"></i> Confirm Payment
                    </button>
                    <button class="btn-reject" onclick="openRejectModal({{ $payment->id }})">
                        <i class="fa fa-times"></i> Reject
                    </button>
                </div>
            </div>
        @endforeach

        {{ $payments->links('pagination::bootstrap-4') }}
    @else
        <div class="card">
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fa fa-inbox"></i>
                </div>
                <h5>No Pending Confirmations</h5>
                <p>All payments have been reviewed.</p>
            </div>
        </div>
    @endif
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal">
    <div class="modal-content">
        <div class="modal-header">Reject Payment</div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Rejection Reason *</label>
                    <textarea name="reason" placeholder="Explain why you're rejecting this payment..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn-submit-reject">Reject Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentPaymentId = null;

    function confirmPayment(paymentId) {
        if(confirm('Are you sure you want to confirm this payment?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/stripe-payments/${paymentId}/confirm`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function openRejectModal(paymentId) {
        currentPaymentId = paymentId;
        document.getElementById('rejectModal').classList.add('active');
        document.getElementById('rejectForm').action = `/stripe-payments/${paymentId}/reject`;
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.remove('active');
        document.getElementById('rejectForm').reset();
    }

    // Close modal when clicking outside
    document.getElementById('rejectModal').addEventListener('click', (e) => {
        if(e.target.id === 'rejectModal') {
            closeRejectModal();
        }
    });
</script>

@endsection
