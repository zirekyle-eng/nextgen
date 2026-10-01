<?php

namespace App\Models;

use App\User;
use Eloquent;

class PaymentRecord extends Eloquent
{
    protected $fillable =['student_id', 'payment_id', 'amt_paid', 'year', 'paid', 'balance', 'ref_no', 'status', 'stripe_payment_intent_id', 'transaction_reference', 'rejection_reason', 'confirmed_at'];

    protected $casts = [
        'confirmed_at' => 'datetime'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function receipt()
    {
        return $this->hasMany(Receipt::class, 'pr_id');
    }

    // Status helpers
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}
