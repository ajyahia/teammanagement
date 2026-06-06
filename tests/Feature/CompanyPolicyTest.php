<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\CompanyPolicy;

class CompanyPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee;
    protected CompanyPolicy $policy;

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

        $this->employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@test.com',
            'username' => 'employee',
            'job_title' => 'Developer',
            'phone' => '0987654321',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $this->policy = CompanyPolicy::create([
            'title' => 'Test Policy',
            'title_ar' => 'سياسة تجريبية',
            'content' => 'This is a test policy description.',
            'content_ar' => 'هذا وصف لسياسة تجريبية.',
            'icon' => 'ri-file-text-line',
            'sort_order' => 1,
        ]);
    }

    public function test_admin_can_view_policies_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/policies');

        $response->assertStatus(200);
        $response->assertSee('Test Policy');
        $response->assertSee('سياسة تجريبية');
    }

    public function test_admin_can_create_policy(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/policies', [
                'title' => 'New Policy',
                'title_ar' => 'سياسة جديدة',
                'content' => 'New policy content.',
                'content_ar' => 'محتوى السياسة الجديدة.',
                'icon' => 'ri-shield-line',
                'sort_order' => 2,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/policies');

        $this->assertDatabaseHas('company_policies', [
            'title' => 'New Policy',
            'title_ar' => 'سياسة جديدة',
        ]);
    }

    public function test_admin_can_edit_policy(): void
    {
        $response = $this->actingAs($this->admin)
            ->put("/admin/policies/{$this->policy->id}", [
                'title' => 'Updated Policy Title',
                'title_ar' => 'سياسة محدثة',
                'content' => 'Updated policy content.',
                'content_ar' => 'محتوى السياسة المحدثة.',
                'icon' => 'ri-time-line',
                'sort_order' => 5,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/policies');

        $this->assertDatabaseHas('company_policies', [
            'id' => $this->policy->id,
            'title' => 'Updated Policy Title',
            'title_ar' => 'سياسة محدثة',
        ]);
    }

    public function test_admin_can_delete_policy(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete("/admin/policies/{$this->policy->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/policies');

        $this->assertDatabaseMissing('company_policies', [
            'id' => $this->policy->id,
        ]);
    }

    public function test_employee_can_view_policies_list(): void
    {
        $response = $this->actingAs($this->employee)
            ->get('/employee/policies');

        $response->assertStatus(200);
        if (app()->getLocale() === 'ar') {
            $response->assertSee($this->policy->title_ar);
        } else {
            $response->assertSee($this->policy->title);
        }
    }

    public function test_employee_cannot_access_admin_policy_routes(): void
    {
        // Try to access admin policies index
        $response = $this->actingAs($this->employee)
            ->get('/admin/policies');
        $response->assertStatus(403);

        // Try to create policy
        $response = $this->actingAs($this->employee)
            ->post('/admin/policies', [
                'title' => 'Hack Policy',
                'title_ar' => 'سياسة اختراق',
                'content' => 'Hacked.',
                'content_ar' => 'تم الاختراق.',
            ]);
        $response->assertStatus(403);
    }
}
