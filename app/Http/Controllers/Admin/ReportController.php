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
            
            $monthlyProjects = $allProjects->filter(function ($project) use ($month, $year) {
                return $project->created_at->format('m-Y') === "{$month}-{$year}";
            });

            return [
                'name' => $service->name,
                'all_time_revenue' => $allProjects->sum('agreed_price'),
                'all_time_profit' => $allProjects->sum('profit'),
                'monthly_revenue' => $monthlyProjects->sum('agreed_price'),
                'monthly_profit' => $monthlyProjects->sum('profit'),
            ];
        });

        // Fetch employees and their projects
        $employees = User::where('role', 'employee')->with(['projects'])->get();

        $employeeStats = $employees->map(function ($emp) use ($month, $year) {
            $allProjects = $emp->projects;

            $monthlyProjects = $allProjects->filter(function ($project) use ($month, $year) {
                return $project->created_at->format('m-Y') === "{$month}-{$year}";
            });

            return [
                'name' => $emp->name,
                'all_time_revenue' => $allProjects->sum('agreed_price'),
                'all_time_profit' => $allProjects->sum('profit'),
                'monthly_revenue' => $monthlyProjects->sum('agreed_price'),
                'monthly_profit' => $monthlyProjects->sum('profit'),
            ];
        });

        return view('admin.reports.index', compact('serviceStats', 'employeeStats', 'month', 'year', 'selectedDate'));
    }
}
