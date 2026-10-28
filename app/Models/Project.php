<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
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
        'ended_at',
        'page_count',
        'price_per_page',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'ended_at' => 'date',
        'agreed_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    /** Statuses of a project that is still being worked on. */
    public const ACTIVE_STATUSES = ['pending', 'in_progress'];

    public function isSubscription(): bool
    {
        return in_array($this->billing_type, ['monthly', 'yearly']);
    }

    /** First month the client works with us. */
    public function periodStart(): Carbon
    {
        return Carbon::parse($this->start_date ?? $this->created_at ?? now())->startOfMonth();
    }

    /**
     * Last month the client works with us.
     * Closed projects end when they were closed; running ones go on to today
     * (or to the deadline when it is still ahead); stopped subscriptions end on their last billing.
     */
    public function periodEnd(): Carbon
    {
        $now = now()->startOfMonth();
        $start = $this->periodStart();

        if ($this->ended_at) {
            $end = $this->ended_at->copy()->startOfMonth();
        } elseif ($this->isSubscription()) {
            // Stopped subscriptions with a recorded stop keep running to today: the stopped months
            // are skipped by activeMonths(). Old ones with no history end on their last billing.
            $legacyStopped = $this->subscription_status === 'stopped' && $this->pauses->isEmpty();
            $running = in_array($this->status, self::ACTIVE_STATUSES) && !$legacyStopped;
            $lastCycle = $this->cycles->max('billing_date');
            $end = $running ? $now : ($lastCycle ? Carbon::parse($lastCycle)->startOfMonth() : $start);
        } else {
            $planned = Carbon::parse($this->deadline ?? $start)->startOfMonth();
            $end = in_array($this->status, self::ACTIVE_STATUSES) && $planned->lt($now) ? $now : $planned;
        }

        return $end->lt($start) ? $start->copy() : $end;
    }

    /** @return string[] 'Y-m' for every month of the period (capped at 20 years). */
    public function activeMonths(): array
    {
        $months = [];
        $end = $this->periodEnd();
        for ($d = $this->periodStart(), $i = 0; $d->lte($end) && $i < 240; $d->addMonth(), $i++) {
            $ym = $d->format('Y-m');
            if (!$this->isPausedIn($ym)) {
                $months[] = $ym;
            }
        }

        return $months;
    }

    /**
     * A month is skipped when the subscription was stopped before it began and started again
     * after it ended. The month it was stopped in and the month it resumed in still count.
     */
    private function isPausedIn(string $ym): bool
    {
        foreach ($this->pauses as $pause) {
            $from = $pause->paused_at->format('Y-m');
            $to = $pause->resumed_at?->format('Y-m');
            if ($from < $ym && ($to === null || $to > $ym)) {
                return true;
            }
        }

        return false;
    }

    public function pauses()
    {
        return $this->hasMany(ProjectPause::class)->orderBy('paused_at');
    }

    /** Stop a subscription. It can be started again later; the history is kept. */
    public function stopSubscription(): void
    {
        if ($this->subscription_status === 'stopped') {
            return;
        }
        $this->update(['subscription_status' => 'stopped']);
        $this->pauses()->create(['paused_at' => today()]);
    }

    /** Start a stopped subscription again and bill the current month. */
    public function resumeSubscription(): void
    {
        if ($this->subscription_status === 'active') {
            return;
        }
        $this->update(['subscription_status' => 'active']);
        $this->pauses()->whereNull('resumed_at')->update(['resumed_at' => today()]);

        $month = now()->startOfMonth();
        $billed = $this->billing_type === 'yearly'
            ? $this->cycles()->whereYear('billing_date', $month->year)->exists()
            : $this->cycles()->whereYear('billing_date', $month->year)->whereMonth('billing_date', $month->month)->exists();
        if (!$billed) {
            $this->cycles()->create(['billing_date' => $month, 'amount' => $this->agreed_price, 'is_paid' => false]);
        }
    }

    /** Split an amount evenly over months; the last month takes the rounding remainder. */
    private function spread(float $total, array $months): array
    {
        $each = round($total / max(1, count($months)), 2);
        $left = round($total, 2);
        $out = [];
        foreach ($months as $i => $ym) {
            $amount = $i === count($months) - 1 ? $left : $each;
            $out[$ym] = $amount;
            $left = round($left - $amount, 2);
        }

        return $out;
    }

    /**
     * Revenue earned per month ('Y-m' => amount), by the months the client works with us,
     * whatever the date the money was received. Needs cycles loaded.
     */
    public function revenueByMonth(): array
    {
        $months = $this->activeMonths();
        $price = (float) $this->agreed_price;

        if (!$this->isSubscription()) {
            return $this->spread($price, $months);
        }

        $out = [];
        if ($this->billing_type === 'yearly') {
            foreach ($months as $ym) {
                $out[$ym] = round($price / 12, 2);
            }

            return $out;
        }

        // Monthly: the billed cycle amount when there is one, otherwise the agreed monthly price
        $cycles = $this->cycles->groupBy(fn($c) => $c->billing_date->format('Y-m'))->map->sum('amount');
        foreach ($months as $ym) {
            $out[$ym] = isset($cycles[$ym]) ? (float) $cycles[$ym] : $price;
        }

        return $out;
    }

    /** Cost of the external workers per month ('Y-m' => amount), spread like the revenue. Needs vendors loaded. */
    public function vendorCostByMonth(): array
    {
        $months = $this->activeMonths();
        $out = array_fill_keys($months, 0.0);

        foreach ($this->vendors as $vendor) {
            $share = $vendor->is_recurring
                ? array_fill_keys($months, (float) $vendor->agreed_cost)
                : $this->spread((float) $vendor->agreed_cost, $months);
            foreach ($share as $ym => $amount) {
                $out[$ym] += $amount;
            }
        }

        return $out;
    }
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
        return $this->belongsToMany(User::class, 'project_user')->withPivot('service_id');
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
