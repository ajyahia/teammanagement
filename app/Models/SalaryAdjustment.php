<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryAdjustment extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'type',
        'amount',
        'bonus',
        'deduction',
        'notes',
    ];

    /**
     * Get the user that owns the adjustment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
