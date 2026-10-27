<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'service_id',
        'agreed_price',
        'paid_amount',
        'billing_type',
        'subscription_status',
        'status',
        'notes',
        'start_date',
        'deadline',
        'page_count',
        'price_per_page',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'agreed_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function employees()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    public function getProfitAttribute()
    {
        return collect([$this->agreed_price])->first();
    }

    public function getTotalRevenueAttribute()
    {
        if (in_array($this->billing_type, ['monthly', 'yearly'])) {
            return (float) $this->cycles->sum('amount');
        }
        return (float) $this->agreed_price;
    }

    public function getTotalPaidAttribute()
    {
        if (in_array($this->billing_type, ['monthly', 'yearly'])) {
            $paidPayments = (float) $this->payments->where('status', 'paid')->sum('amount');
            $directCyclesPaid = 0;
            foreach ($this->cycles as $cycle) {
                if ($cycle->is_paid && $cycle->payments->isEmpty()) {
                    $directCyclesPaid += (float) $cycle->amount;
                }
            }
            return $paidPayments + $directCyclesPaid;
        }

        // Incorporate both legacy paid_amount and new project payments
        return (float) $this->payments->where('status', 'paid')->sum('amount') + (float) $this->paid_amount;
    }

    public function getDueAmountAttribute()
    {
        return max(0, (float) $this->total_revenue - (float) $this->total_paid);
    }

    public function partners()
    {
        return $this->hasMany(ProjectPartner::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function vendors()
    {
        return $this->hasMany(ProjectVendor::class);
    }

    /** Money already paid out to external workers on this project. */
    public function getVendorPaidAttribute()
    {
        return (float) $this->vendors->sum(fn($v) => $v->paid_total);
    }

    /** The owner keeps whatever percentage the partners don't hold. */
    public function getOwnerSharePercentAttribute()
    {
        return max(0, round(100 - (float) $this->partners->sum('share_percent'), 2));
    }

    /** Realized profit: what clients paid minus project expenses and what was paid to external workers. */
    public function getNetProfitAttribute()
    {
        return round((float) $this->total_paid - (float) $this->expenses->sum('amount') - $this->vendor_paid, 2);
    }

    public function getOwnerProfitShareAttribute()
    {
        return round($this->net_profit * $this->owner_share_percent / 100, 2);
    }

    public function payments()
    {
        return $this->hasMany(ProjectPayment::class);
    }

    public function cycles()
    {
        return $this->hasMany(ProjectCycle::class);
    }
}
