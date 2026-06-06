<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $employee = auth()->user();
        return view('employee.profile.edit', compact('employee'));
    }

    /**
     * Update the employee's own profile.
     */
    public function update(Request $request)
    {
        $employee = auth()->user();

        $validatedData = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($employee->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validatedData['password'])) {
            unset($validatedData['password']);
        } else {
            $validatedData['password_text'] = $validatedData['password'];
        }

        $employee->update($validatedData);

        return redirect()->route('employee.profile.edit')->with('success', 'Profile updated successfully.');
    }
}
