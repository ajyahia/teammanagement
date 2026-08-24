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
            return $this->cycles->sum('amount');
        }
        return $this->agreed_price;
    }

    public function getTotalPaidAttribute()
    {
        if (in_array($this->billing_type, ['monthly', 'yearly'])) {
            return $this->cycles->where('is_paid', true)->sum('amount');
        }
        return $this->paid_amount;
    }

    public function getDueAmountAttribute()
    {
        return $this->total_revenue - $this->total_paid;
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
