<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['client', 'service', 'employees'])->latest()->get();
        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $employees = User::with('services')->where('role', 'employee')->orderBy('name')->get();
        return view('admin.projects.create', compact('clients', 'services', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:users,id',
            'agreed_price' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'billing_type' => 'required|string|in:one_time,monthly',
            'subscription_status' => 'nullable|string|in:active,stopped',
            'notes' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
        ]);

        if ($data['billing_type'] !== 'monthly') {
            $data['subscription_status'] = null;
        }

        $employeeIds = $data['employee_ids'] ?? [];
        unset($data['employee_ids']);

        $project = Project::create($data);
        $project->employees()->sync($employeeIds);
        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');
    }

    public function edit(Project $project)
    {
        $clients = Client::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $employees = User::with('services')->where('role', 'employee')->orderBy('name')->get();
        return view('admin.projects.edit', compact('project', 'clients', 'services', 'employees'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:users,id',
            'agreed_price' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'billing_type' => 'required|string|in:one_time,monthly',
            'subscription_status' => 'nullable|string|in:active,stopped',
            'notes' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
        ]);

        if ($data['billing_type'] !== 'monthly') {
            $data['subscription_status'] = null;
        }

        $employeeIds = $data['employee_ids'] ?? [];
        unset($data['employee_ids']);

        $project->update($data);
        $project->employees()->sync($employeeIds);
        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }
}
