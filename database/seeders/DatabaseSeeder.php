<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin Account
        User::create([
            'name' => 'System Admin',
            'age' => 35,
            'job_title' => 'Administrator',
            'phone' => '1234567890',
            'whatsapp' => '1234567890',
            'email' => 'admin@teammanagement.com',
            'username' => 'testadmin',
            'password' => 'testadmin', // Hashed via User casts
            'password_text' => 'testadmin',
            'role' => 'admin',
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        // Employee Account
        $employee = User::create([
            'name' => 'Test Employee',
            'age' => 28,
            'job_title' => 'Software Engineer',
            'phone' => '0987654321',
            'whatsapp' => '0987654321',
            'email' => 'employee@teammanagement.com',
            'username' => 'testemp',
            'password' => 'testemp', // Hashed via User casts
            'password_text' => 'testemp',
            'role' => 'employee',
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
            'salary' => 5000.00,
        ]);

        // Seed Vacation Balance for the Employee for the current year
        \App\Models\VacationBalance::create([
            'user_id' => $employee->id,
            'year' => date('Y'),
            'total_allowed' => 21,
            'used' => 0,
        ]);

        // Seed Company Policies
        \App\Models\CompanyPolicy::create([
            'title' => 'Working Hours & Attendance',
            'title_ar' => 'مواعيد العمل والحضور',
            'content' => 'Standard work shift is 8 hours daily. Employees are expected to check in at their scheduled start time. Late arrivals of more than 15 minutes will be logged and may affect the monthly metrics.',
            'content_ar' => 'شفت العمل الرسمي هو 8 ساعات يومياً. يتوقع من الأعضاء تسجيل الحضور في وقت بدء الدوام المحدد. سيتم تسجيل التأخير لأكثر من 15 دقيقة وقد يؤثر ذلك على مؤشرات الحضور الشهرية.',
            'icon' => 'ri-time-line',
            'sort_order' => 1,
        ]);

        \App\Models\CompanyPolicy::create([
            'title' => 'Vacations & Leaves',
            'title_ar' => 'الإجازات والعطلات',
            'content' => 'Employees are entitled to 21 days of paid annual vacation. Vacation requests must be submitted at least 3 days in advance and approved by the management. Official holidays are fully paid.',
            'content_ar' => 'يحق للأعضاء الحصول على 21 يوماً من الإجازة السنوية مدفوعة الأجر. يجب تقديم طلبات الإجازة قبل 3 أيام على الأقل واعتمادها من الإدارة. العطلات الرسمية مدفوعة الأجر بالكامل.',
            'icon' => 'ri-plane-line',
            'sort_order' => 2,
        ]);

        \App\Models\CompanyPolicy::create([
            'title' => 'Salary & Compensation',
            'title_ar' => 'المرتبات والمكافآت',
            'content' => 'Basic salaries are paid at the end of each calendar month. Absence deductions are calculated based on the shift rate. Adjustments (bonuses/deductions) are reviewed and approved by the admin.',
            'content_ar' => 'يتم دفع المرتبات الأساسية في نهاية كل شهر ميلادي. يتم احتساب خصومات الغياب بناءً على معدل الدوام. يتم مراجعة واعتماد التسويات (العلاوات والخصومات) من قبل المدير.',
            'icon' => 'ri-money-dollar-circle-line',
            'sort_order' => 3,
        ]);

        \App\Models\CompanyPolicy::create([
            'title' => 'Code of Conduct',
            'title_ar' => 'قواعد السلوك والعمل',
            'content' => 'We foster a collaborative, professional, and respectful environment. Team members are expected to maintain professional behavior, protect company assets, and respect their colleagues.',
            'content_ar' => 'نحن نسعى لخلق بيئة عمل تعاونية ومهنية ومحترمة. يتوقع من أعضاء الفريق الحفاظ على السلوك المهني، وحماية أصول الشركة، واحترام زملائهم.',
            'icon' => 'ri-shield-line',
            'sort_order' => 4,
        ]);
    }
}
