<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index()
    {
        $employees = User::with('services')->where('role', 'employee')->get();
        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $services = Service::all();
        return view('admin.employees.create', compact('services'));
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in(['admin', 'employee'])],
            'salary' => ['required', 'numeric', 'min:0'],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:services,id'],
        ]);

        $validatedData['password_text'] = $validatedData['password'];
        $user = User::create($validatedData);

        if (isset($validatedData['services'])) {
            $user->services()->sync($validatedData['services']);
        }

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(User $employee)
    {
        $services = Service::all();
        $employee->load('services');
        return view('admin.employees.edit', compact('employee', 'services'));
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, User $employee)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($employee->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in(['admin', 'employee'])],
            'salary' => ['required', 'numeric', 'min:0'],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:services,id'],
        ]);

        if (empty($validatedData['password'])) {
            unset($validatedData['password']);
        } else {
            $validatedData['password_text'] = $validatedData['password'];
        }

        $employee->update($validatedData);

        if (isset($validatedData['services'])) {
            $employee->services()->sync($validatedData['services']);
        } else {
            $employee->services()->detach();
        }

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(User $employee)
    {
        if ($employee->id === auth()->id()) {
            return redirect()->route('admin.employees.index')->with('error', 'You cannot delete yourself.');
        }

        $employee->delete();
        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
    }
}
