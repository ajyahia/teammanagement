<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        // Fetch services and their projects
        $services = Service::with(['projects'])->get();
        
        $serviceStats = $services->map(function ($service) use ($month, $year) {
            $allProjects = $service->projects;
            
            $allTimeRevenue = 0;
            $monthlyRevenue = 0;

            foreach ($allProjects as $project) {
                // One time projects add their paid amount
                if ($project->billing_type === 'one_time') {
                    $allTimeRevenue += $project->paid_amount;
                    if ($project->created_at->format('m-Y') === "{$month}-{$year}") {
                        $monthlyRevenue += $project->paid_amount;
                    }
                } else {
                    // Monthly projects sum their paid cycles
                    $paidCycles = $project->cycles()->where('is_paid', true)->get();
                    $allTimeRevenue += $paidCycles->sum('amount');
                    $monthlyRevenue += $paidCycles->filter(function ($c) use ($month, $year) {
                        return $c->paid_at && $c->paid_at->format('m-Y') === "{$month}-{$year}";
                    })->sum('amount');
                }
            }

            return [
                'name' => $service->name,
                'all_time_revenue' => $allTimeRevenue,
                'monthly_revenue' => $monthlyRevenue,
            ];
        });

        // Fetch employees and their projects
        $employees = User::where('role', 'employee')->with(['projects'])->get();

        $employeeStats = $employees->map(function ($emp) use ($month, $year) {
            $allProjects = $emp->projects;
            
            $allTimeRevenue = 0;
            $monthlyRevenue = 0;

            foreach ($allProjects as $project) {
                if ($project->billing_type === 'one_time') {
                    $allTimeRevenue += $project->paid_amount;
                    if ($project->created_at->format('m-Y') === "{$month}-{$year}") {
                        $monthlyRevenue += $project->paid_amount;
                    }
                } else {
                    $paidCycles = $project->cycles()->where('is_paid', true)->get();
                    $allTimeRevenue += $paidCycles->sum('amount');
                    $monthlyRevenue += $paidCycles->filter(function ($c) use ($month, $year) {
                        return $c->paid_at && $c->paid_at->format('m-Y') === "{$month}-{$year}";
                    })->sum('amount');
                }
            }

            $monthlySalaryPaid = \App\Models\SalaryPayment::where('user_id', $emp->id)->where('month', (int)$month)->where('year', (int)$year)->sum('amount');
            $allTimeSalaryPaid = \App\Models\SalaryPayment::where('user_id', $emp->id)->sum('amount');

            return [
                'name' => $emp->name,
                'all_time_revenue' => $allTimeRevenue,
                'monthly_revenue' => $monthlyRevenue,
                'monthly_salary' => $monthlySalaryPaid,
                'all_time_salary' => $allTimeSalaryPaid,
                'monthly_profit' => $monthlyRevenue - $monthlySalaryPaid,
                'all_time_profit' => $allTimeRevenue - $allTimeSalaryPaid,
            ];
        });

        // ---------------------------------------------------------
        // Calculate Company Totals (Revenue, Salaries, Profit)
        // ---------------------------------------------------------

        // 1. Total Revenue (sum of all service stats)
        $totalMonthlyRevenue = $serviceStats->sum('monthly_revenue');
        $totalAllTimeRevenue = $serviceStats->sum('all_time_revenue');

        // 2. Total Salaries (from SalaryPayment)
        $totalMonthlySalaries = \App\Models\SalaryPayment::where('month', (int)$month)->where('year', (int)$year)->sum('amount');
        $totalAllTimeSalaries = \App\Models\SalaryPayment::sum('amount');

        // 3. Net Profit
        $monthlyNetProfit = $totalMonthlyRevenue - $totalMonthlySalaries;
        $allTimeNetProfit = $totalAllTimeRevenue - $totalAllTimeSalaries;

        $financialSummary = [
            'monthly_revenue' => $totalMonthlyRevenue,
            'monthly_salaries' => $totalMonthlySalaries,
            'monthly_net_profit' => $monthlyNetProfit,
            'all_time_revenue' => $totalAllTimeRevenue,
            'all_time_salaries' => $totalAllTimeSalaries,
            'all_time_net_profit' => $allTimeNetProfit,
        ];

        return view('admin.reports.index', compact(
            'serviceStats', 
            'employeeStats', 
            'month', 
            'year', 
            'selectedDate',
            'financialSummary'
        ));
    }
}
