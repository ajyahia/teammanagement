<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SpecificHoliday;
use App\Models\SalaryAdjustment;
use App\Models\AttendanceRecord;
use App\Models\SalaryPayment;
use App\Models\User;
use Carbon\Carbon;

class SalaryService
{
    /**
     * Calculate expected working days in a month.
     */
    public function calculateExpectedWorkingDays($month, $year)
    {
        $weeklyHolidaysJson = Setting::getVal('weekly_holidays', '[]');
        $weeklyHolidays = json_decode($weeklyHolidaysJson, true) ?? [];

        $specificHolidays = SpecificHoliday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->map(fn($h) => $h->date->format('Y-m-d'))
            ->toArray();

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        $workingDays = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $timestamp = strtotime($dateStr);
            $dayOfWeek = (int)date('w', $timestamp); // 0 (Sunday) to 6 (Saturday)

            if (in_array($dayOfWeek, $weeklyHolidays)) continue;
            if (in_array($dateStr, $specificHolidays)) continue;

            $workingDays++;
        }

        return $workingDays;
    }

    /**
     * Generate monthly salaries report for all employees (optimised query).
     */
    public function getMonthlySalariesReport($month, $year)
    {
        $month = sprintf('%02d', $month);
        $expectedWorkingDays = $this->calculateExpectedWorkingDays($month, $year);

        $employees = User::where('role', 'employee')->orderBy('name', 'asc')->get();
        $allAdjustments = SalaryAdjustment::where('month', (int)$month)->where('year', (int)$year)->get()->groupBy('user_id');
        $absentRecords = AttendanceRecord::whereYear('date', $year)->whereMonth('date', $month)->where('status', 'absent')->get()->groupBy('user_id');
        $allPayments = SalaryPayment::where('month', (int)$month)->where('year', (int)$year)->get()->groupBy('user_id');

        $reports = [];
        foreach ($employees as $emp) {
            $absentDays = $absentRecords->has($emp->id) ? $absentRecords->get($emp->id)->count() : 0;
            
            if ($emp->payment_type === 'per_project') {
                $monthlyProjectsCount = $emp->projects()->whereYear('projects.created_at', $year)->whereMonth('projects.created_at', $month)->count();
                $baseSalary = (float)$emp->project_rate * $monthlyProjectsCount;
                $absentDeduction = 0.00; // No absent deduction for per-project workers
            } else {
                $baseSalary = (float)$emp->salary;
                $deductionPerDay = $expectedWorkingDays > 0 ? $baseSalary / $expectedWorkingDays : 0.00;
                $absentDeduction = $deductionPerDay * $absentDays;
            }

            $userAdjustments = $allAdjustments->get($emp->id, collect());
            $manualBonus = $userAdjustments->where('type', 'bonus')->sum('amount') + $userAdjustments->sum('bonus');
            $manualDeduction = $userAdjustments->whereIn('type', ['deduction', 'advance'])->sum('amount') + $userAdjustments->sum('deduction');

            $totalDeductions = $absentDeduction + $manualDeduction;
            $totalBonuses = $manualBonus;

            $netSalary = $baseSalary - $totalDeductions + $totalBonuses;

            // Hide employees with absolutely no financial activity this month
            if ($baseSalary == 0 && $totalBonuses == 0 && $totalDeductions == 0 && !$allPayments->has($emp->id)) {
                continue;
            }

            $reports[] = [
                'employee' => $emp,
                'base_salary' => $baseSalary,
                'total_deductions' => $totalDeductions,
                'total_bonuses' => $totalBonuses,
                'net_salary' => $netSalary,
                'is_paid' => $allPayments->has($emp->id),
            ];
        }

        return [
            'reports' => $reports,
            'expectedWorkingDays' => $expectedWorkingDays
        ];
    }

    /**
     * Calculate salary details for a specific employee for a given month and year.
     */
    public function calculateEmployeeMonthlySalary(User $user, $month, $year, $expectedWorkingDays = null)
    {
        $month = sprintf('%02d', $month);
        
        if ($expectedWorkingDays === null) {
            $expectedWorkingDays = $this->calculateExpectedWorkingDays($month, $year);
        }

        $workStart = $user->work_start_time ?? Setting::getVal('default_work_start', '09:00');
        $workEnd = $user->work_end_time ?? Setting::getVal('default_work_end', '17:00');

        $start = Carbon::parse($workStart);
        $end = Carbon::parse($workEnd);
        $shiftHours = $start->diffInMinutes($end) / 60.0;
        if ($shiftHours <= 0) {
            $shiftHours = 8.0; // Fallback
        }

        $expectedWorkingHours = $expectedWorkingDays * $shiftHours;

        $absentDays = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'absent')
            ->count();

        $presentDays = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'present')
            ->count();

        if ($user->payment_type === 'per_project') {
            $monthlyProjectsCount = $user->projects()->whereYear('projects.created_at', $year)->whereMonth('projects.created_at', $month)->count();
            $baseSalary = (float)$user->project_rate * $monthlyProjectsCount;
            $absentDeduction = 0.00;
        } else {
            $baseSalary = (float)$user->salary;
            $deductionPerDay = $expectedWorkingDays > 0 ? $baseSalary / $expectedWorkingDays : 0.00;
            $absentDeduction = $deductionPerDay * $absentDays;
        }

        $adjustments = SalaryAdjustment::where('user_id', $user->id)
            ->where('month', (int)$month)
            ->where('year', (int)$year)
            ->orderBy('created_at', 'desc')
            ->get();

        $manualBonus = $adjustments->where('type', 'bonus')->sum('amount') + $adjustments->sum('bonus');
        $manualDeduction = $adjustments->whereIn('type', ['deduction', 'advance'])->sum('amount') + $adjustments->sum('deduction');

        $netSalary = $baseSalary - $absentDeduction - $manualDeduction + $manualBonus;

        return [
            'expected_working_days' => $expectedWorkingDays,
            'expected_working_hours' => $expectedWorkingHours,
            'shift_hours' => $shiftHours,
            'absent_days' => $absentDays,
            'present_days' => $presentDays,
            'absent_deduction' => $absentDeduction,
            'base_salary' => $baseSalary,
            'manual_bonus' => $manualBonus,
            'manual_deduction' => $manualDeduction,
            'net_salary' => $netSalary,
            'adjustments' => $adjustments
        ];
    }

    /**
     * Get the chronological history of salaries for an employee.
     */
    public function getEmployeeSalaryHistory(User $user)
    {
        if (config('database.default') === 'sqlite') {
            $attendanceMonths = AttendanceRecord::where('user_id', $user->id)
                ->selectRaw('strftime("%Y", date) as year, strftime("%m", date) as month')
                ->groupBy('year', 'month')
                ->get();
        } else {
            $attendanceMonths = AttendanceRecord::where('user_id', $user->id)
                ->selectRaw('YEAR(date) as year, MONTH(date) as month')
                ->groupBy('year', 'month')
                ->get();
        }

        $adjustmentMonths = SalaryAdjustment::where('user_id', $user->id)
            ->select('year', 'month')
            ->groupBy('year', 'month')
            ->get();

        $historyMonths = $attendanceMonths->concat($adjustmentMonths)
            ->map(function ($item) {
                return [
                    'year' => (int)$item->year,
                    'month' => (int)$item->month,
                ];
            })
            ->unique(function ($item) {
                return $item['year'] . '-' . $item['month'];
            })
            ->sortByDesc(function ($item) {
                return sprintf('%04d%02d', $item['year'], $item['month']);
            });

        $historyReports = [];
        foreach ($historyMonths as $hm) {
            $hMonth = sprintf('%02d', $hm['month']);
            $hYear = $hm['year'];

            $report = $this->calculateEmployeeMonthlySalary($user, $hMonth, $hYear);

            $hPayment = SalaryPayment::where('user_id', $user->id)
                ->where('month', (int)$hMonth)
                ->where('year', (int)$hYear)
                ->first();
            $hIsPaid = $hPayment !== null;

            $historyReports[] = [
                'month' => $hm['month'],
                'year' => $hYear,
                'base_salary' => $report['base_salary'],
                'absent_days' => $report['absent_days'],
                'absent_deduction' => $report['absent_deduction'],
                'manual_bonus' => $report['manual_bonus'],
                'manual_deduction' => $report['manual_deduction'],
                'net_salary' => $report['net_salary'],
                'is_paid' => $hIsPaid,
            ];
        }

        return $historyReports;
    }
}
