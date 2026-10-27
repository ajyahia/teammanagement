<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\User;
use App\Models\Expense;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function services(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        $services = Service::with(['projects.payments', 'projects.cycles.payments'])->get();

        $serviceStats = $services->map(function ($service) use ($month, $year) {
            $allTimeRevenue = 0;
            $monthlyRevenue = 0;

            foreach ($service->projects as $project) {
                [$all, $monthly] = $this->projectRevenue($project, (int) $month, (int) $year);
                $allTimeRevenue += $all;
                $monthlyRevenue += $monthly;
            }

            return [
                'name' => $service->name,
                'projects_count' => $service->projects->count(),
                'all_time_revenue' => $allTimeRevenue,
                'monthly_revenue' => $monthlyRevenue,
            ];
        })->sortByDesc('monthly_revenue')->values();

        // Calculate Totals
        $totalMonthlyRevenue = $serviceStats->sum('monthly_revenue');
        $totalAllTimeRevenue = $serviceStats->sum('all_time_revenue');
        $totalMonthlySalaries = \App\Models\SalaryPayment::where('month', (int)$month)->where('year', (int)$year)->sum('amount');
        $totalAllTimeSalaries = \App\Models\SalaryPayment::sum('amount');
        $totalMonthlyExpenses = Expense::whereMonth('expense_date', $month)->whereYear('expense_date', $year)->sum('amount');
        $totalAllTimeExpenses = Expense::sum('amount');

        // Money paid out to external workers (subcontractors)
        $paidVendors = \App\Models\VendorPayment::where('status', 'paid');
        $totalMonthlyVendors = (clone $paidVendors)->whereMonth('paid_at', $month)->whereYear('paid_at', $year)->sum('amount');
        $totalAllTimeVendors = (clone $paidVendors)->sum('amount');

        $financialSummary = [
            'monthly_revenue' => $totalMonthlyRevenue,
            'monthly_salaries' => $totalMonthlySalaries,
            'monthly_expenses' => $totalMonthlyExpenses,
            'monthly_vendors' => $totalMonthlyVendors,
            'monthly_net_profit' => $totalMonthlyRevenue - $totalMonthlySalaries - $totalMonthlyExpenses - $totalMonthlyVendors,
            'all_time_revenue' => $totalAllTimeRevenue,
            'all_time_salaries' => $totalAllTimeSalaries,
            'all_time_expenses' => $totalAllTimeExpenses,
            'all_time_vendors' => $totalAllTimeVendors,
            'all_time_net_profit' => $totalAllTimeRevenue - $totalAllTimeSalaries - $totalAllTimeExpenses - $totalAllTimeVendors,
        ];

        return view('admin.reports.services', compact(
            'serviceStats', 'month', 'year', 'selectedDate', 'financialSummary'
        ));
    }

    public function employees(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        $employees = User::where('role', 'employee')
            ->with(['projects.payments', 'projects.cycles.payments', 'projects.employees'])
            ->get();

        $employeeStats = $employees->map(function ($emp) use ($month, $year) {
            $allTimeRevenue = 0;
            $monthlyRevenue = 0;

            foreach ($emp->projects as $project) {
                [$all, $monthly] = $this->projectRevenue($project, (int) $month, (int) $year);
                // A project shared by several employees is split between them, not counted for each
                $team = max(1, $project->employees->count());
                $allTimeRevenue += $all / $team;
                $monthlyRevenue += $monthly / $team;
            }

            $monthlySalaryPaid = \App\Models\SalaryPayment::where('user_id', $emp->id)->where('month', (int)$month)->where('year', (int)$year)->sum('amount');
            $allTimeSalaryPaid = \App\Models\SalaryPayment::where('user_id', $emp->id)->sum('amount');

            return [
                'name' => $emp->name,
                'projects_count' => $emp->projects->count(),
                'all_time_revenue' => round($allTimeRevenue, 2),
                'monthly_revenue' => round($monthlyRevenue, 2),
                'monthly_salary' => $monthlySalaryPaid,
                'all_time_salary' => $allTimeSalaryPaid,
                'monthly_profit' => round($monthlyRevenue - $monthlySalaryPaid, 2),
                'all_time_profit' => round($allTimeRevenue - $allTimeSalaryPaid, 2),
            ];
        })->sortByDesc('monthly_revenue')->values();

        return view('admin.reports.employees', compact(
            'employeeStats', 'month', 'year', 'selectedDate'
        ));
    }

    /**
     * Money actually received for a project: [all time, selected month].
     * Paid installments count by their paid date; subscription cycles marked
     * paid without any payment record are counted as well.
     */
    private function projectRevenue($project, int $month, int $year): array
    {
        $paid = $project->payments->where('status', 'paid');
        $all = (float) $paid->sum('amount');
        $monthly = (float) $paid
            ->filter(fn($p) => $p->paid_at && $p->paid_at->month === $month && $p->paid_at->year === $year)
            ->sum('amount');

        if (in_array($project->billing_type, ['monthly', 'yearly'])) {
            $direct = $project->cycles->filter(fn($c) => $c->is_paid && $c->payments->isEmpty());
            $all += (float) $direct->sum('amount');
            $monthly += (float) $direct
                ->filter(fn($c) => $c->paid_at && $c->paid_at->month === $month && $c->paid_at->year === $year)
                ->sum('amount');
        }

        return [$all, $monthly];
    }
}
