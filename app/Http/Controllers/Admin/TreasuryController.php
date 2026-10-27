<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\SalaryPayment;
use App\Models\Expense;
use App\Models\ProjectPayment;
use App\Models\ProjectCycle;
use App\Models\PartnerContribution;
use App\Models\VendorPayment;

class TreasuryController extends Controller
{
    public function index()
    {
        $budget = (float) Setting::getVal('company_budget', 0);

        // Fetch Outgoing: Salary Payments
        $salaryPayments = SalaryPayment::with('user')->get()->map(function($sp) {
            return [
                'date' => $sp->paid_at ?? $sp->created_at,
                'type' => 'salary',
                'amount' => $sp->amount,
                'description' => __('Salary Payment') . ': ' . ($sp->user ? $sp->user->name : __('Unknown')) . ($sp->notes ? ' - ' . $sp->notes : ''),
                'direction' => 'out'
            ];
        });

        // Fetch Outgoing: Expenses
        $expenses = Expense::all()->map(function($e) {
            return [
                'date' => $e->expense_date ?? $e->created_at,
                'type' => 'expense',
                'amount' => (float) $e->amount,
                'description' => __('Expense') . ': ' . $e->title,
                'direction' => 'out'
            ];
        });

        // Fetch Outgoing: Payments to external workers
        $vendorPayments = VendorPayment::with('vendor.project.service')->where('status', 'paid')->get()->map(function($vp) {
            $project = $vp->vendor?->project;
            return [
                'date' => $vp->paid_at ?? $vp->due_date,
                'type' => 'vendor_payment',
                'amount' => (float) $vp->amount,
                'description' => __('External Worker Payment') . ' (' . ($vp->vendor?->name ?? '') . ' - ' . ($project?->service?->name ?? '') . ')' . ($vp->title ? ': ' . $vp->title : ''),
                'direction' => 'out'
            ];
        });

        // Fetch Incoming: Partner capital contributions
        $partnerContributions = PartnerContribution::with('partner.project.service')->where('status', 'paid')->get()->map(function($pc) {
            $project = $pc->partner?->project;
            return [
                'date' => $pc->paid_at ?? $pc->due_date,
                'type' => 'partner_contribution',
                'amount' => (float) $pc->amount,
                'description' => __('Partner Contribution') . ' (' . ($pc->partner?->name ?? '') . ' - ' . ($project?->service?->name ?? '') . ')',
                'direction' => 'in'
            ];
        });

        // Fetch Incoming: Project Payments (Installments & Cycle Payments)
        $projectPayments = ProjectPayment::with('project.client', 'project.service', 'cycle')->where('status', 'paid')->get()->map(function($pp) {
            $context = '';
            if ($pp->cycle) {
                $context = ' [' . $pp->cycle->billing_date->format('M Y') . ']';
            }
            return [
                'date' => $pp->paid_at ?? $pp->due_date,
                'type' => 'project_payment',
                'amount' => (float) $pp->amount,
                'description' => __('Payment/Installment') . ' (' . ($pp->project && $pp->project->service ? $pp->project->service->name : '') . ' - ' . ($pp->project && $pp->project->client ? $pp->project->client->name : '') . '): ' . $pp->title . $context,
                'direction' => 'in'
            ];
        });

        // Fetch Incoming: Legacy/Direct Project Cycles without individual payment records
        $projectCycles = ProjectCycle::with('project.client', 'project.service')
            ->where('is_paid', true)
            ->whereDoesntHave('payments')
            ->get()
            ->map(function($pc) {
                return [
                    'date' => $pc->paid_at ?? $pc->billing_date,
                    'type' => 'project_cycle',
                    'amount' => (float) $pc->amount,
                    'description' => __('Subscription Payment') . ' (' . ($pc->project && $pc->project->service ? $pc->project->service->name : '') . ' - ' . ($pc->project && $pc->project->client ? $pc->project->client->name : '') . '): ' . $pc->billing_date->format('M Y'),
                    'direction' => 'in'
                ];
            });

        // Combine all transactions
        $transactions = collect([])
            ->concat($salaryPayments)
            ->concat($expenses)
            ->concat($projectPayments)
            ->concat($projectCycles)
            ->concat($partnerContributions)
            ->concat($vendorPayments)
            ->sortByDesc('date')
            ->values();

        $totalIn = $transactions->where('direction', 'in')->sum('amount');
        $totalOut = $transactions->where('direction', 'out')->sum('amount');
        $currentBalance = $budget + $totalIn - $totalOut;

        return view('admin.treasury.index', compact('transactions', 'budget', 'totalIn', 'totalOut', 'currentBalance'));
    }
}
