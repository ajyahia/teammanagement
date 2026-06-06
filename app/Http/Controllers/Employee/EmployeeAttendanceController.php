<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class EmployeeAttendanceController extends Controller
{
    /**
     * Display the employee's own attendance history & summary.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $summary = [
            'present' => AttendanceRecord::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'present')->count(),
            'absent' => AttendanceRecord::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count(),
            'vacation' => AttendanceRecord::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'vacation')->count(),
            'excused' => AttendanceRecord::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'excused')->count(),
        ];

        return view('employee.attendance.index', compact('month', 'year', 'summary'));
    }

    /**
     * Display weekly attendance calendar for the employee (view-only).
     */
    public function details($month, $year)
    {
        $userId = auth()->id();
        $weeks = $this->getWeeklyAttendance($userId, $month, $year);
        
        $summary = [
            'present' => AttendanceRecord::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'present')->count(),
            'absent' => AttendanceRecord::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count(),
            'vacation' => AttendanceRecord::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'vacation')->count(),
            'excused' => AttendanceRecord::where('user_id', $userId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'excused')->count(),
        ];

        return view('employee.attendance.details', compact('weeks', 'summary', 'month', 'year'));
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
