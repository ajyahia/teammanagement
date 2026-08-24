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
        $projects = Project::with(['client', 'service', 'employees'])->latest()->paginate(10);
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
            'billing_type' => 'required|string|in:one_time,monthly,per_page,yearly',
            'subscription_status' => 'nullable|string|in:active,stopped',
            'page_count' => 'nullable|integer|min:1|required_if:billing_type,per_page',
            'price_per_page' => 'nullable|numeric|min:0|required_if:billing_type,per_page',
            'notes' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
        ]);

        if (!in_array($data['billing_type'], ['monthly', 'yearly'])) {
            $data['subscription_status'] = null;
        }
        
        if ($data['billing_type'] === 'per_page') {
            $data['agreed_price'] = $data['page_count'] * $data['price_per_page'];
        } else {
            $data['page_count'] = null;
            $data['price_per_page'] = null;
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
            'billing_type' => 'required|string|in:one_time,monthly,per_page,yearly',
            'subscription_status' => 'nullable|string|in:active,stopped',
            'page_count' => 'nullable|integer|min:1|required_if:billing_type,per_page',
            'price_per_page' => 'nullable|numeric|min:0|required_if:billing_type,per_page',
            'notes' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
        ]);

        if (!in_array($data['billing_type'], ['monthly', 'yearly'])) {
            $data['subscription_status'] = null;
        }
        
        if ($data['billing_type'] === 'per_page') {
            $data['agreed_price'] = $data['page_count'] * $data['price_per_page'];
        } else {
            $data['page_count'] = null;
            $data['price_per_page'] = null;
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
