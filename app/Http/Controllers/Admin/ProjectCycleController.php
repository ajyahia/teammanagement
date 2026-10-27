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

        return redirect()->back()->with('success', __('Billing cycle added successfully.'));
    }

    public function update(Request $request, \App\Models\ProjectCycle $cycle)
    {
        $data = $request->validate([
            'billing_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        $cycle->update($data);

        // Cycles paid through installments must be re-checked against the new amount
        if ($cycle->payments()->exists()) {
            $cycle->syncPaidStatus();
        }

        return redirect()->back()->with('success', __('Billing cycle updated successfully.'));
    }

    public function togglePaid(\App\Models\ProjectCycle $cycle)
    {
        if ($cycle->is_paid) {
            // Mark as unpaid
            $cycle->is_paid = false;
            $cycle->paid_at = null;
            $cycle->save();

            // Set any paid payments on this cycle to pending
            $cycle->payments()->where('status', 'paid')->update([
                'status' => 'pending',
                'paid_at' => null,
            ]);
        } else {
            // Mark as fully paid
            $cycle->is_paid = true;
            $cycle->paid_at = now();
            $cycle->save();

            if ($cycle->payments()->exists()) {
                // If it already has payments, mark all pending as paid
                $cycle->payments()->where('status', '!=', 'paid')->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            } else {
                // Automatically record a full payment for this cycle so Treasury tracks it accurately
                $cycle->project->payments()->create([
                    'cycle_id' => $cycle->id,
                    'title' => __('Full Cycle Payment') . ' (' . $cycle->billing_date->format('M Y') . ')',
                    'amount' => $cycle->amount,
                    'due_date' => $cycle->billing_date,
                    'paid_at' => now(),
                    'status' => 'paid',
                ]);
            }
        }

        return redirect()->back()->with('success', __('Cycle payment status updated.'));
    }

    public function destroy(\App\Models\ProjectCycle $cycle)
    {
        $cycle->delete();
        return redirect()->back()->with('success', __('Cycle deleted successfully.'));
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
