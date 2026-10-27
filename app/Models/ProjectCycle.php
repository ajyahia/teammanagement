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

    protected static function booted(): void
    {
        // A new billing cycle also schedules the cost of every recurring external worker
        static::created(function (ProjectCycle $cycle) {
            $vendors = ProjectVendor::where('project_id', $cycle->project_id)->where('is_recurring', true)->get();
            foreach ($vendors as $vendor) {
                $vendor->payments()->create([
                    'cycle_id' => $cycle->id,
                    'title' => $cycle->billing_date->format('M Y'),
                    'amount' => $vendor->agreed_cost,
                    'due_date' => $cycle->billing_date->toDateString(),
                    'status' => 'pending',
                ]);
            }
        });
    }

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
