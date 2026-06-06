<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SalaryPayment;

class SalaryPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee1;
    protected User $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'job_title' => 'Manager',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->employee1 = User::create([
            'name' => 'Employee 1',
            'email' => 'employee1@test.com',
            'username' => 'employee1',
            'job_title' => 'Developer',
            'phone' => '0987654321',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'salary' => 5000.00,
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $this->employee2 = User::create([
            'name' => 'Employee 2',
            'email' => 'employee2@test.com',
            'username' => 'employee2',
            'job_title' => 'Designer',
            'phone' => '0987654322',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'salary' => 4500.00,
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);
    }

    public function test_admin_can_mark_salary_as_paid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/salaries/{$this->employee1->id}/pay", [
                'month' => 6,
                'year' => 2026,
                'amount' => 5000.00,
                'notes' => 'Paid via Bank Transfer',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('salary_payments', [
            'user_id' => $this->employee1->id,
            'month' => 6,
            'year' => 2026,
            'amount' => 5000.00,
            'notes' => 'Paid via Bank Transfer',
        ]);
    }

    public function test_admin_cannot_pay_already_paid_salary(): void
    {
        SalaryPayment::create([
            'user_id' => $this->employee1->id,
            'month' => 6,
            'year' => 2026,
            'amount' => 5000.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/salaries/{$this->employee1->id}/pay", [
                'month' => 6,
                'year' => 2026,
                'amount' => 5000.00,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    public function test_admin_can_cancel_payment(): void
    {
        SalaryPayment::create([
            'user_id' => $this->employee1->id,
            'month' => 6,
            'year' => 2026,
            'amount' => 5000.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/salaries/{$this->employee1->id}/unpay", [
                'month' => 6,
                'year' => 2026,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('salary_payments', [
            'user_id' => $this->employee1->id,
            'month' => 6,
            'year' => 2026,
        ]);
    }

    public function test_employee_can_view_own_salary_details_and_history(): void
    {
        $response = $this->actingAs($this->employee1)
            ->get('/employee/salary?month=06&year=2026');

        $response->assertStatus(200);
        $response->assertViewHas('user');
        $response->assertViewHas('activeReport');
        $response->assertViewHas('adjustments');
        $response->assertViewHas('historyReports');
    }

    public function test_employee_cannot_access_admin_pay_routes(): void
    {
        $response = $this->actingAs($this->employee1)
            ->post("/admin/salaries/{$this->employee2->id}/pay", [
                'month' => 6,
                'year' => 2026,
                'amount' => 4500.00,
            ]);

        // Should be forbidden/redirected by AdminMiddleware
        $response->assertStatus(403);
    }
}
