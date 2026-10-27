<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerContribution extends Model
{
    protected $fillable = [
        'project_partner_id',
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

    public function partner()
    {
        return $this->belongsTo(ProjectPartner::class, 'project_partner_id');
    }
}
