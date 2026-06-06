<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\VacationBalance;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    /**
     * Display the employee dashboard.
     */
    public function index()
    {
        $userId = auth()->id();
        $currentMonth = date('m');
        $currentYear = date('Y');

        $stats = [
            'present_this_month' => AttendanceRecord::where('user_id', $userId)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'present')
                ->count(),
            'absent_this_month' => AttendanceRecord::where('user_id', $userId)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'absent')
                ->count(),
            'vacation_this_month' => AttendanceRecord::where('user_id', $userId)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'vacation')
                ->count(),
            'excused_this_month' => AttendanceRecord::where('user_id', $userId)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'excused')
                ->count(),
            'vacation_balance' => VacationBalance::where('user_id', $userId)
                ->where('year', $currentYear)
                ->first(),
        ];

        $date = date('Y-m-d');
        $dayOfWeek = (int) date('w', strtotime($date));
        $weeklyHolidaysJson = \App\Models\Setting::getVal('weekly_holidays', '[]');
        $weeklyHolidays = json_decode($weeklyHolidaysJson, true) ?? [];
        $isWeeklyHoliday = in_array($dayOfWeek, $weeklyHolidays);

        $specificHoliday = \App\Models\SpecificHoliday::where('date', $date)->first();
        $isHoliday = $isWeeklyHoliday || $specificHoliday !== null;

        $holidayName = '';
        if ($specificHoliday) {
            $holidayName = $specificHoliday->name ?: __('Official Holiday');
        } elseif ($isWeeklyHoliday) {
            $holidayName = __('Weekly Holiday');
        }

        $todayRecord = AttendanceRecord::where('user_id', $userId)
            ->where('date', date('Y-m-d'))
            ->first();

        return view('employee.dashboard', compact('stats', 'isHoliday', 'holidayName', 'todayRecord'));
    }
}
