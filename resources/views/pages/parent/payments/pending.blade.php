@extends('layouts.master')
@section('page_title', 'Payment Records')
@section('content')

<style>
    :root {
        --pay-bg: #f3f7fc;
        --pay-card: #ffffff;
        --pay-line: #dbe6f2;
        --pay-text: #123452;
        --pay-sub: #6e8298;
        --pay-navy: #123d7a;
        --pay-red: #c1263e;
    }

    .pay-shell {
        background: linear-gradient(145deg, #f8fbff, var(--pay-bg));
        border: 1px solid #d9e5f1;
        border-radius: 14px;
        padding: 12px;
    }

    .pay-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .pay-title {
        margin: 0;
        color: var(--pay-text);
        font-size: 1.03rem;
        font-weight: 800;
    }

    .pay-subtitle {
        margin: 2px 0 0;
        color: var(--pay-sub);
        font-size: .75rem;
    }

    .pay-stats {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .pay-pill {
        border-radius: 999px;
        border: 1px solid #d8e3ef;
        background: #fff;
        color: #2a4b6d;
        padding: .28rem .68rem;
        font-size: .7rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .pay-pill strong { color: var(--pay-navy); }

    .pay-tabs {
        display: flex;
        gap: 6px;
        margin-bottom: 10px;
    }

    .pay-tab-btn {
        border: 1px solid #d6e3f0;
        background: #f8fbff;
        color: #4f6881;
        border-radius: 9px;
        padding: .42rem .8rem;
        font-size: .74rem;
        font-weight: 800;
        cursor: pointer;
    }

    .pay-tab-btn.active {
        background: linear-gradient(120deg, var(--pay-navy), #1954a1);
        border-color: #194f96;
        color: #fff;
    }

    .pay-tab {
        display: none;
    }

    .pay-tab.active {
        display: block;
        animation: payFade .18s ease-in;
    }

    @keyframes payFade {
        from { opacity: .7; transform: translateY(2px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .pay-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .pay-card {
        background: var(--pay-card);
        border: 1px solid var(--pay-line);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(18, 61, 122, .06);
        display: flex;
        flex-direction: column;
    }

    .pay-card-head {
        padding: .6rem .74rem;
        border-bottom: 1px solid var(--pay-line);
        background: linear-gradient(90deg, #f9fcff, #f1f7ff);
    }

    .pay-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        border: 1px solid transparent;
        padding: .2rem .52rem;
        font-size: .63rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .pay-status-pending { background: #fff0da; color: #8e6512; border-color: #f3ddb0; }
    .pay-status-paid { background: #e5f3ff; color: #1d5f96; border-color: #cbe2f8; }
    .pay-status-rejected { background: #fde9ed; color: #9f2439; border-color: #f2ced6; }
    .pay-status-confirmed { background: #e9f6ef; color: #1f7a4a; border-color: #cde8d8; }

    .pay-card-title {
        margin: 0 0 4px;
        color: #173d63;
        font-size: .82rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .pay-student {
        color: #6f859b;
        font-size: .7rem;
        font-weight: 600;
    }

    .pay-body {
        padding: .66rem .74rem;
        flex: 1;
    }

    .pay-amount-box {
        border: 1px solid #dce8f4;
        border-radius: 8px;
        background: #f8fbff;
        padding: .45rem .55rem;
        margin-bottom: 8px;
    }

    .pay-amount-label {
        display: block;
        color: #7590a8;
        font-size: .62rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .35px;
        margin-bottom: 2px;
    }

    .pay-amount-value {
        color: var(--pay-navy);
        font-size: 1rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .pay-row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        color: #587189;
        font-size: .69rem;
        margin-bottom: 4px;
    }

    .pay-row strong { color: #2f4f70; }

    .pay-foot {
        border-top: 1px solid var(--pay-line);
        padding: .58rem .74rem;
        background: #fbfdff;
    }

    .pay-btn {
        width: 100%;
        border: none;
        border-radius: 8px;
        font-size: .73rem;
        font-weight: 800;
        padding: .45rem .6rem;
        text-align: center;
        text-decoration: none;
        display: inline-block;
    }

    .pay-btn-main {
        background: linear-gradient(120deg, var(--pay-navy), #1954a1);
        color: #fff;
    }

    .pay-btn-main:hover { color: #fff; text-decoration: none; background: linear-gradient(120deg, #103369, #164788); }

    .pay-btn-retry {
        background: linear-gradient(120deg, var(--pay-red), #a81f35);
        color: #fff;
    }

    .pay-btn-retry:hover { color: #fff; text-decoration: none; background: linear-gradient(120deg, #ac2137, #901b2e); }

    .pay-note {
        border: 1px solid #dbe7f3;
        border-left: 4px solid #5f7fa3;
        border-radius: 8px;
        background: #f8fbff;
        color: #4f6982;
        font-size: .7rem;
        font-weight: 600;
        padding: .42rem .52rem;
    }

    .pay-note.success { border-left-color: #2d9361; background: #f0faf4; color: #2f7c55; }
    .pay-note.warn { border-left-color: #2b6fb0; background: #f0f7ff; color: #2b628e; }
    .pay-note.error { border-left-color: #b3283d; background: #fef1f3; color: #8f2234; }

    .pay-empty {
        border: 1px dashed #cfdceb;
        border-radius: 12px;
        background: #fff;
        text-align: center;
        padding: 1.8rem .8rem;
        color: #6f8399;
        font-size: .82rem;
    }

    .pay-empty i {
        display: block;
        font-size: 1.9rem;
        color: #aebccc;
        margin-bottom: 7px;
    }

    @media (max-width: 1200px) {
        .pay-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 768px) {
        .pay-top { flex-direction: column; align-items: stretch; }
        .pay-grid { grid-template-columns: 1fr; }
        .pay-tabs { display: grid; grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="container-fluid py-3">
    @include('includes.alerts')

    <div class="pay-shell">
        <div class="pay-top">
            <div>
                <h4 class="pay-title"><i class="fas fa-credit-card"></i> Parent Payments</h4>
                <p class="pay-subtitle">Track pending requests and completed Stripe payments</p>
            </div>
            <div class="pay-stats">
                <span class="pay-pill">Pending: <strong>{{ $payments->where('status', 'pending')->count() }}</strong></span>
                <span class="pay-pill">Waiting Confirm: <strong>{{ $payments->where('status', 'paid')->count() }}</strong></span>
                <span class="pay-pill">Rejected: <strong>{{ $payments->where('status', 'rejected')->count() }}</strong></span>
                <span class="pay-pill">Completed: <strong>{{ $paidPayments->count() }}</strong></span>
            </div>
        </div>

        <div class="pay-tabs">
            <button class="pay-tab-btn active" onclick="switchPaymentTab(event, 'pending-tab')"><i class="fas fa-hourglass-half"></i> Pending</button>
            <button class="pay-tab-btn" onclick="switchPaymentTab(event, 'completed-tab')"><i class="fas fa-check-circle"></i> Completed</button>
        </div>

        <div id="pending-tab" class="pay-tab active">
            @if($payments->count() > 0)
                <div class="pay-grid">
                    @foreach($payments as $payment)
                        <div class="pay-card">
                            <div class="pay-card-head">
                                <span class="pay-status pay-status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                                <h6 class="pay-card-title">{{ $payment->payment->title }}</h6>
                                <div class="pay-student"><i class="fas fa-user-circle"></i> {{ $payment->student->name }}</div>
                            </div>

                            <div class="pay-body">
                                <div class="pay-amount-box">
                                    <span class="pay-amount-label">Amount Due</span>
                                    <div class="pay-amount-value">${{ number_format($payment->payment->amount, 2) }}</div>
                                </div>

                                <div class="pay-row">
                                    <span>Original</span>
                                    <strong>${{ number_format($payment->payment->amount, 2) }}</strong>
                                </div>
                                <div class="pay-row">
                                    <span>Already Paid</span>
                                    <strong>${{ number_format($payment->amt_paid ?? 0, 2) }}</strong>
                                </div>
                            </div>

                            <div class="pay-foot">
                                @if($payment->status === 'pending')
                                    <a href="{{ route('stripe.checkout', $payment) }}" class="pay-btn pay-btn-main">
                                        <i class="fas fa-lock"></i> Pay Now
                                    </a>
                                @elseif($payment->status === 'paid')
                                    <div class="pay-note warn">
                                        <i class="fas fa-info-circle"></i> Waiting for admin confirmation.
                                    </div>
                                @elseif($payment->status === 'rejected')
                                    <div class="pay-note error mb-2">
                                        <strong>Rejected:</strong> {{ $payment->rejection_reason }}
                                    </div>
                                    <a href="{{ route('stripe.checkout', $payment) }}" class="pay-btn pay-btn-retry">
                                        <i class="fas fa-redo"></i> Try Again
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="pay-empty">
                    <i class="fas fa-check-circle"></i>
                    No pending payments right now.
                </div>
            @endif
        </div>

        <div id="completed-tab" class="pay-tab">
            @if($paidPayments->count() > 0)
                <div class="pay-grid">
                    @foreach($paidPayments as $payment)
                        <div class="pay-card">
                            <div class="pay-card-head">
                                <span class="pay-status pay-status-confirmed"><i class="fas fa-check"></i> Confirmed</span>
                                <h6 class="pay-card-title">{{ $payment->payment->title }}</h6>
                                <div class="pay-student"><i class="fas fa-user-circle"></i> {{ $payment->student->name }}</div>
                            </div>

                            <div class="pay-body">
                                <div class="pay-amount-box">
                                    <span class="pay-amount-label">Amount Paid</span>
                                    <div class="pay-amount-value">${{ number_format($payment->payment->amount, 2) }}</div>
                                </div>
                                <div class="pay-row">
                                    <span>Payment Date</span>
                                    <strong>{{ $payment->confirmed_at ? $payment->confirmed_at->format('M d, Y') : 'N/A' }}</strong>
                                </div>
                                <div class="pay-row">
                                    <span>Transaction</span>
                                    <strong>{{ $payment->transaction_reference ? substr($payment->transaction_reference, 0, 16) . '...' : 'N/A' }}</strong>
                                </div>
                            </div>

                            <div class="pay-foot">
                                <div class="pay-note success">
                                    <i class="fas fa-check-circle"></i> Payment completed and confirmed.
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="pay-empty">
                    <i class="fas fa-history"></i>
                    No completed payments yet.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function switchPaymentTab(event, tabId) {
        const tabs = document.querySelectorAll('.pay-tab');
        tabs.forEach(tab => tab.classList.remove('active'));

        const buttons = document.querySelectorAll('.pay-tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));

        const targetTab = document.getElementById(tabId);
        if (targetTab) {
            targetTab.classList.add('active');
        }

        const clickedBtn = event.target.closest('.pay-tab-btn');
        if (clickedBtn) {
            clickedBtn.classList.add('active');
        }
    }
</script>

@endsection

