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

        // Revenue by service, by the months its clients work with us
        $serviceStats = $reports->revenueByService((int) $month, (int) $year);

        $projectStats = $reports->projectProfitability((int) $month, (int) $year);
        $dues = $reports->dues();

        return view('admin.reports.services', compact(
            'month', 'year', 'selectedDate', 'series', 'yearTotals', 'monthTotals', 'serviceStats', 'projectStats', 'dues'
        ));
    }

    /**
     * Who works on what, what each employee costs and how present they are.
     * Revenue is deliberately not credited to individual employees: money belongs to
     * the project and the service, and every project is a team effort.
     */
    public function employees(Request $request, \App\Services\ReportService $reports)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        [, $load] = $reports->teamCostByProject((int) $month, (int) $year);
        $serviceProfit = $reports->serviceProfit((int) $month, (int) $year);
        $attendance = app(\App\Services\AttendanceService::class);
        $serviceNames = Service::pluck('name', 'id');

        $employees = User::where('role', 'employee')
            ->activeInMonth($month, $year)
            ->with(['services', 'projects.client', 'projects.service'])
            ->get();

        $employeeStats = $employees->map(function ($emp) use ($month, $year, $load, $attendance, $serviceNames) {
            $active = $emp->projects->whereIn('status', \App\Models\Project::ACTIVE_STATUSES);
            $salary = (float) \App\Models\SalaryPayment::where('user_id', $emp->id)
                ->where('month', (int) $month)->where('year', (int) $year)->sum('amount');
            $activeCount = $load[$emp->id] ?? 0;

            return [
                'name' => $emp->name,
                'job_title' => $emp->job_title,
                'skills' => $emp->services->pluck('name')->all(),
                'active_projects' => $active->map(fn($p) => [
                    'id' => $p->id,
                    'label' => ($p->client?->name ?? '-') . ' · ' . ($p->service?->name ?? ''),
                    'role' => $serviceNames[$p->pivot->service_id] ?? null,
                ])->values()->all(),
                'active_count' => $activeCount,
                'done_count' => $emp->projects->where('status', 'completed')->count(),
                'salary' => $salary,
                'salary_per_project' => $activeCount ? round($salary / $activeCount, 2) : 0.0,
                'attendance' => $attendance->summary($emp, (int) $month, (int) $year),
            ];
        })->sortByDesc('active_count')->values();

        return view('admin.reports.employees', compact('employeeStats', 'month', 'year', 'selectedDate', 'serviceProfit'));
    }
}
