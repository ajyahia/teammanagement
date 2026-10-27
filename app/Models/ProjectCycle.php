<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCycle extends Model
{
    protected $fillable = [
        'project_id',
        'billing_date',
        'amount',
        'is_paid',
        'paid_at',
    ];

    protected $casts = [
        'billing_date' => 'date',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payments()
    {
        return $this->hasMany(ProjectPayment::class, 'cycle_id');
    }

    /**
     * Recalculate is_paid from the cycle's paid payments.
     */
    public function syncPaidStatus()
    {
        $paidTotal = (float) $this->payments()->where('status', 'paid')->sum('amount');
        $isFullyPaid = $paidTotal >= (float) $this->amount && (float) $this->amount > 0;

        $this->update([
            'is_paid' => $isFullyPaid,
            'paid_at' => $isFullyPaid ? ($this->paid_at ?? now()) : null,
        ]);
    }

    public function getTotalPaidAttribute()
    {
        $paidViaPayments = (float) $this->payments()->where('status', 'paid')->sum('amount');
        if ($paidViaPayments > 0 || $this->payments()->exists()) {
            return $paidViaPayments;
        }
        return $this->is_paid ? (float) $this->amount : 0.0;
    }

    public function getDueAmountAttribute()
    {
        return max(0, (float) $this->amount - $this->total_paid);
    }

    public function getPaymentStatusAttribute()
    {
        $paid = $this->total_paid;
        $total = (float) $this->amount;

        if ($paid >= $total && $total > 0) {
            return 'paid';
        }
        if ($paid > 0) {
            return 'partial';
        }
        return 'unpaid';
    }
}
