<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\PaymentRecord;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // عرض الدفعات المستحقة والمدفوعة للوالد
    public function pendingPayments()
    {
        $user = auth()->user();
        $students = $user->my_students()->with('user')->get();
        
        $pendingPayments = collect();
        $paidPayments = collect();
        
        foreach($students as $student) {
            // جلب الدفعات المستحقة (pending)
            $pending = PaymentRecord::where('student_id', $student->user_id)
                ->whereIn('status', ['pending', 'paid', 'rejected'])
                ->with('payment', 'student')
                ->get();
            $pendingPayments = $pendingPayments->merge($pending);
            
            // جلب الدفعات المكتملة (confirmed)
            $paid = PaymentRecord::where('student_id', $student->user_id)
                ->where('status', 'confirmed')
                ->with('payment', 'student')
                ->orderBy('confirmed_at', 'desc')
                ->get();
            $paidPayments = $paidPayments->merge($paid);
        }

        return view('pages.parent.payments.pending', [
            'payments' => $pendingPayments,
            'paidPayments' => $paidPayments
        ]);
    }

    // صفحة الدفع الإلكترونية
    public function showCheckout(PaymentRecord $paymentRecord)
    {
        // تأكد أن الدفعة للطالب الخاص بالوالد
        $user = auth()->user();
        $studentIds = $user->my_students()->pluck('user_id')->toArray();
        
        if(!in_array($paymentRecord->student_id, $studentIds)) {
            return back()->with('flash_danger', 'Unauthorized access');
        }

        return view('pages.parent.payments.checkout', [
            'paymentRecord' => $paymentRecord,
            'stripeKey' => config('services.stripe.public')
        ]);
    }

    // معالجة الدفع
    public function processPayment(Request $req, PaymentRecord $paymentRecord)
    {
        try {
            $user = auth()->user();
            $studentIds = $user->my_students()->pluck('user_id')->toArray();
            
            if(!in_array($paymentRecord->student_id, $studentIds)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // استخدام amount من payment بدل balance
            $amount = $paymentRecord->payment->amount ?? $paymentRecord->balance;
            $amountInCents = intval($amount * 100);
            
            if ($amountInCents < 50) {
                return response()->json([
                    'error' => 'Minimum payment amount is $0.50. Current amount: $' . number_format($amount, 2)
                ], 400);
            }

            // إنشاء payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'metadata' => [
                    'payment_record_id' => $paymentRecord->id,
                    'student_id' => $paymentRecord->student_id,
                    'payment_id' => $paymentRecord->payment_id
                ]
            ]);

            // حفظ payment intent ID
            $paymentRecord->update([
                'stripe_payment_intent_id' => $paymentIntent->id
            ]);

            return response()->json(['clientSecret' => $paymentIntent->client_secret]);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // تأكيد الدفع (webhook from Stripe)
    public function confirmPayment(Request $req)
    {
        try {
            $paymentRecord = PaymentRecord::where('stripe_payment_intent_id', $req->payment_intent_id)->first();
            
            if(!$paymentRecord) {
                return response()->json(['error' => 'Payment record not found'], 404);
            }

            // احسب المبلغ المدفوع (من payment amount بدل balance)
            $amountPaid = $paymentRecord->payment->amount ?? $paymentRecord->balance ?? 0;
            
            // احسب الرصيد المتبقي
            $currentBalance = $paymentRecord->balance ?? 0;
            $newBalance = max(0, $currentBalance - $amountPaid);

            // تحديث حالة الدفعة إلى "paid"
            $paymentRecord->update([
                'status' => 'paid',
                'transaction_reference' => $req->charge_id ?? $req->payment_intent_id,
                'amt_paid' => $amountPaid,
                'balance' => $newBalance,
                'paid' => 1
            ]);

            return response()->json(['success' => true]);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // الموافقة على الدفعة (من قبل الإدمن)
    public function adminConfirm(PaymentRecord $paymentRecord)
    {
        if(!Qs::userIsTeamAccount() && !Qs::userIsTeamSA()) {
            return back()->with('flash_danger', 'Unauthorized access');
        }

        $paymentRecord->update([
            'status' => 'confirmed',
            'paid' => 1,
            'confirmed_at' => now()
        ]);

        return back()->with('flash_success', 'Payment confirmed successfully');
    }

    // رفض الدفعة (من قبل الإدمن)
    public function adminReject(Request $req, PaymentRecord $paymentRecord)
    {
        $this->validate($req, [
            'reason' => 'required|string'
        ]);

        if(!Qs::userIsTeamAccount() && !Qs::userIsTeamSA()) {
            return back()->with('flash_danger', 'Unauthorized access');
        }

        $paymentRecord->update([
            'status' => 'rejected',
            'rejection_reason' => $req->reason
        ]);

        return back()->with('flash_success', 'Payment rejected successfully');
    }

    // عرض جميع الدفعات المعلقة (للإدمن)
    public function adminPendingPayments()
    {
        if(!Qs::userIsTeamAccount() && !Qs::userIsTeamSA()) {
            return back()->with('flash_danger', 'Unauthorized access');
        }

        $payments = PaymentRecord::where('status', 'paid')
            ->with('student', 'payment')
            ->paginate(20);

        return view('pages.support_team.payments.pending_confirmation', ['payments' => $payments]);
    }
}
