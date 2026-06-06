<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'year',
    'total_allowed',
    'used'
])]
class VacationBalance extends Model
{
    /**
     * Get the user that owns the vacation balance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
