<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\PartnerContribution;
use App\Models\Project;
use App\Models\ProjectCycle;
use App\Models\ProjectPartner;
use App\Models\ProjectPayment;
use App\Models\SalaryPayment;
use App\Models\VendorPayment;

/**
 * Money in / money out for the financial report.
 *
 * Revenue    = what clients actually paid (counted on the paid date).
 * Costs      = salaries paid + general expenses + payments to external workers.
 * Net profit = revenue - costs. Partner capital is investment, not revenue.
 */
class ReportService
{
    /**
     * Month-by-month figures for a year.
     *
     * @return array<int, array{revenue: float, salaries: float, expenses: float, vendors: float, net: float}>
     */
    public function monthlySeries(int $year): array
    {
        $months = array_fill(1, 12, ['revenue' => 0.0, 'salaries' => 0.0, 'expenses' => 0.0, 'vendors' => 0.0]);

        ProjectPayment::where('status', 'paid')->whereYear('paid_at', $year)->get(['amount', 'paid_at'])
            ->each(function ($p) use (&$months) {
                $months[(int) $p->paid_at->format('n')]['revenue'] += (float) $p->amount;
            });

        // Cycles marked paid without payment records
        ProjectCycle::where('is_paid', true)->whereDoesntHave('payments')->whereYear('paid_at', $year)->get(['amount', 'paid_at'])
            ->each(function ($c) use (&$months) {
                $months[(int) $c->paid_at->format('n')]['revenue'] += (float) $c->amount;
            });

        SalaryPayment::where('year', $year)->get(['amount', 'month'])
            ->each(function ($s) use (&$months) {
                if ($s->month >= 1 && $s->month <= 12) {
                    $months[(int) $s->month]['salaries'] += (float) $s->amount;
                }
            });

        Expense::whereYear('expense_date', $year)->get(['amount', 'expense_date'])
            ->each(function ($e) use (&$months) {
                $months[(int) $e->expense_date->format('n')]['expenses'] += (float) $e->amount;
            });

        VendorPayment::where('status', 'paid')->whereYear('paid_at', $year)->get(['amount', 'paid_at'])
            ->each(function ($v) use (&$months) {
                $months[(int) $v->paid_at->format('n')]['vendors'] += (float) $v->amount;
            });

        foreach ($months as $m => $row) {
            $months[$m]['net'] = $row['revenue'] - $row['salaries'] - $row['expenses'] - $row['vendors'];
        }

        return $months;
    }

    /** Sum a monthlySeries() result. */
    public function totals(array $series): array
    {
        $t = ['revenue' => 0.0, 'salaries' => 0.0, 'expenses' => 0.0, 'vendors' => 0.0, 'net' => 0.0];
        foreach ($series as $row) {
            foreach ($t as $k => $_) {
                $t[$k] += $row[$k];
            }
        }

        return $t;
    }

    /** Statuses of a project that is still being worked on. */
    public const ACTIVE_STATUSES = ['pending', 'in_progress'];

    /**
     * Each employee's salary for the month, split across the active projects they are assigned to.
     * Returns [projectId => cost] and [userId => projects count].
     *
     * @return array{0: array<int, float>, 1: array<int, int>}
     */
    public function teamCostByProject(int $month, int $year): array
    {
        $salaries = SalaryPayment::where('month', $month)->where('year', $year)->get()
            ->groupBy('user_id')->map->sum('amount');

        $projects = Project::with('employees:id')->whereIn('status', self::ACTIVE_STATUSES)->get();

        $load = [];
        foreach ($projects as $p) {
            foreach ($p->employees as $e) {
                $load[$e->id] = ($load[$e->id] ?? 0) + 1;
            }
        }

        $cost = [];
        foreach ($projects as $p) {
            $cost[$p->id] = (float) $p->employees->sum(fn($e) => (float) ($salaries[$e->id] ?? 0) / max(1, $load[$e->id]));
        }

        return [$cost, $load];
    }

    /** One row per project: what came in, what went out, what is left. Best profit first. */
    public function projectProfitability(int $month = 0, int $year = 0)
    {
        [$teamCost] = $month && $year ? $this->teamCostByProject($month, $year) : [[]];

        return Project::with(['client', 'service', 'payments', 'cycles.payments', 'expenses', 'vendors.payments'])
            ->get()
            ->map(function ($p) use ($teamCost) {
                $received = (float) $p->total_paid;
                $vendors = (float) $p->vendor_paid;
                $expenses = (float) $p->expenses->sum('amount');

                return [
                    'id' => $p->id,
                    'client' => $p->client?->name,
                    'service' => $p->service?->name,
                    'status' => $p->status,
                    'agreed' => (float) $p->total_revenue,
                    'received' => $received,
                    'remaining' => (float) $p->due_amount,
                    'vendors' => $vendors,
                    'expenses' => $expenses,
                    'net' => round($received - $vendors - $expenses, 2),
                    'team_cost' => round($teamCost[$p->id] ?? 0, 2),
                ];
            })
            ->sortByDesc('net')
            ->values();
    }

    /**
     * What is still owed to us and what we still owe.
     *
     * @return array<string, mixed>
     */
    public function dues(): array
    {
        $today = today();

        $clientsOwe = (float) Project::with(['payments', 'cycles.payments'])->where('status', '!=', 'cancelled')->get()->sum('due_amount');

        $overdueFromClients = ProjectPayment::with('project.client', 'project.service')
            ->whereIn('status', ['pending', 'overdue'])->whereDate('due_date', '<', $today)
            ->orderBy('due_date')->get();

        $pendingVendors = VendorPayment::with('vendor.project.client')->where('status', '!=', 'paid')->orderBy('due_date')->get();
        $overdueVendors = $pendingVendors->filter(fn($v) => $v->due_date->lt($today))->values();

        $partnersOwe = (float) ProjectPartner::with('contributions')->get()->sum('remaining_capital');
        $overdueContributions = PartnerContribution::with('partner.project.service')
            ->where('status', '!=', 'paid')->whereDate('due_date', '<', $today)->orderBy('due_date')->get();

        return [
            'clients_owe' => $clientsOwe,
            'overdue_from_clients' => $overdueFromClients,
            'we_owe_vendors' => (float) $pendingVendors->sum('amount'),
            'overdue_vendors' => $overdueVendors,
            'partners_owe' => $partnersOwe,
            'overdue_contributions' => $overdueContributions,
        ];
    }
}
