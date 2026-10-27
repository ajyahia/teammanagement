<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    protected $fillable = [
        'project_vendor_id',
        'cycle_id',
        'title',
        'amount',
        'due_date',
        'paid_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(ProjectVendor::class, 'project_vendor_id');
    }
}
