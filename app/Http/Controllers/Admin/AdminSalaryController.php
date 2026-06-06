<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Models\SpecificHoliday;
use App\Models\SalaryAdjustment;
use App\Models\AttendanceRecord;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminSalaryController extends Controller
{
    /**
     * Display a listing of monthly salaries.
     */
    public function index(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        // Format to two digits
        $month = sprintf('%02d', $month);

        $employees = User::where('role', 'employee')->orderBy('name', 'asc')->get();

        // Calculate expected working days for the selected month/year
        $expectedWorkingDays = $this->calculateExpectedWorkingDays($month, $year);

        // Fetch ALL adjustments for this month (multiple records per user now)
        $allAdjustments = SalaryAdjustment::where('month', (int)$month)
            ->where('year', (int)$year)
            ->get()
            ->groupBy('user_id');

        // Fetch absent counts for this month
        $absentRecords = AttendanceRecord::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'absent')
            ->get()
            ->groupBy('user_id');

        // Fetch ALL payments for this month
        $allPayments = SalaryPayment::where('month', (int)$month)
            ->where('year', (int)$year)
            ->get()
            ->groupBy('user_id');

        $reports = [];
        foreach ($employees as $emp) {
            // Absent days count
            $absentDays = $absentRecords->has($emp->id) ? $absentRecords->get($emp->id)->count() : 0;

            // Calculations
            $baseSalary = (float)$emp->salary;
            $deductionPerDay = $expectedWorkingDays > 0 ? $baseSalary / $expectedWorkingDays : 0.00;
            $absentDeduction = $deductionPerDay * $absentDays;

            // Manual adjustments - sum all records for this user
            $userAdjustments = $allAdjustments->get($emp->id, collect());
            $manualBonus = $userAdjustments->where('type', 'bonus')->sum('amount');
            $manualDeduction = $userAdjustments->where('type', 'deduction')->sum('amount');

            // Also support legacy records that used bonus/deduction columns
            $manualBonus += $userAdjustments->sum('bonus');
            $manualDeduction += $userAdjustments->sum('deduction');

            // Totals
            $totalDeductions = $absentDeduction + $manualDeduction;
            $totalBonuses = $manualBonus;

            // Net Salary
            $netSalary = $baseSalary - $totalDeductions + $totalBonuses;
            if ($netSalary < 0) {
                $netSalary = 0.00;
            }

            $isPaid = $allPayments->has($emp->id);

            $reports[] = [
                'employee' => $emp,
                'base_salary' => $baseSalary,
                'total_deductions' => $totalDeductions,
                'total_bonuses' => $totalBonuses,
                'net_salary' => $netSalary,
                'is_paid' => $isPaid,
            ];
        }

        return view('admin.salaries.index', compact('reports', 'month', 'year', 'expectedWorkingDays'));
    }

    /**
     * Display detailed monthly calculation and chronological history for a specific employee.
     */
    public function details(Request $request, User $user)
    {
        // Enforce employee role
        if ($user->role !== 'employee') {
            abort(404);
        }

        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        // Format to two digits
        $month = sprintf('%02d', $month);

        // 1. Current Active Month calculations
        $expectedWorkingDays = $this->calculateExpectedWorkingDays($month, $year);

        // Calculate shift hours
        $start = Carbon::parse($user->work_start_time);
        $end = Carbon::parse($user->work_end_time);
        $shiftHours = $start->diffInMinutes($end) / 60.0;
        if ($shiftHours <= 0) {
            $shiftHours = 8.0;
        }

        $expectedWorkingHours = $expectedWorkingDays * $shiftHours;

        // Absent days count for active month
        $absentDays = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'absent')
            ->count();

        // Present days count for active month
        $presentDays = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'present')
            ->count();

        $baseSalary = (float)$user->salary;
        $deductionPerDay = $expectedWorkingDays > 0 ? $baseSalary / $expectedWorkingDays : 0.00;
        $absentDeduction = $deductionPerDay * $absentDays;

        // Get ALL adjustments for the active month
        $adjustments = SalaryAdjustment::where('user_id', $user->id)
            ->where('month', (int)$month)
            ->where('year', (int)$year)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get payment record if exists
        $payment = SalaryPayment::where('user_id', $user->id)
            ->where('month', (int)$month)
            ->where('year', (int)$year)
            ->first();

        // Calculate totals from all adjustment records
        $manualBonus = $adjustments->where('type', 'bonus')->sum('amount')
                     + $adjustments->sum('bonus');
        $manualDeduction = $adjustments->where('type', 'deduction')->sum('amount')
                         + $adjustments->sum('deduction');

        $netSalary = $baseSalary - $absentDeduction - $manualDeduction + $manualBonus;
        if ($netSalary < 0) {
            $netSalary = 0.00;
        }

        $activeReport = [
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
        ];

        // 2. Chronological History (all adjustment records for this user, grouped by month)
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

            $hExpectedDays = $this->calculateExpectedWorkingDays($hMonth, $hYear);

            $hAbsentDays = AttendanceRecord::where('user_id', $user->id)
                ->whereYear('date', $hYear)
                ->whereMonth('date', $hMonth)
                ->where('status', 'absent')
                ->count();

            $hDeductionPerDay = $hExpectedDays > 0 ? $baseSalary / $hExpectedDays : 0.00;
            $hAbsentDeduction = $hDeductionPerDay * $hAbsentDays;

            $hAdjs = SalaryAdjustment::where('user_id', $user->id)
                ->where('month', (int)$hMonth)
                ->where('year', (int)$hYear)
                ->get();

            $hBonus = $hAdjs->where('type', 'bonus')->sum('amount') + $hAdjs->sum('bonus');
            $hDeduction = $hAdjs->where('type', 'deduction')->sum('amount') + $hAdjs->sum('deduction');

            $hNet = $baseSalary - $hAbsentDeduction - $hDeduction + $hBonus;
            if ($hNet < 0) {
                $hNet = 0.00;
            }

            $hPayment = SalaryPayment::where('user_id', $user->id)
                ->where('month', (int)$hMonth)
                ->where('year', (int)$hYear)
                ->first();
            $hIsPaid = $hPayment !== null;

            $historyReports[] = [
                'month' => $hm['month'],
                'year' => $hYear,
                'base_salary' => $baseSalary,
                'absent_days' => $hAbsentDays,
                'absent_deduction' => $hAbsentDeduction,
                'manual_bonus' => $hBonus,
                'manual_deduction' => $hDeduction,
                'net_salary' => $hNet,
                'is_paid' => $hIsPaid,
            ];
        }

        return view('admin.salaries.details', compact(
            'user',
            'month',
            'year',
            'activeReport',
            'adjustments',
            'historyReports',
            'payment'
        ));
    }

    /**
     * Store a NEW adjustment record (bonus or deduction).
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'month' => ['required', 'numeric', 'min:1', 'max:12'],
            'year' => ['required', 'numeric', 'min:2020', 'max:2100'],
            'type' => ['required', 'in:bonus,deduction'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        SalaryAdjustment::create([
            'user_id' => $validatedData['user_id'],
            'month' => (int)$validatedData['month'],
            'year' => (int)$validatedData['year'],
            'type' => $validatedData['type'],
            'amount' => $validatedData['amount'],
            'bonus' => 0,
            'deduction' => 0,
            'notes' => $validatedData['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', __('Adjustment added successfully.'));
    }

    /**
     * Delete a specific adjustment record.
     */
    public function destroy($id)
    {
        $adjustment = SalaryAdjustment::findOrFail($id);
        $adjustment->delete();

        return redirect()->back()->with('success', __('Adjustment deleted successfully.'));
    }

    /**
     * Record a salary payment.
     */
    public function pay(Request $request, User $user)
    {
        if ($user->role !== 'employee') {
            abort(404);
        }

        $validatedData = $request->validate([
            'month' => ['required', 'numeric', 'min:1', 'max:12'],
            'year' => ['required', 'numeric', 'min:2020', 'max:2100'],
            'amount' => ['required', 'numeric', 'min:0.00'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $exists = SalaryPayment::where('user_id', $user->id)
            ->where('month', (int)$validatedData['month'])
            ->where('year', (int)$validatedData['year'])
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', __('Salary is already marked as paid.'));
        }

        SalaryPayment::create([
            'user_id' => $user->id,
            'month' => (int)$validatedData['month'],
            'year' => (int)$validatedData['year'],
            'amount' => $validatedData['amount'],
            'notes' => $validatedData['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', __('Salary payment recorded successfully.'));
    }

    /**
     * Cancel a salary payment record.
     */
    public function unpay(Request $request, User $user)
    {
        if ($user->role !== 'employee') {
            abort(404);
        }

        $validatedData = $request->validate([
            'month' => ['required', 'numeric', 'min:1', 'max:12'],
            'year' => ['required', 'numeric', 'min:2020', 'max:2100'],
        ]);

        $payment = SalaryPayment::where('user_id', $user->id)
            ->where('month', (int)$validatedData['month'])
            ->where('year', (int)$validatedData['year'])
            ->first();

        if (!$payment) {
            return redirect()->back()->with('error', __('Salary payment record not found.'));
        }

        $payment->delete();

        return redirect()->back()->with('success', __('Salary payment record deleted successfully.'));
    }

    /**
     * Helper to calculate expected working days in a month.
     */
    private function calculateExpectedWorkingDays($month, $year)
    {
        $weeklyHolidaysJson = Setting::getVal('weekly_holidays', '[]');
        $weeklyHolidays = json_decode($weeklyHolidaysJson, true) ?? [];

        // Get specific holiday dates for this month/year
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

            // Check weekly weekend
            if (in_array($dayOfWeek, $weeklyHolidays)) {
                continue;
            }

            // Check specific holiday
            if (in_array($dateStr, $specificHolidays)) {
                continue;
            }

            $workingDays++;
        }

        return $workingDays;
    }
}
