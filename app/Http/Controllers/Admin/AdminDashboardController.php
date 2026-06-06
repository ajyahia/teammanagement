<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AttendanceRecord;
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

        return view('admin.dashboard', compact('stats', 'attendance_rate', 'recent_activities'));
    }
}
