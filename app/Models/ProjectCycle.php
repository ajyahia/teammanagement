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
}
