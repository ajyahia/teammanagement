<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SalaryAdjustment;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;
use App\Services\SalaryService;
use App\Http\Requests\StoreSalaryAdjustmentRequest;

class AdminSalaryController extends Controller
{
    protected SalaryService $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * Display a listing of monthly salaries.
     */
    public function index(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $data = $this->salaryService->getMonthlySalariesReport($month, $year);

        return view('admin.salaries.index', [
            'reports' => $data['reports'],
            'month' => sprintf('%02d', $month),
            'year' => $year,
            'expectedWorkingDays' => $data['expectedWorkingDays']
        ]);
    }

    /**
     * Display detailed monthly calculation and chronological history for a specific employee.
     */
    public function details(Request $request, User $user)
    {
        if ($user->role !== 'employee') {
            abort(404);
        }

        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $activeReport = $this->salaryService->calculateEmployeeMonthlySalary($user, $month, $year);
        $historyReports = $this->salaryService->getEmployeeSalaryHistory($user);

        $payment = SalaryPayment::where('user_id', $user->id)
            ->where('month', (int)$month)
            ->where('year', (int)$year)
            ->first();

        return view('admin.salaries.details', [
            'user' => $user,
            'month' => sprintf('%02d', $month),
            'year' => $year,
            'activeReport' => $activeReport,
            'adjustments' => $activeReport['adjustments'],
            'historyReports' => $historyReports,
            'payment' => $payment
        ]);
    }

    /**
     * Store a NEW adjustment record (bonus or deduction).
     */
    public function store(StoreSalaryAdjustmentRequest $request)
    {
        $validatedData = $request->validated();

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
}
