<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'age',
    'job_title',
    'phone',
    'whatsapp',
    'email',
    'username',
    'password',
    'password_text',
    'role',
    'linkedin',
    'facebook',
    'instagram',
    'work_start_time',
    'work_end_time',
    'salary',
    'payment_type',
    'project_rate',
    'deactivated_at'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'work_start_time' => 'datetime:H:i:s',
            'work_end_time' => 'datetime:H:i:s',
            'deactivated_at' => 'date',
        ];
    }

    /**
     * Whether the employee is still active on the given date (defaults to today).
     * deactivated_at is the first day the employee no longer works.
     */
    public function isActive($date = null): bool
    {
        $date = \Carbon\Carbon::parse($date ?? today())->startOfDay();

        return $this->deactivated_at === null || $this->deactivated_at->gt($date);
    }

    /**
     * Scope: employees still active on the given date (defaults to today).
     */
    public function scopeActiveOn($query, $date = null)
    {
        $date = \Carbon\Carbon::parse($date ?? today())->toDateString();

        return $query->where(function ($q) use ($date) {
            $q->whereNull('deactivated_at')->orWhere('deactivated_at', '>', $date);
        });
    }

    /**
     * Scope: employees who were active for at least one day of the given month.
     */
    public function scopeActiveInMonth($query, $month, $year)
    {
        $monthStart = \Carbon\Carbon::create((int)$year, (int)$month, 1)->toDateString();

        return $query->where(function ($q) use ($monthStart) {
            $q->whereNull('deactivated_at')->orWhere('deactivated_at', '>', $monthStart);
        });
    }

    /**
     * Get the attendance records for the user.
     */
    public function attendanceRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the vacation balances for the user.
     */
    public function vacationBalances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VacationBalance::class);
    }

    /**
     * Get the salary adjustments for the user.
     */
    public function salaryAdjustments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalaryAdjustment::class);
    }

    /**
     * Get the salary payments for the user.
     */
    public function salaryPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    /**
     * Get the services associated with the user.
     */
    public function services(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * Get the projects associated with the user.
     */
    public function projects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')->withPivot('service_id');
    }
}
