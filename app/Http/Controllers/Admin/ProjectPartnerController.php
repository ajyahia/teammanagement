<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerContribution;
use App\Models\Project;
use App\Models\ProjectPartner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProjectPartnerController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'share_percent' => 'required|numeric|min:0.01|max:100',
            'committed_capital' => 'required|numeric|min:0',
            'months' => 'nullable|integer|min:1|max:120',
            'first_due_date' => 'nullable|date',
        ]);

        if ($error = $this->shareError($project, (float) $data['share_percent'])) {
            return back()->withInput()->withErrors(['share_percent' => $error]);
        }

        $partner = $project->partners()->create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'share_percent' => $data['share_percent'],
            'committed_capital' => $data['committed_capital'],
        ]);

        // Split the committed capital into equal monthly pending contributions
        $capital = round((float) $data['committed_capital'], 2);
        $months = (int) ($data['months'] ?? 0);
        if ($capital > 0 && $months > 0) {
            $start = Carbon::parse($data['first_due_date'] ?? now()->toDateString());
            $each = round($capital / $months, 2);
            $left = $capital;
            for ($i = 0; $i < $months; $i++) {
                $amount = $i === $months - 1 ? $left : $each;
                $partner->contributions()->create([
                    'amount' => $amount,
                    'due_date' => $start->copy()->addMonthsNoOverflow($i)->toDateString(),
                    'status' => 'pending',
                ]);
                $left = round($left - $amount, 2);
            }
        }

        return back()->with('success', __('Partner added successfully.'));
    }

    public function update(Request $request, ProjectPartner $partner)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'share_percent' => 'required|numeric|min:0.01|max:100',
            'committed_capital' => 'required|numeric|min:0',
        ]);

        if ($error = $this->shareError($partner->project, (float) $data['share_percent'], $partner->id)) {
            return back()->withInput()->withErrors(['share_percent' => $error]);
        }

        $partner->update($data);

        return back()->with('success', __('Partner updated successfully.'));
    }

    public function destroy(ProjectPartner $partner)
    {
        $partner->delete();

        return back()->with('success', __('Partner deleted successfully.'));
    }

    public function storeContribution(Request $request, ProjectPartner $partner)
    {
        $partner->contributions()->create($this->validateContribution($request));

        return back()->with('success', __('Contribution added successfully.'));
    }

    public function updateContribution(Request $request, PartnerContribution $contribution)
    {
        $contribution->update($this->validateContribution($request));

        return back()->with('success', __('Contribution updated successfully.'));
    }

    public function destroyContribution(PartnerContribution $contribution)
    {
        $contribution->delete();

        return back()->with('success', __('Contribution deleted successfully.'));
    }

    private function validateContribution(Request $request): array
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'paid_at' => 'nullable|date',
            'status' => 'required|string|in:pending,paid',
            'notes' => 'nullable|string',
        ]);

        $data['paid_at'] = $data['status'] === 'paid' ? ($data['paid_at'] ?? now()->toDateString()) : null;

        return $data;
    }

    /** The partner shares plus the owner remainder must not exceed 100%. */
    private function shareError(Project $project, float $share, ?int $ignoreId = null): ?string
    {
        $others = (float) $project->partners()
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->sum('share_percent');

        return $others + $share > 100 ? __('Total partner shares cannot exceed 100%.') : null;
    }
}
