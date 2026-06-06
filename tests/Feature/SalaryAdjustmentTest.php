<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SalaryAdjustment;

class SalaryAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_salary_adjustment_with_numeric_month_with_leading_zero(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'job_title' => 'Manager',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@test.com',
            'username' => 'employee',
            'job_title' => 'Developer',
            'phone' => '0987654321',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'salary' => 5000.00,
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->post('/admin/salaries', [
                'user_id' => $employee->id,
                'month' => '06', // leading zero
                'year' => '2026',
                'type' => 'bonus',
                'amount' => 150.00,
                'notes' => 'Test bonus',
            ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('salary_adjustments', [
            'user_id' => $employee->id,
            'month' => 6,
            'year' => 2026,
            'type' => 'bonus',
            'amount' => 150.00,
            'notes' => 'Test bonus',
        ]);
    }
}
