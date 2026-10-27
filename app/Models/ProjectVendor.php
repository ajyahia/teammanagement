<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectVendor extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'phone',
        'agreed_cost',
        'is_recurring',
    ];

    protected $casts = [
        'agreed_cost' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payments()
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function getPaidTotalAttribute()
    {
        return (float) $this->payments->where('status', 'paid')->sum('amount');
    }

    public function getPendingTotalAttribute()
    {
        return (float) $this->payments->where('status', '!=', 'paid')->sum('amount');
    }
}
