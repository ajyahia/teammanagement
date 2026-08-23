<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $budget = \App\Models\Setting::getVal('company_budget', 0);
        return view('admin.settings.index', compact('budget'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_budget' => 'required|numeric|min:0',
        ]);

        \App\Models\Setting::setVal('company_budget', $request->company_budget);
        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }
