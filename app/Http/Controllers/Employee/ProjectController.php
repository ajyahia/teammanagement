<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        // Get projects assigned to this employee
        $projects = Project::with(['client', 'service'])
            ->where('employee_id', Auth::id())
            ->latest()
            ->get();
            
        return view('employee.projects.index', compact('projects'));
    }

    public function updateStatus(Request $request, Project $project)
    {
        // Ensure the project belongs to this employee
        if ($project->employee_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed',
        ]);

        $project->update(['status' => $data['status']]);
        
        return redirect()->route('employee.projects.index')->with('success', 'Project status updated successfully.');
    }
}
