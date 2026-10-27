<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectPayment;
use Illuminate\Http\Request;

class ProjectPaymentController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'cycle_id' => 'nullable|exists:project_cycles,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'paid_at' => 'nullable|date',
            'status' => 'required|string|in:pending,paid,overdue',
            'notes' => 'nullable|string',
        ]);

        if (!empty($data['cycle_id'])) {
            $cycleExists = $project->cycles()->where('id', $data['cycle_id'])->exists();
            if (!$cycleExists) {
                $data['cycle_id'] = null;
            }
        }

        if ($data['status'] === 'paid') {
            $data['paid_at'] = $data['paid_at'] ?? now();
        } else {
            $data['paid_at'] = null;
        }

        $payment = $project->payments()->create($data);

        if ($payment->cycle_id) {
            $payment->cycle->syncPaidStatus();
        }

        return back()->with('success', __('Payment/Installment added successfully.'));
    }

    /**
     * Split what is still unscheduled (agreed price - paid - already pending/overdue)
     * into monthly pending installments. Fixed-price projects only.
     */
    public function generate(Request $request, Project $project)
    {
        $data = $request->validate([
            'installment_amount' => 'required|numeric|min:0.01',
            'first_due_date' => 'required|date',
        ]);

        if (in_array($project->billing_type, ['monthly', 'yearly'])) {
            return back()->withErrors(['installment_amount' => __('Subscription projects use billing cycles instead of installments.')]);
        }

        $scheduled = (float) $project->payments()->whereIn('status', ['pending', 'overdue'])->sum('amount');
        $remaining = round((float) $project->agreed_price - $project->total_paid - $scheduled, 2);

        if ($remaining <= 0) {
            return back()->withErrors(['installment_amount' => __('Nothing left to schedule: the agreed price is already covered.')]);
        }

        $start = \Carbon\Carbon::parse($data['first_due_date']);
        $each = (float) $data['installment_amount'];
        $existing = $project->payments()->count();

        for ($i = 0; $remaining > 0 && $i < 120; $i++) {
            $amount = min($each, $remaining);
            $project->payments()->create([
                'title' => __('Installment') . ' ' . ($existing + $i + 1),
                'amount' => $amount,
                'due_date' => $start->copy()->addMonthsNoOverflow($i)->toDateString(),
                'status' => 'pending',
            ]);
            $remaining = round($remaining - $amount, 2);
        }

        return back()->with('success', __('Installments generated successfully.'));
    }

    public function update(Request $request, ProjectPayment $payment)
    {
        $data = $request->validate([
            'cycle_id' => 'nullable|exists:project_cycles,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'paid_at' => 'nullable|date',
            'status' => 'required|string|in:pending,paid,overdue',
            'notes' => 'nullable|string',
        ]);

        if (isset($data['cycle_id']) && !empty($data['cycle_id'])) {
            $cycleExists = $payment->project->cycles()->where('id', $data['cycle_id'])->exists();
            if (!$cycleExists) {
                $data['cycle_id'] = null;
            }
        }

        if ($data['status'] === 'paid') {
            // Use the entered paid date, otherwise keep the existing one, otherwise today
            $data['paid_at'] = $data['paid_at']
                ?? ($payment->status === 'paid' && $payment->paid_at ? $payment->paid_at : now());
        } else {
            $data['paid_at'] = null;
        }

        $oldCycle = $payment->cycle;
        $payment->update($data);
        $payment->load('cycle');

        if ($oldCycle) {
            $oldCycle->syncPaidStatus();
        }
        if ($payment->cycle && (!$oldCycle || $oldCycle->id !== $payment->cycle->id)) {
            $payment->cycle->syncPaidStatus();
        }

        return back()->with('success', __('Payment/Installment updated successfully.'));
    }

    public function destroy(ProjectPayment $payment)
    {
        $cycle = $payment->cycle;
        $payment->delete();

        if ($cycle) {
            $cycle->syncPaidStatus();
        }

        return back()->with('success', __('Payment/Installment deleted successfully.'));
    }
}
