<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CompanyPolicy;
use Illuminate\Http\Request;

class EmployeePolicyController extends Controller
{
    /**
     * Display the company policies for employees.
     */
    public function index()
    {
        $policies = CompanyPolicy::orderBy('sort_order', 'asc')->get();
        return view('employee.policies.index', compact('policies'));
    }
}
