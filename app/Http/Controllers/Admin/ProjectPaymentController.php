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
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|string|in:pending,paid,overdue',
            'notes' => 'nullable|string',
        ]);

        if ($data['status'] === 'paid') {
            $data['paid_at'] = now();
        } else {
            $data['paid_at'] = null;
        }

        $project->payments()->create($data);

        return back()->with('success', 'Payment/Installment added successfully.');
    }

    public function update(Request $request, ProjectPayment $payment)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|string|in:pending,paid,overdue',
            'notes' => 'nullable|string',
        ]);

        if ($data['status'] === 'paid' && $payment->status !== 'paid') {
            $data['paid_at'] = now();
        } elseif ($data['status'] !== 'paid') {
            $data['paid_at'] = null;
        }

        $payment->update($data);

        return back()->with('success', 'Payment/Installment updated successfully.');
    }

    public function destroy(ProjectPayment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Payment/Installment deleted successfully.');
    }
}
