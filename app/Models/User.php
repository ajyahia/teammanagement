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
    'salary'
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
        ];
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
}
