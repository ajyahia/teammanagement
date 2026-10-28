@extends('layouts.app')

@section('title', __('Financial Reports'))
@section('page_header', __('Financial Reports'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    @php
        $monthNames = collect(range(1, 12))->map(fn($m) => \Carbon\Carbon::create(2000, $m, 1)->translatedFormat('F'));
        $rows = [
            ['label' => __('Revenue (earned)'), 'key' => 'revenue', 'sign' => '+', 'color' => 'var(--green)'],
            ['label' => __('Salaries Paid'), 'key' => 'salaries', 'sign' => '−', 'color' => 'var(--red)'],
            ['label' => __('Expenses'), 'key' => 'expenses', 'sign' => '−', 'color' => 'var(--red)'],
            ['label' => __('External Workers'), 'key' => 'vendors', 'sign' => '−', 'color' => 'var(--red)'],
        ];
    @endphp

    <style>
        .rep-grid { display: grid; gap: 20px; margin-bottom: 24px; }
        .rep-grid.cols-2 { grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr); }
        .rep-grid.cols-3 { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
        .rep-title { font-family: var(--font-title); font-weight: 600; margin: 0 0 16px; }
        .rep-sub { color: var(--text-secondary); font-size: 0.85rem; margin: -10px 0 16px; }
        .rep-sum td, .rep-sum th { padding: 10px 8px; }
        .rep-sum tr + tr td { border-top: 1px solid rgba(255,255,255,0.06); }
        .rep-sum .net td { font-size: 1.15rem; font-weight: 800; border-top: 2px solid var(--border-color); }
        .rep-num { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .rep-stat { padding: 18px 20px; border-radius: var(--radius-md); background: var(--bg-hover); border: 1px solid var(--border-color); }
        .rep-stat .label { font-size: 0.82rem; color: var(--text-secondary); }
        .rep-stat .value { font-size: 1.6rem; font-weight: 800; font-family: var(--font-title); margin-top: 4px; }
        .rep-stat .hint { font-size: 0.78rem; color: var(--text-secondary); margin-top: 4px; }
        .rep-pos { color: var(--green); } .rep-neg { color: var(--red); } .rep-muted { color: var(--text-secondary); }
        @media (max-width: 991px) { .rep-grid.cols-2 { grid-template-columns: minmax(0, 1fr); } }
    </style>

    {{-- Header + filter --}}
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Financial Reports') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 4px 0 0;">
                    {{ __('Money in, money out and what is left, for') }} {{ $selectedDate->translatedFormat('F') }} {{ $year }}
                </p>
            </div>
            <form action="{{ route('admin.reports.services') }}" method="GET" style="display: flex; gap: 10px; align-items: center;">
                <select name="month" class="form-control" style="width: auto;" onchange="this.form.submit()">
                    @foreach($monthNames as $i => $name)
                        <option value="{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}" {{ (int) $month === $i + 1 ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="year" class="form-control" style="width: auto;" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    {{-- 1. Money in / out: selected month and whole year --}}
    <div class="rep-grid cols-2">
        <div class="card" style="margin-bottom: 0;">
            <h4 class="rep-title"><i class="ri-scales-3-line" style="color: var(--color-primary);"></i> {{ __('Profit & Loss') }}</h4>
            <table class="rep-sum" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr class="rep-muted" style="font-size: 0.8rem;">
                        <th style="text-align: start;"></th>
                        <th style="text-align: end;">{{ $selectedDate->translatedFormat('F') }}</th>
                        <th style="text-align: end;">{{ __('Year') }} {{ $year }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td>{{ $r['label'] }}</td>
                            <td class="rep-num" style="text-align: end; color: {{ $r['color'] }};">{{ $r['sign'] }} {{ number_format($monthTotals[$r['key']], 2) }}</td>
                            <td class="rep-num" style="text-align: end; color: {{ $r['color'] }};">{{ $r['sign'] }} {{ number_format($yearTotals[$r['key']], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="rep-muted" style="font-size: 0.82rem;">
                        <td>{{ __('Collected from clients (cash)') }}</td>
                        <td class="rep-num" style="text-align: end;">{{ number_format($monthTotals['collected'], 2) }}</td>
                        <td class="rep-num" style="text-align: end;">{{ number_format($yearTotals['collected'], 2) }}</td>
                    </tr>
                    <tr class="net">
                        <td>{{ __('Net Profit') }}</td>
                        <td class="rep-num {{ $monthTotals['net'] >= 0 ? 'rep-pos' : 'rep-neg' }}" style="text-align: end;">{{ number_format($monthTotals['net'], 2) }}</td>
                        <td class="rep-num {{ $yearTotals['net'] >= 0 ? 'rep-pos' : 'rep-neg' }}" style="text-align: end;">{{ number_format($yearTotals['net'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
            <p class="rep-muted" style="font-size: 0.8rem; margin: 14px 0 0; line-height: 1.7;">
                {{ __('Revenue is the project price spread over the months the client works with us (subscriptions: every active month), whatever day the client pays. Collected is the cash actually received. Partner capital is not revenue.') }}
            </p>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <h4 class="rep-title"><i class="ri-bar-chart-2-line" style="color: var(--color-primary);"></i> {{ __('Month by month') }} {{ $year }}</h4>
            <div style="width: 100%; height: 300px;"><canvas id="monthlyChart"></canvas></div>
        </div>
    </div>

    {{-- 2. Dues --}}
    <h4 class="rep-title" style="margin-top: 8px;"><i class="ri-hand-coin-line" style="color: var(--orange);"></i> {{ __('Dues & Overdue') }}</h4>
    <div class="rep-grid cols-3" style="margin-bottom: 16px;">
        <div class="rep-stat">
            <div class="label">{{ __('Clients still owe you') }}</div>
            <div class="value">{{ number_format($dues['clients_owe'], 2) }}</div>
            <div class="hint">{{ __('Agreed price minus what was paid, on active projects') }}</div>
        </div>
        <div class="rep-stat">
            <div class="label">{{ __('You still owe external workers') }}</div>
            <div class="value">{{ number_format($dues['we_owe_vendors'], 2) }}</div>
            <div class="hint">{{ __('Scheduled payments not paid yet') }}</div>
        </div>
        <div class="rep-stat">
            <div class="label">{{ __('Partners still have to pay') }}</div>
            <div class="value">{{ number_format($dues['partners_owe'], 2) }}</div>
            <div class="hint">{{ __('Committed capital minus paid') }}</div>
        </div>
    </div>

    @php
        $overdueSets = [
            [
                'title' => __('Overdue from clients'),
                'items' => $dues['overdue_from_clients']->map(fn($p) => [
                    'who' => ($p->project?->client?->name ?? '-') . ' · ' . ($p->project?->service?->name ?? ''),
                    'what' => $p->title, 'amount' => $p->amount, 'date' => $p->due_date,
                ]),
            ],
            [
                'title' => __('Overdue to external workers'),
                'items' => $dues['overdue_vendors']->map(fn($v) => [
                    'who' => ($v->vendor?->name ?? '-') . ' · ' . ($v->vendor?->project?->client?->name ?? ''),
                    'what' => $v->title, 'amount' => $v->amount, 'date' => $v->due_date,
                ]),
            ],
            [
                'title' => __('Overdue from partners'),
                'items' => $dues['overdue_contributions']->map(fn($c) => [
                    'who' => ($c->partner?->name ?? '-') . ' · ' . ($c->partner?->project?->service?->name ?? ''),
                    'what' => __('Capital contribution'), 'amount' => $c->amount, 'date' => $c->due_date,
                ]),
            ],
        ];
    @endphp
    <div class="rep-grid cols-3">
        @foreach($overdueSets as $set)
            <div class="card" style="margin-bottom: 0;">
                <h5 style="margin: 0 0 12px; font-weight: 600;">{{ $set['title'] }}
                    <span class="badge" style="background: rgba(239,68,68,0.12); color: #ef4444;">{{ $set['items']->count() }}</span>
                </h5>
                @forelse($set['items']->take(8) as $it)
                    <div style="display: flex; justify-content: space-between; gap: 10px; padding: 8px 0; border-top: 1px solid rgba(255,255,255,0.06); font-size: 0.88rem;">
                        <div style="min-width: 0;">
                            <div style="font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $it['who'] }}</div>
                            <div class="rep-muted" style="font-size: 0.78rem;">{{ $it['what'] }} · {{ $it['date']->format('Y-m-d') }}</div>
                        </div>
                        <div class="rep-num rep-neg" style="font-weight: 700;">{{ number_format($it['amount'], 2) }}</div>
                    </div>
                @empty
                    <div class="rep-muted" style="font-size: 0.88rem;">{{ __('Nothing overdue.') }}</div>
                @endforelse
                @if($set['items']->count() > 8)
                    <div class="rep-muted" style="font-size: 0.78rem; margin-top: 8px;">+ {{ $set['items']->count() - 8 }} {{ __('more') }}</div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- 3. Profit per project --}}
    <div class="card" style="margin-bottom: 24px;">
        <h4 class="rep-title"><i class="ri-briefcase-4-line" style="color: var(--color-primary);"></i> {{ __('Profit per Project') }}</h4>
        <p class="rep-sub">{{ __('Period = the months the client works with us (from the start date to the end date, or until the project is closed or the subscription stopped). Net = cash received minus paid external workers and project expenses, before salaries. Team cost is only for the selected month: each salary split across the projects the employee works on that month.') }}</p>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Project') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Agreed') }}</th>
                        <th>{{ __('Earned') }} ({{ $selectedDate->translatedFormat('F') }})</th>
                        <th>{{ __('Received') }}</th>
                        <th>{{ __('Remaining') }}</th>
                        <th>{{ __('External Workers') }}</th>
                        <th>{{ __('Expenses') }}</th>
                        <th>{{ __('Net') }}</th>
                        <th title="{{ __('Salaries of the assigned employees for the selected month, split across their active projects.') }}">{{ __('Team cost') }} ({{ $selectedDate->translatedFormat('F') }}) <i class="ri-information-line"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projectStats as $p)
                        <tr>
                            <td>
                                <a href="{{ route('admin.projects.edit', $p['id']) }}" style="color: inherit; text-decoration: none;">
                                    <strong>{{ $p['client'] }}</strong>
                                    <div class="rep-muted" style="font-size: 0.78rem;">#{{ $p['id'] }} · {{ $p['service'] }}</div>
                                </a>
                            </td>
                            <td style="white-space: nowrap; font-size: 0.85rem;">
                                {{ $p['period_from'] }} → {{ $p['running'] ? __('now') : $p['period_to'] }}
                                <div class="rep-muted" style="font-size: 0.75rem;">{{ $p['period_months'] }} {{ __('months') }}</div>
                            </td>
                            <td class="rep-num">{{ number_format($p['agreed'], 2) }}</td>
                            <td class="rep-num {{ $p['earned_month'] > 0 ? '' : 'rep-muted' }}">{{ number_format($p['earned_month'], 2) }}</td>
                            <td class="rep-num rep-pos">{{ number_format($p['received'], 2) }}</td>
                            <td class="rep-num {{ $p['remaining'] > 0 ? '' : 'rep-muted' }}">{{ number_format($p['remaining'], 2) }}</td>
                            <td class="rep-num {{ $p['vendors'] > 0 ? 'rep-neg' : 'rep-muted' }}">{{ number_format($p['vendors'], 2) }}</td>
                            <td class="rep-num {{ $p['expenses'] > 0 ? 'rep-neg' : 'rep-muted' }}">{{ number_format($p['expenses'], 2) }}</td>
                            <td class="rep-num {{ $p['net'] >= 0 ? 'rep-pos' : 'rep-neg' }}" style="font-weight: 800;">{{ number_format($p['net'], 2) }}</td>
                            <td class="rep-num {{ $p['team_cost'] > 0 ? '' : 'rep-muted' }}">{{ number_format($p['team_cost'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" style="text-align: center;" class="rep-muted">{{ __('No projects yet.') }}</td></tr>
                    @endforelse
                </tbody>
                @if($projectStats->count() > 0)
                    <tfoot>
                        <tr style="font-weight: 800;">
                            <td>{{ __('Total') }}</td>
                            <td></td>
                            <td class="rep-num">{{ number_format($projectStats->sum('agreed'), 2) }}</td>
                            <td class="rep-num">{{ number_format($projectStats->sum('earned_month'), 2) }}</td>
                            <td class="rep-num">{{ number_format($projectStats->sum('received'), 2) }}</td>
                            <td class="rep-num">{{ number_format($projectStats->sum('remaining'), 2) }}</td>
                            <td class="rep-num">{{ number_format($projectStats->sum('vendors'), 2) }}</td>
                            <td class="rep-num">{{ number_format($projectStats->sum('expenses'), 2) }}</td>
                            <td class="rep-num">{{ number_format($projectStats->sum('net'), 2) }}</td>
                            <td class="rep-num">{{ number_format($projectStats->sum('team_cost'), 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- 4. Revenue per service --}}
    <div class="card">
        <h4 class="rep-title"><i class="ri-customer-service-2-fill" style="color: var(--color-primary);"></i> {{ __('Revenue by Service') }}</h4>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Service') }}</th>
                        <th>{{ __('Projects') }}</th>
                        <th>{{ $selectedDate->translatedFormat('F') }}</th>
                        <th>{{ __('Year') }} {{ $year }}</th>
                        <th>{{ __('All-Time Revenue') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceStats as $s)
                        <tr>
                            <td><strong>{{ $s['name'] }}</strong></td>
                            <td>{{ $s['projects_count'] }}</td>
                            <td class="rep-num">{{ number_format($s['month'], 2) }}</td>
                            <td class="rep-num">{{ number_format($s['year'], 2) }}</td>
                            <td class="rep-num">{{ number_format($s['all'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align: center;" class="rep-muted">{{ __('No services found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        Chart.defaults.color = '#a1a1aa';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
        Chart.defaults.font.family = "'Inter', 'Tajawal', sans-serif";

        const series = @json(array_values($series));
        new Chart(document.getElementById('monthlyChart'), {
            data: {
                labels: @json($monthNames),
                datasets: [
                    { type: 'bar', label: '{{ __("Revenue") }}', data: series.map(r => r.revenue), backgroundColor: 'rgba(16, 185, 129, 0.8)', borderRadius: 4 },
                    { type: 'bar', label: '{{ __("Costs") }}', data: series.map(r => r.salaries + r.expenses + r.vendors), backgroundColor: 'rgba(239, 68, 68, 0.8)', borderRadius: 4 },
                    { type: 'line', label: '{{ __("Collected") }}', data: series.map(r => r.collected), borderColor: '#f59e0b', backgroundColor: '#f59e0b', borderDash: [6, 4], tension: 0.3, pointRadius: 2 },
                    { type: 'line', label: '{{ __("Net Profit") }}', data: series.map(r => r.net), borderColor: '#328af1', backgroundColor: '#328af1', tension: 0.3, pointRadius: 3 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { position: 'top' } }
            }
        });
    </script>
@endsection
