@extends('layouts.master')
@section('page_title', 'Payment Checkout')
@section('content')

<style>
    .checkout-container {
        max-width: 600px;
        margin: 40px auto;
    }

    .checkout-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        overflow: hidden;
    }

    .checkout-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .checkout-header h2 {
        margin: 0 0 10px 0;
        font-size: 24px;
    }

    .amount-display {
        font-size: 48px;
        font-weight: 800;
        margin: 20px 0;
        line-height: 1;
    }

    .checkout-body {
        padding: 30px;
    }

    .payment-summary {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 30px;
        border-left: 4px solid #667eea;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        border-top: 1px solid #ddd;
        padding-top: 10px;
        margin-top: 10px;
        font-weight: 700;
        font-size: 16px;
        color: #667eea;
    }

    .stripe-element {
        border: 1px solid #ddd;
        padding: 12px;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 20px;
        height: 40px;
    }

    .stripe-element:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-submit:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }

    .loading {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .student-info {
        background: #e3f2fd;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .student-info strong {
        color: #667eea;
    }

    .error-message {
        color: #dc3545;
        font-size: 14px;
        margin-bottom: 20px;
        padding: 12px;
        background: #f8d7da;
        border-radius: 8px;
        border-left: 4px solid #dc3545;
        display: none;
    }

    .success-message {
        color: #155724;
        font-size: 14px;
        margin-bottom: 20px;
        padding: 12px;
        background: #d4edda;
        border-radius: 8px;
        border-left: 4px solid #28a745;
        display: none;
    }
</style>

<div class="checkout-container">
    <div class="checkout-card">
        <div class="checkout-header">
            <h2>Secure Payment</h2>
            <p style="margin: 0; opacity: 0.9;">Powered by Stripe</p>
            <div class="amount-display">
                @php echo '$' . number_format($paymentRecord->payment->amount, 2); @endphp
            </div>
        </div>

        <div class="checkout-body">
            <div class="student-info">
                <strong>Student:</strong> {{ $paymentRecord->student->name }} <br>
                <strong>Payment:</strong> {{ $paymentRecord->payment->title }}
            </div>

            <div class="payment-summary">
                <div class="summary-row">
                    <span>Original Amount:</span>
                    <span>${{ number_format($paymentRecord->payment->amount , 2) }}</span>
                </div>
              
                <div class="summary-row">
                    <span>Amount Due:</span>
                    <span>${{ number_format($paymentRecord->payment->amount, 2) }}</span>
                </div>
            </div>

            <div id="error-message" class="error-message"></div>
            <div id="success-message" class="success-message">Payment processing... please wait.</div>

            <form id="payment-form">
                @csrf
                <div id="card-element" class="stripe-element"></div>
                <button type="submit" id="submit-btn" class="btn-submit">
                    <span id="button-text">Pay ${{ number_format($paymentRecord->payment->amount, 2) }}</span>
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ route('stripe.pending') }}" class="text-muted" style="text-decoration: none; font-size: 14px;">
                    ← Back to Payments
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    // Stripe integration
    const stripe = Stripe('{{ $stripeKey }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const form = document.getElementById('payment-form');
    const submitBtn = document.getElementById('submit-btn');
    const errorDiv = document.getElementById('error-message');
    const successDiv = document.getElementById('success-message');
    const buttonText = document.getElementById('button-text');

    // Handle card errors
    cardElement.addEventListener('change', (event) => {
        if (event.error) {
            errorDiv.textContent = event.error.message;
            errorDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'none';
        }
    });

    // Handle form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        submitBtn.disabled = true;
        buttonText.innerHTML = '<span class="loading"></span> Processing...';
        errorDiv.style.display = 'none';

        // Create payment intent
        try {
            const response = await fetch('{{ route("stripe.process", $paymentRecord) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({})
            });

            const data = await response.json();

            if (data.error) {
                errorDiv.textContent = data.error;
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                buttonText.textContent = 'Pay ${{ number_format($paymentRecord->payment->amount, 2) }}';
                return;
            }

            // Confirm payment with Stripe
            const { error, paymentIntent } = await stripe.confirmCardPayment(data.clientSecret, {
                payment_method: {
                    card: cardElement,
                    billing_details: { name: 'Customer' }
                }
            });

            if (error) {
                errorDiv.textContent = error.message;
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                buttonText.textContent = 'Pay ${{ number_format($paymentRecord->payment->amount, 2) }}';
            } else if (paymentIntent.status === 'succeeded') {
                // Payment successful
                successDiv.textContent = '✓ Payment successful! Redirecting...';
                successDiv.style.display = 'block';

                // Confirm on server
                const chargeId = paymentIntent.charges && paymentIntent.charges.data && paymentIntent.charges.data[0] 
                    ? paymentIntent.charges.data[0].id 
                    : paymentIntent.id;

                const confirmResponse = await fetch('{{ route("stripe.confirm") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({
                        payment_intent_id: paymentIntent.id,
                        charge_id: chargeId
                    })
                });

                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '{{ route("stripe.pending") }}';
                }, 2000);
            }
        } catch (error) {
            errorDiv.textContent = 'Payment processing failed: ' + error.message;
            errorDiv.style.display = 'block';
            submitBtn.disabled = false;
            buttonText.textContent = 'Pay ${{ number_format($paymentRecord->payment->amount, 2) }}';
        }
    });
</script>

@endsection
