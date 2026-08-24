<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\SalaryPayment;
use App\Models\Expense;
use App\Models\ProjectPayment;
use App\Models\ProjectCycle;

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
                'date' => $e->date ?? $e->created_at,
                'type' => 'expense',
                'amount' => $e->amount,
                'description' => __('Expense') . ': ' . $e->name,
                'direction' => 'out'
            ];
        });

        // Fetch Incoming: Project Payments (Installments)
        $projectPayments = ProjectPayment::with('project.client', 'project.service')->where('status', 'paid')->get()->map(function($pp) {
            return [
                'date' => $pp->paid_at ?? $pp->due_date,
                'type' => 'project_payment',
                'amount' => $pp->amount,
                'description' => __('Project Installment') . ' (' . ($pp->project && $pp->project->service ? $pp->project->service->name : '') . ' - ' . ($pp->project && $pp->project->client ? $pp->project->client->name : '') . '): ' . $pp->title,
                'direction' => 'in'
            ];
        });

        // Fetch Incoming: Project Cycles (Subscriptions)
        $projectCycles = ProjectCycle::with('project.client', 'project.service')->where('is_paid', true)->get()->map(function($pc) {
            return [
                'date' => $pc->paid_at ?? $pc->billing_date,
                'type' => 'project_cycle',
                'amount' => $pc->amount,
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
            ->sortByDesc('date')
            ->values();

        $totalIn = $transactions->where('direction', 'in')->sum('amount');
        $totalOut = $transactions->where('direction', 'out')->sum('amount');
        $currentBalance = $budget + $totalIn - $totalOut;

        return view('admin.treasury.index', compact('transactions', 'budget', 'totalIn', 'totalOut', 'currentBalance'));
    }
}
