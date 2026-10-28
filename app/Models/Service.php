<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'default_price',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /** Services this one is made of (Marketing -> Video Editing + Design). */
    public function components()
    {
        return $this->belongsToMany(Service::class, 'service_components', 'service_id', 'component_service_id');
    }

    /** Composite services that include this one. */
    public function usedIn()
    {
        return $this->belongsToMany(Service::class, 'service_components', 'component_service_id', 'service_id');
    }

    /** The service itself followed by its components: the roles a project of this service needs. */
    public function roles()
    {
        return collect([$this])->concat($this->components)->unique('id')->values();
    }
}
