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
        'employee_id',
        'agreed_price',
        'cost',
        'billing_type',
        'subscription_status',
        'status',
        'notes',
        'start_date',
        'deadline',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'agreed_price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function getProfitAttribute()
    {
        return $this->agreed_price - $this->cost;
    }

    public function payments()
    {
        return $this->hasMany(ProjectPayment::class);
    }
}
