<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectCycleController extends Controller
{
    public function store(Request $request, \App\Models\Project $project)
    {
        $data = $request->validate([
            'billing_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        $project->cycles()->create([
            'billing_date' => $data['billing_date'],
            'amount' => $data['amount'],
            'is_paid' => false,
        ]);

        return redirect()->back()->with('success', 'Billing cycle added successfully.');
    }

    public function togglePaid(\App\Models\ProjectCycle $cycle)
    {
        $cycle->is_paid = !$cycle->is_paid;
        $cycle->paid_at = $cycle->is_paid ? now() : null;
        $cycle->save();

        return redirect()->back()->with('success', 'Cycle payment status updated.');
    }

    public function destroy(\App\Models\ProjectCycle $cycle)
    {
        $cycle->delete();
        return redirect()->back()->with('success', 'Cycle deleted successfully.');
    }
}
