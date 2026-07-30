<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;
use App\Services\SalaryService;

class EmployeeSalaryController extends Controller
{
    protected SalaryService $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * Display the employee's own salary page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $activeReport = $this->salaryService->calculateEmployeeMonthlySalary($user, $month, $year);
        $historyReports = $this->salaryService->getEmployeeSalaryHistory($user);

        $payment = SalaryPayment::where('user_id', $user->id)
            ->where('month', (int)$month)
            ->where('year', (int)$year)
            ->first();

        return view('employee.salary.index', [
            'user' => $user,
            'month' => sprintf('%02d', $month),
            'year' => $year,
            'activeReport' => $activeReport,
            'adjustments' => $activeReport['adjustments'],
            'historyReports' => $historyReports,
            'payment' => $payment
        ]);
    }
}
