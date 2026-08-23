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

            return [
                'name' => $emp->name,
                'all_time_revenue' => $allTimeRevenue,
                'monthly_revenue' => $monthlyRevenue,
            ];
        });

        return view('admin.reports.index', compact('serviceStats', 'employeeStats', 'month', 'year', 'selectedDate'));
    }
}
