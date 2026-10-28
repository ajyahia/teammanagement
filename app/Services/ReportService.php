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
 * Figures for the financial report, by the months a client works with us.
 *
 * Revenue    = the project price spread over the months of its period (subscriptions: every
 *              active month), whatever day the client paid. What was actually collected is
 *              shown next to it.
 * Costs      = salaries (by salary month) + general expenses + external workers (spread like
 *              the revenue).
 * Net profit = revenue - costs. Partner capital is investment, not revenue.
 */
class ReportService
{
    /** Projects with everything the monthly accrual needs. */
    private function projects()
    {
        return Project::with(['cycles', 'vendors', 'employees:id']);
    }

    /**
     * Month-by-month figures for a year.
     *
     * @return array<int, array{revenue: float, collected: float, salaries: float, expenses: float, vendors: float, net: float}>
     */
    public function monthlySeries(int $year): array
    {
        $months = array_fill(1, 12, ['revenue' => 0.0, 'collected' => 0.0, 'salaries' => 0.0, 'expenses' => 0.0, 'vendors' => 0.0]);
        $prefix = $year . '-';

        foreach ($this->projects()->get() as $project) {
            foreach ($project->revenueByMonth() as $ym => $amount) {
                if (str_starts_with($ym, $prefix)) {
                    $months[(int) substr($ym, 5, 2)]['revenue'] += $amount;
                }
            }
            foreach ($project->vendorCostByMonth() as $ym => $amount) {
                if (str_starts_with($ym, $prefix)) {
                    $months[(int) substr($ym, 5, 2)]['vendors'] += $amount;
                }
            }
        }

        // Cash actually received, shown for comparison
        ProjectPayment::where('status', 'paid')->whereYear('paid_at', $year)->get(['amount', 'paid_at'])
            ->each(function ($p) use (&$months) {
                $months[(int) $p->paid_at->format('n')]['collected'] += (float) $p->amount;
            });
        ProjectCycle::where('is_paid', true)->whereDoesntHave('payments')->whereYear('paid_at', $year)->get(['amount', 'paid_at'])
            ->each(function ($c) use (&$months) {
                $months[(int) $c->paid_at->format('n')]['collected'] += (float) $c->amount;
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

        foreach ($months as $m => $row) {
            $months[$m]['net'] = $row['revenue'] - $row['salaries'] - $row['expenses'] - $row['vendors'];
        }

        return $months;
    }

    /** Sum a monthlySeries() result. */
    public function totals(array $series): array
    {
        $t = ['revenue' => 0.0, 'collected' => 0.0, 'salaries' => 0.0, 'expenses' => 0.0, 'vendors' => 0.0, 'net' => 0.0];
        foreach ($series as $row) {
            foreach ($t as $k => $_) {
                $t[$k] += $row[$k];
            }
        }

        return $t;
    }

    /**
     * Each employee's salary for the month, split across the projects they work on that month
     * (a project counts for the months of its period).
     * Returns [projectId => cost] and [userId => projects count].
     *
     * @return array{0: array<int, float>, 1: array<int, int>}
     */
    public function teamCostByProject(int $month, int $year): array
    {
        $ym = sprintf('%04d-%02d', $year, $month);

        $salaries = SalaryPayment::where('month', $month)->where('year', $year)->get()
            ->groupBy('user_id')->map->sum('amount');

        $projects = $this->projects()->get()->filter(fn($p) => in_array($ym, $p->activeMonths(), true));

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

    /** One row per project: its period, what it earned, what came in, what went out. Best profit first. */
    public function projectProfitability(int $month = 0, int $year = 0)
    {
        [$teamCost] = $month && $year ? $this->teamCostByProject($month, $year) : [[]];
        $ym = $month && $year ? sprintf('%04d-%02d', $year, $month) : null;

        return Project::with(['client', 'service', 'payments', 'cycles.payments', 'expenses', 'vendors.payments'])
            ->get()
            ->map(function ($p) use ($teamCost, $ym) {
                $received = (float) $p->total_paid;
                $vendors = (float) $p->vendor_paid;
                $expenses = (float) $p->expenses->sum('amount');
                $months = $p->activeMonths();
                $earned = $p->revenueByMonth();

                return [
                    'id' => $p->id,
                    'client' => $p->client?->name,
                    'service' => $p->service?->name,
                    'status' => $p->status,
                    'period_from' => $p->periodStart()->format('Y-m'),
                    'period_to' => $p->periodEnd()->format('Y-m'),
                    'period_months' => count($months),
                    'running' => $p->isSubscription() ? ($p->subscription_status !== 'stopped' && !$p->ended_at) : in_array($p->status, Project::ACTIVE_STATUSES),
                    'agreed' => (float) $p->total_revenue,
                    'earned_month' => $ym ? (float) ($earned[$ym] ?? 0) : 0.0,
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
     * Revenue earned per service: this month, this year and everything up to now.
     *
     * @return array<int, array{name: string, projects_count: int, month: float, year: float, all: float}>
     */
    public function revenueByService(int $month, int $year)
    {
        $ym = sprintf('%04d-%02d', $year, $month);
        $now = now()->format('Y-m');

        return \App\Models\Service::with(['projects.cycles'])->get()->map(function ($service) use ($ym, $year, $now) {
            $row = ['name' => $service->name, 'projects_count' => $service->projects->count(), 'month' => 0.0, 'year' => 0.0, 'all' => 0.0];
            foreach ($service->projects as $project) {
                foreach ($project->revenueByMonth() as $m => $amount) {
                    if ($m <= $now) {
                        $row['all'] += $amount;
                    }
                    if (str_starts_with($m, $year . '-')) {
                        $row['year'] += $amount;
                    }
                    if ($m === $ym) {
                        $row['month'] += $amount;
                    }
                }
            }

            return $row;
        })->sortByDesc('year')->values();
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
