<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = trim((string) $request->input('q'));

        $query = Project::query()
            ->when(in_array($status, ['pending', 'in_progress', 'completed', 'cancelled']), fn($q) => $q->where('status', $status))
            ->when($search !== '', fn($q) => $q->where(function ($w) use ($search) {
                $w->whereHas('client', fn($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('service', fn($s) => $s->where('name', 'like', "%{$search}%"));
            }));

        // Totals cover every matching project, not just the visible page
        $totals = (clone $query)->with(['payments', 'cycles.payments'])->get();
        $summary = [
            'revenue' => $totals->sum('total_revenue'),
            'paid' => $totals->sum('total_paid'),
            'due' => $totals->sum('due_amount'),
        ];
        $counts = Project::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $projects = $query->with(['client', 'service', 'employees', 'payments', 'cycles.payments', 'pauses'])
            ->latest()->paginate(10)->withQueryString();

        return view('admin.projects.index', compact('projects', 'summary', 'counts', 'status', 'search'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $services = Service::with('components')->orderBy('name')->get();
        $employees = User::with('services')->where('role', 'employee')->activeOn()->orderBy('name')->get();
        return view('admin.projects.create', compact('clients', 'services', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'assignments' => 'nullable|array',
            'assignments.*' => 'nullable|array',
            'assignments.*.*' => 'exists:users,id',
            'agreed_price' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'paid_at' => 'nullable|date',
            'installment_amount' => 'nullable|numeric|min:0.01',
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'billing_type' => 'required|string|in:one_time,monthly,per_page,yearly',
            'subscription_status' => 'nullable|string|in:active,stopped',
            'page_count' => 'nullable|integer|min:1|required_if:billing_type,per_page',
            'price_per_page' => 'nullable|numeric|min:0|required_if:billing_type,per_page',
            'notes' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
        ]);

        if (!in_array($data['billing_type'], ['monthly', 'yearly'])) {
            $data['subscription_status'] = null;
        }

        if ($data['billing_type'] === 'per_page') {
            $data['agreed_price'] = $data['page_count'] * $data['price_per_page'];
        } else {
            $data['page_count'] = null;
            $data['price_per_page'] = null;
        }

        $employeeIds = $this->assignmentPivot($data['assignments'] ?? []);
        $initialPaid = (float) ($data['paid_amount'] ?? 0);
        $initialPaidAt = $data['paid_at'] ?? now()->toDateString();
        $installmentAmount = (float) ($data['installment_amount'] ?? 0);
        unset($data['assignments'], $data['paid_at'], $data['installment_amount']);

        // Money paid at creation is recorded as a real installment so it shows in
        // the treasury, reports and subscription totals (paid_amount is legacy only).
        $data['paid_amount'] = 0;

        if (in_array($data['status'], ['completed', 'cancelled'])) {
            $data['ended_at'] = today()->toDateString();
        }

        $project = Project::create($data);
        $project->employees()->sync($employeeIds);

        if ($initialPaid > 0) {
            $project->payments()->create([
                'title' => __('Initial Payment'),
                'amount' => $initialPaid,
                'due_date' => $initialPaidAt,
                'paid_at' => $initialPaidAt,
                'status' => 'paid',
            ]);
        }

        // Split the remaining balance into monthly pending installments
        // (the last one takes whatever is left). Subscriptions use cycles instead.
        $remaining = round((float) $project->agreed_price - $initialPaid, 2);
        if ($installmentAmount > 0 && $remaining > 0 && !in_array($project->billing_type, ['monthly', 'yearly'])) {
            $dueDate = \Carbon\Carbon::parse($initialPaidAt);
            for ($i = 1; $remaining > 0 && $i <= 120; $i++) {
                $amount = min($installmentAmount, $remaining);
                $project->payments()->create([
                    'title' => __('Installment') . ' ' . $i,
                    'amount' => $amount,
                    'due_date' => $dueDate->copy()->addMonthsNoOverflow($i)->toDateString(),
                    'status' => 'pending',
                ]);
                $remaining = round($remaining - $amount, 2);
            }
        }

        return redirect()->route('admin.projects.index')->with('success', __('Project created successfully.'));
    }

    public function edit(Project $project)
    {
        $clients = Client::orderBy('name')->get();
        $services = Service::with('components')->orderBy('name')->get();
        // Keep already-assigned employees selectable even if they were deactivated
        $assignedIds = $project->employees()->pluck('users.id');
        $employees = User::with('services')->where('role', 'employee')
            ->where(fn($q) => $q->activeOn()->orWhereIn('id', $assignedIds))
            ->orderBy('name')->get();
        return view('admin.projects.edit', compact('project', 'clients', 'services', 'employees'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'assignments' => 'nullable|array',
            'assignments.*' => 'nullable|array',
            'assignments.*.*' => 'exists:users,id',
            'agreed_price' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'billing_type' => 'required|string|in:one_time,monthly,per_page,yearly',
            'subscription_status' => 'nullable|string|in:active,stopped',
            'page_count' => 'nullable|integer|min:1|required_if:billing_type,per_page',
            'price_per_page' => 'nullable|numeric|min:0|required_if:billing_type,per_page',
            'notes' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
        ]);

        if (!in_array($data['billing_type'], ['monthly', 'yearly'])) {
            $data['subscription_status'] = null;
        }
        
        if ($data['billing_type'] === 'per_page') {
            $data['agreed_price'] = $data['page_count'] * $data['price_per_page'];
        } else {
            $data['page_count'] = null;
            $data['price_per_page'] = null;
        }

        $employeeIds = $this->assignmentPivot($data['assignments'] ?? []);
        unset($data['assignments']);

        // Remember the day the project was closed; reopening clears it.
        // A stopped subscription is not closed: it keeps a stop/resume history instead.
        $closing = in_array($data['status'], ['completed', 'cancelled']);
        $data['ended_at'] = $closing ? ($project->ended_at ?? today()->toDateString()) : null;

        $newStatus = $data['subscription_status'] ?? null;
        unset($data['subscription_status']);

        $project->update($data);

        if (in_array($project->billing_type, ['monthly', 'yearly'])) {
            if ($newStatus === 'stopped') {
                $project->stopSubscription();
            } elseif ($newStatus === 'active') {
                $project->resumeSubscription();
            }
        } else {
            $project->update(['subscription_status' => null]);
        }
        $project->employees()->sync($employeeIds);
        return redirect()->route('admin.projects.index')->with('success', __('Project updated successfully.'));
    }

    /** Stop a subscription or start it again from the projects list. */
    public function toggleSubscription(Project $project)
    {
        abort_unless($project->isSubscription(), 404);

        if ($project->subscription_status === 'stopped') {
            $project->resumeSubscription();
            $message = __('Subscription started again.');
        } else {
            $project->stopSubscription();
            $message = __('Subscription stopped.');
        }

        return back()->with('success', $message);
    }

    /**
     * [roleServiceId => [userIds]] -> sync() payload [userId => ['service_id' => roleServiceId]].
     * A user picked for several roles is stored once, under the first role.
     */
    private function assignmentPivot(array $assignments): array
    {
        $pivot = [];
        foreach ($assignments as $serviceId => $userIds) {
            foreach ((array) $userIds as $userId) {
                $pivot[(int) $userId] ??= ['service_id' => (int) $serviceId];
            }
        }

        return $pivot;
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('admin.projects.index')->with('success', __('Project deleted successfully.'));
    }
}
