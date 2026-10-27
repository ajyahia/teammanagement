<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPartner extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'phone',
        'share_percent',
        'committed_capital',
    ];

    protected $casts = [
        'share_percent' => 'decimal:2',
        'committed_capital' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function contributions()
    {
        return $this->hasMany(PartnerContribution::class);
    }

    public function getPaidCapitalAttribute()
    {
        return (float) $this->contributions->where('status', 'paid')->sum('amount');
    }

    public function getRemainingCapitalAttribute()
    {
        return max(0, (float) $this->committed_capital - $this->paid_capital);
    }

    public function getProfitShareAttribute()
    {
        return round($this->project->net_profit * (float) $this->share_percent / 100, 2);
    }
}
