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
     * Profit of every service for one month.
     *
     *   revenue earned by the service's projects (by the months the client works with us)
     * - salaries of the people on the service's team (paid salary, or the base salary when not paid yet);
     *   someone on several services is split equally between them
     * - share of the general expenses, by headcount (4 of 10 people = 40% of the expenses)
     * - expenses booked on its projects and the cost of its external workers
     * = profit
     *
     * Employees with no service go to a "no service" row so the totals still match the company.
     *
     * @return array{rows: array<int, array<string, mixed>>, general_expenses: float, headcount: int}
     */
    public function serviceProfit(int $month, int $year): array
    {
        $ym = sprintf('%04d-%02d', $year, $month);
        $upToNow = $ym <= now()->format('Y-m');

        $services = \App\Models\Service::get(['id', 'name'])->keyBy('id');
        $rows = [];
        foreach ($services as $id => $service) {
            $rows[$id] = $this->emptyServiceRow($service->name);
        }
        $rows[0] = $this->emptyServiceRow(null);

        // Team and salaries
        $paid = SalaryPayment::where('month', $month)->where('year', $year)->get()->groupBy('user_id')->map->sum('amount');
        $employees = \App\Models\User::where('role', 'employee')->activeInMonth($month, $year)->with('services:id')->get();

        foreach ($employees as $e) {
            $salary = $upToNow ? (float) ($paid[$e->id] ?? $e->salary ?? 0) : 0.0;
            $ids = $e->services->pluck('id')->filter(fn($id) => isset($rows[$id]))->values();
            $ids = $ids->isEmpty() ? collect([0]) : $ids;
            $weight = 1 / $ids->count();

            foreach ($ids as $id) {
                $rows[$id]['headcount'] += $weight;
                $rows[$id]['salaries'] += $salary * $weight;
                $rows[$id]['team'][] = ['name' => $e->name, 'salary' => round($salary * $weight, 2), 'share' => $weight];
            }
        }
        $headcount = max(1, $employees->count());

        // Expenses: project ones go straight to the project's service, general ones are shared by headcount
        $expenses = Expense::whereMonth('expense_date', $month)->whereYear('expense_date', $year)->with('project:id,service_id')->get();
        $general = (float) $expenses->whereNull('project_id')->sum('amount');
        foreach ($expenses->whereNotNull('project_id') as $x) {
            $sid = $x->project?->service_id;
            if ($sid && isset($rows[$sid])) {
                $rows[$sid]['project_expenses'] += (float) $x->amount;
            }
        }

        // Revenue and external workers of the service's projects, by month of the period
        foreach ($this->projects()->get() as $p) {
            if (!isset($rows[$p->service_id])) {
                continue;
            }
            $rows[$p->service_id]['revenue'] += (float) ($p->revenueByMonth()[$ym] ?? 0);
            $rows[$p->service_id]['vendors'] += (float) ($p->vendorCostByMonth()[$ym] ?? 0);
            $rows[$p->service_id]['projects'] += isset($p->revenueByMonth()[$ym]) ? 1 : 0;
        }

        foreach ($rows as $id => &$r) {
            $r['general_expenses'] = $general * $r['headcount'] / $headcount;
            $r['cost'] = $r['salaries'] + $r['general_expenses'] + $r['project_expenses'] + $r['vendors'];
            $r['profit'] = $r['revenue'] - $r['cost'];
            $r['share_percent'] = round($r['headcount'] / $headcount * 100, 1);
        }
        unset($r);

        // Hide empty rows (a service with no team, no revenue and no costs; "no service" with nobody)
        $rows = array_filter($rows, fn($r) => $r['headcount'] > 0 || $r['revenue'] != 0 || $r['cost'] != 0);
        uasort($rows, fn($a, $b) => $b['profit'] <=> $a['profit']);

        return ['rows' => array_values($rows), 'general_expenses' => $general, 'headcount' => $employees->count()];
    }

    private function emptyServiceRow(?string $name): array
    {
        return [
            'name' => $name, 'projects' => 0, 'headcount' => 0.0, 'team' => [],
            'revenue' => 0.0, 'salaries' => 0.0, 'general_expenses' => 0.0,
            'project_expenses' => 0.0, 'vendors' => 0.0, 'cost' => 0.0, 'profit' => 0.0, 'share_percent' => 0.0,
        ];
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
