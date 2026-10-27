<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectVendor;
use App\Models\VendorPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProjectVendorController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'agreed_cost' => 'required|numeric|min:0',
            'is_recurring' => 'nullable|boolean',
            'months' => 'nullable|integer|min:1|max:120',
            'first_due_date' => 'nullable|date',
        ]);

        $isSubscription = in_array($project->billing_type, ['monthly', 'yearly']);
        $recurring = $isSubscription && $request->boolean('is_recurring');

        $vendor = $project->vendors()->create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'agreed_cost' => $data['agreed_cost'],
            'is_recurring' => $recurring,
        ]);

        $cost = round((float) $data['agreed_cost'], 2);

        if ($recurring) {
            // One pending payment for every billing cycle that already exists;
            // future cycles add theirs automatically.
            foreach ($project->cycles as $cycle) {
                $vendor->payments()->create([
                    'cycle_id' => $cycle->id,
                    'title' => $cycle->billing_date->format('M Y'),
                    'amount' => $cost,
                    'due_date' => $cycle->billing_date->toDateString(),
                    'status' => 'pending',
                ]);
            }
        } elseif ($cost > 0 && !empty($data['months'])) {
            // Fixed job: split the total cost into equal monthly payments
            $months = (int) $data['months'];
            $start = Carbon::parse($data['first_due_date'] ?? now()->toDateString());
            $each = round($cost / $months, 2);
            $left = $cost;
            for ($i = 0; $i < $months; $i++) {
                $amount = $i === $months - 1 ? $left : $each;
                $vendor->payments()->create([
                    'title' => __('Payment') . ' ' . ($i + 1),
                    'amount' => $amount,
                    'due_date' => $start->copy()->addMonthsNoOverflow($i)->toDateString(),
                    'status' => 'pending',
                ]);
                $left = round($left - $amount, 2);
            }
        }

        return back()->with('success', __('External worker added successfully.'));
    }

    public function update(Request $request, ProjectVendor $vendor)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'agreed_cost' => 'required|numeric|min:0',
        ]);

        $vendor->update($data);

        return back()->with('success', __('External worker updated successfully.'));
    }

    public function destroy(ProjectVendor $vendor)
    {
        $vendor->delete();

        return back()->with('success', __('External worker deleted successfully.'));
    }

    public function storePayment(Request $request, ProjectVendor $vendor)
    {
        $vendor->payments()->create($this->validatePayment($request));

        return back()->with('success', __('Payment added successfully.'));
    }

    public function updatePayment(Request $request, VendorPayment $payment)
    {
        $payment->update($this->validatePayment($request));

        return back()->with('success', __('Payment updated successfully.'));
    }

    public function destroyPayment(VendorPayment $payment)
    {
        $payment->delete();

        return back()->with('success', __('Payment deleted successfully.'));
    }

    private function validatePayment(Request $request): array
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'paid_at' => 'nullable|date',
            'status' => 'required|string|in:pending,paid',
            'notes' => 'nullable|string',
        ]);

        $data['paid_at'] = $data['status'] === 'paid' ? ($data['paid_at'] ?? now()->toDateString()) : null;

        return $data;
    }
}
