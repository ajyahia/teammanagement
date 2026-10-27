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

        $month = app(\App\Services\AttendanceService::class)->summary(auth()->user(), (int) $currentMonth, (int) $currentYear);

        $stats = [
            'present_this_month' => $month['present'],
            'absent_this_month' => $month['absent'],
            'vacation_this_month' => $month['vacation'],
            'excused_this_month' => $month['excused'],
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
