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
    public function services(Request $request, \App\Services\ReportService $reports)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        // Money in / out for every month of the year
        $series = $reports->monthlySeries((int) $year);
        $yearTotals = $reports->totals($series);
        $monthTotals = $series[(int) $month];

        // Revenue by service
        $services = Service::with(['projects.payments', 'projects.cycles.payments'])->get();
        $serviceStats = $services->map(function ($service) use ($month, $year) {
            $row = ['name' => $service->name, 'projects_count' => $service->projects->count(), 'month' => 0, 'year' => 0, 'all' => 0];
            foreach ($service->projects as $project) {
                [$all, $monthly, $yearly] = $this->projectRevenue($project, (int) $month, (int) $year);
                $row['all'] += $all;
                $row['month'] += $monthly;
                $row['year'] += $yearly;
            }

            return $row;
        })->sortByDesc('year')->values();

        $projectStats = $reports->projectProfitability();
        $dues = $reports->dues();

        return view('admin.reports.services', compact(
            'month', 'year', 'selectedDate', 'series', 'yearTotals', 'monthTotals', 'serviceStats', 'projectStats', 'dues'
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
                [$all, $monthly] = array_slice($this->projectRevenue($project, (int) $month, (int) $year), 0, 2);
                // A project shared by several employees is split between them, not counted for each
                $team = max(1, $project->employees->count());
                $allTimeRevenue += $all / $team;
                $monthlyRevenue += $monthly / $team;
            }

            $monthlySalaryPaid = \App\Models\SalaryPayment::where('user_id', $emp->id)->where('month', (int)$month)->where('year', (int)$year)->sum('amount');
            $allTimeSalaryPaid = \App\Models\SalaryPayment::where('user_id', $emp->id)->sum('amount');

            $attendance = app(\App\Services\AttendanceService::class)->summary($emp, (int) $month, (int) $year);

            return [
                'attendance' => $attendance,
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
     * Money actually received for a project: [all time, selected month, selected year].
     * Paid installments count by their paid date; subscription cycles marked
     * paid without any payment record are counted as well.
     */
    private function projectRevenue($project, int $month, int $year): array
    {
        $items = $project->payments->where('status', 'paid')
            ->map(fn($p) => ['amount' => (float) $p->amount, 'date' => $p->paid_at]);

        if (in_array($project->billing_type, ['monthly', 'yearly'])) {
            $items = $items->concat(
                $project->cycles->filter(fn($c) => $c->is_paid && $c->payments->isEmpty())
                    ->map(fn($c) => ['amount' => (float) $c->amount, 'date' => $c->paid_at])
            );
        }

        $all = $items->sum('amount');
        $yearly = $items->filter(fn($i) => $i['date'] && $i['date']->year === $year)->sum('amount');
        $monthly = $items->filter(fn($i) => $i['date'] && $i['date']->year === $year && $i['date']->month === $month)->sum('amount');

        return [$all, $monthly, $yearly];
    }
}
