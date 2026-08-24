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

    public function generateAll()
    {
        $projects = \App\Models\Project::whereIn('billing_type', ['monthly', 'yearly'])
            ->where('subscription_status', 'active')
            ->get();

        $generatedCount = 0;
        $now = now();

        foreach ($projects as $project) {
            $exists = false;
            
            if ($project->billing_type === 'monthly') {
                $exists = $project->cycles()
                    ->whereMonth('billing_date', $now->month)
                    ->whereYear('billing_date', $now->year)
                    ->exists();
            } else if ($project->billing_type === 'yearly') {
                $exists = $project->cycles()
                    ->whereYear('billing_date', $now->year)
                    ->exists();
            }

            if (!$exists) {
                $project->cycles()->create([
                    'billing_date' => $now->copy()->startOfMonth(),
                    'amount' => $project->agreed_price,
                    'is_paid' => false,
                ]);
                $generatedCount++;
            }
        }

        return redirect()->back()->with('success', $generatedCount . ' ' . __('new billing cycles generated.'));
    }
}
