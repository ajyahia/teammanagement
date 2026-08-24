<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\Setting;
use App\Models\Project;
use App\Models\SalaryPayment;
use App\Models\Expense;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with stats.
     */
    public function index()
    {
        $today = date('Y-m-d');
        
        $stats = [
            'total_employees' => User::where('role', 'employee')->count(),
            'present_today' => AttendanceRecord::where('date', $today)->where('status', 'present')->count(),
            'absent_today' => AttendanceRecord::where('date', $today)->where('status', 'absent')->count(),
            'vacation_today' => AttendanceRecord::where('date', $today)->where('status', 'vacation')->count(),
            'excused_today' => AttendanceRecord::where('date', $today)->where('status', 'excused')->count(),
        ];

        $attendance_rate = 0;
        if ($stats['total_employees'] > 0) {
            $attendance_rate = round(($stats['present_today'] / $stats['total_employees']) * 100);
        }

        $recent_activities = AttendanceRecord::with('user')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Financial Metrics Calculation
        $budget = (float) Setting::getVal('company_budget', 0);
        $totalSalaryPayments = (float) SalaryPayment::sum('amount');
        $totalExpenses = (float) Expense::sum('amount');

        $projects = Project::with('cycles')->get();
        
        $totalProjectIncomes = $projects->sum('total_paid');
        
        $treasuryBalance = $budget + $totalProjectIncomes - $totalSalaryPayments - $totalExpenses;

        $monthlyDues = $projects->whereIn('billing_type', ['monthly', 'yearly'])->sum('due_amount');
        $regularDues = $projects->whereIn('billing_type', ['one_time', 'per_page'])->sum('due_amount');

        $financials = [
            'treasury' => $treasuryBalance,
            'monthly_dues' => $monthlyDues,
            'regular_dues' => $regularDues,
        ];

        return view('admin.dashboard', compact('stats', 'attendance_rate', 'recent_activities', 'financials'));
    }
}
