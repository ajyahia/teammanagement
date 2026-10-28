<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPause extends Model
{
    protected $fillable = ['project_id', 'paused_at', 'resumed_at'];

    protected $casts = [
        'paused_at' => 'date',
        'resumed_at' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
