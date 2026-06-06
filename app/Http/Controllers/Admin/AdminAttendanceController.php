<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    /**
     * Display a listing of attendance records for a specific date.
     */
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $employees = User::where('role', 'employee')->get();
        
        // Fetch all attendance records for this date, keyed by user_id
        $attendanceRecords = AttendanceRecord::where('date', $date)
            ->get()
            ->keyBy('user_id');

        // Check if date is a holiday (weekly or specific)
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

        return view('admin.attendance.index', compact('employees', 'attendanceRecords', 'date', 'isHoliday', 'holidayName'));
    }

    /**
     * Store or update an attendance record for a specific employee and date.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,vacation,excused'],
            'notes' => ['nullable', 'string'],
        ]);

        // Holiday Check
        $date = $validatedData['date'];
        $dayOfWeek = (int) date('w', strtotime($date));
        $weeklyHolidays = json_decode(\App\Models\Setting::getVal('weekly_holidays', '[]'), true) ?? [];
        $isHoliday = in_array($dayOfWeek, $weeklyHolidays) || \App\Models\SpecificHoliday::where('date', $date)->exists();

        if ($isHoliday) {
            return redirect()->back()->with('error', __('Cannot mark attendance on a holiday.'));
        }

        $attendance = AttendanceRecord::updateOrCreate(
            [
                'user_id' => $validatedData['user_id'],
                'date' => $validatedData['date'],
            ],
            [
                'status' => $validatedData['status'],
                'notes' => $validatedData['notes'] ?? null,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()->route('admin.attendance.index', ['date' => $validatedData['date']])
            ->with('success', 'Attendance record saved successfully.');
    }

    /**
     * Mark all employees as present for a specific date.
     */
    public function markAllPresent(Request $request)
    {
        $validatedData = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $validatedData['date'];

        // Holiday Check
        $dayOfWeek = (int) date('w', strtotime($date));
        $weeklyHolidays = json_decode(\App\Models\Setting::getVal('weekly_holidays', '[]'), true) ?? [];
        $isHoliday = in_array($dayOfWeek, $weeklyHolidays) || \App\Models\SpecificHoliday::where('date', $date)->exists();

        if ($isHoliday) {
            return redirect()->back()->with('error', __('Cannot mark attendance on a holiday.'));
        }

        $employees = User::where('role', 'employee')->get();

        foreach ($employees as $emp) {
            AttendanceRecord::updateOrCreate(
                [
                    'user_id' => $emp->id,
                    'date' => $date,
                ],
                [
                    'status' => 'present',
                    'created_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('admin.attendance.index', ['date' => $date])
            ->with('success', 'All employees marked as present.');
    }

    /**
     * Delete an attendance record.
     */
    public function destroy(AttendanceRecord $attendance)
    {
        $date = $attendance->date->format('Y-m-d');
        $attendance->delete();

        return redirect()->route('admin.attendance.index', ['date' => $date])
            ->with('success', 'Attendance record deleted successfully.');
    }

    /**
     * Display listing of employees with their monthly summaries.
     */
    public function months(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        
        $employees = User::where('role', 'employee')->get();
        
        // Map summaries for each employee
        $summaries = [];
        foreach ($employees as $emp) {
            $summaries[$emp->id] = [
                'present' => AttendanceRecord::where('user_id', $emp->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'present')->count(),
                'absent' => AttendanceRecord::where('user_id', $emp->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count(),
                'vacation' => AttendanceRecord::where('user_id', $emp->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'vacation')->count(),
                'excused' => AttendanceRecord::where('user_id', $emp->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'excused')->count(),
            ];
        }

        return view('admin.attendance.months', compact('employees', 'summaries', 'month', 'year'));
    }

    /**
     * Display monthly attendance details in a Saturday-to-Friday weeks table.
     */
    public function details(User $employee, $month, $year)
    {
        $weeks = $this->getWeeklyAttendance($employee->id, $month, $year);
        
        $summary = [
            'present' => AttendanceRecord::where('user_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'present')->count(),
            'absent' => AttendanceRecord::where('user_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count(),
            'vacation' => AttendanceRecord::where('user_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'vacation')->count(),
            'excused' => AttendanceRecord::where('user_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'excused')->count(),
        ];

        return view('admin.attendance.details', compact('employee', 'weeks', 'summary', 'month', 'year'));
    }

    /**
     * Helper to group a month's attendance records into Saturday-to-Friday calendar weeks.
     */
    private function getWeeklyAttendance($userId, $month, $year)
    {
        $records = AttendanceRecord::where('user_id', $userId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $firstDay = strtotime("$year-$month-01");
        $lastDay = strtotime("last day of this month", $firstDay);

        $dayOfWeek = date('w', $firstDay); // Sunday = 0, Saturday = 6
        $daysToSubtract = ($dayOfWeek + 1) % 7; // Get preceding Saturday offset
        $startInterval = strtotime("-$daysToSubtract days", $firstDay);

        $weeks = [];
        $current = $startInterval;
        $weekNum = 1;

        while ($current <= $lastDay) {
            $week = [
                'week_number' => $weekNum++,
                'days' => []
            ];

            // Day index order: Saturday (6), Sunday (0), Monday (1), Tuesday (2), Wednesday (3), Thursday (4), Friday (5)
            $dayOrder = [6, 0, 1, 2, 3, 4, 5];

            foreach ($dayOrder as $wIndex) {
                $dateStr = date('Y-m-d', $current);
                $currentMonth = date('m', $current);

                if ($currentMonth == $month) {
                    $record = $records->get($dateStr);
                    $week['days'][$wIndex] = [
                        'date' => $dateStr,
                        'day_num' => date('d', $current),
                        'status' => $record ? $record->status : 'none',
                        'notes' => $record ? $record->notes : ''
                    ];
                } else {
                    $week['days'][$wIndex] = null;
                }

                $current = strtotime("+1 day", $current);
            }

            $weeks[] = $week;
        }

        return $weeks;
    }
}
