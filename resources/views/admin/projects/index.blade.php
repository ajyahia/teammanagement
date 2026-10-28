@extends('layouts.app')

@section('title', __('Projects & Orders'))
@section('page_header', __('Projects & Orders'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    @php
        $tabs = [
            '' => ['label' => __('All'), 'count' => $counts->sum()],
            'pending' => ['label' => __('Pending'), 'count' => $counts['pending'] ?? 0],
            'in_progress' => ['label' => __('In Progress'), 'count' => $counts['in_progress'] ?? 0],
            'completed' => ['label' => __('Completed'), 'count' => $counts['completed'] ?? 0],
            'cancelled' => ['label' => __('Cancelled'), 'count' => $counts['cancelled'] ?? 0],
        ];
        $statusBadge = ['completed' => 'badge-present', 'in_progress' => 'badge-vacation', 'cancelled' => 'badge-absent'];
        $statusLabel = ['pending' => __('Pending'), 'in_progress' => __('In Progress'), 'completed' => __('Completed'), 'cancelled' => __('Cancelled')];
    @endphp

    <style>
        .prj-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .prj-stat { margin-bottom: 0; padding: 18px 20px; }
        .prj-stat .label { color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 4px; }
        .prj-stat .value { margin: 0; font-family: var(--font-title); font-weight: 800; font-size: 1.5rem; font-variant-numeric: tabular-nums; }
        .prj-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 16px; }
        .prj-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
        .prj-tab { padding: 7px 14px; border-radius: 999px; font-size: 0.85rem; text-decoration: none; color: var(--text-secondary); border: 1px solid var(--border-color); transition: var(--transition); }
        .prj-tab:hover { background: var(--bg-hover); color: var(--text-primary); }
        .prj-tab.is-active { background: rgba(59,130,246,0.15); border-color: rgba(59,130,246,0.4); color: var(--color-primary-light); }
        .prj-tab small { opacity: 0.7; margin-inline-start: 4px; }
        .prj-search { display: flex; gap: 8px; }
        .prj-search .form-control { min-width: 220px; }
        .prj-table td { vertical-align: top; }
        .prj-client { font-weight: 700; }
        .prj-sub { font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px; }
        .prj-badges { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 6px; }
        .prj-badges .badge { font-size: 0.68rem; padding: 2px 7px; }
        .prj-team { display: flex; gap: 4px; flex-wrap: wrap; max-width: 240px; }
        .prj-chip { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 0.75rem; background: rgba(59,130,246,0.12); color: var(--color-primary-light); }
        .prj-money { min-width: 190px; font-variant-numeric: tabular-nums; }
        .prj-money .row { display: flex; justify-content: space-between; gap: 12px; font-size: 0.82rem; }
        .prj-money .row span:first-child { color: var(--text-secondary); }
        .prj-bar { height: 6px; border-radius: 3px; background: var(--border-color); overflow: hidden; margin: 6px 0; }
        .prj-bar span { display: block; height: 100%; background: var(--green); border-radius: 3px; }
        .prj-actions { display: flex; justify-content: center; gap: 8px; }
    </style>

    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('All Projects') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 4px 0 0;">{{ __('Track services, assign employees, and monitor profits') }}</p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <form action="{{ route('admin.projects.cycles.generate_all') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;" title="{{ __('Generate billing cycles for all active subscriptions for the current month') }}">
                        <i class="ri-refresh-line"></i>
                        <span>{{ __('Generate Monthly Bills') }}</span>
                    </button>
                </form>
                <a href="{{ route('admin.projects.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ri-add-line"></i>
                    <span>{{ __('Create Project') }}</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: rgba(46, 204, 113, 0.1); color: #27ae60; border: 1px solid rgba(46, 204, 113, 0.2);">
            {{ session('success') }}
        </div>
    @endif

    <div class="prj-stats">
        <div class="card prj-stat" style="border-inline-start: 4px solid var(--color-primary);">
            <div class="label">{{ __('Total Revenue') }}</div>
            <p class="value">{{ number_format($summary['revenue'], 2) }}</p>
        </div>
        <div class="card prj-stat" style="border-inline-start: 4px solid var(--green);">
            <div class="label">{{ __('Total Paid') }}</div>
            <p class="value" style="color: var(--green);">{{ number_format($summary['paid'], 2) }}</p>
        </div>
        <div class="card prj-stat" style="border-inline-start: 4px solid var(--orange);">
            <div class="label">{{ __('Total Due') }}</div>
            <p class="value" style="color: var(--orange);">{{ number_format($summary['due'], 2) }}</p>
        </div>
    </div>

    <div class="card">
        <div class="prj-toolbar">
            <div class="prj-tabs">
                @foreach($tabs as $key => $tab)
                    <a href="{{ route('admin.projects.index', array_filter(['status' => $key, 'q' => $search])) }}"
                       class="prj-tab {{ (string) $status === (string) $key ? 'is-active' : '' }}">
                        {{ $tab['label'] }}<small>{{ $tab['count'] }}</small>
                    </a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('admin.projects.index') }}" class="prj-search">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                <input type="text" name="q" class="form-control" value="{{ $search }}" placeholder="{{ __('Search client or service...') }}">
                <button class="btn btn-secondary btn-icon" title="{{ __('Search') }}"><i class="ri-search-line"></i></button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table prj-table">
                <thead>
                    <tr>
                        <th>{{ __('Project') }}</th>
                        <th>{{ __('Team') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Payments') }}</th>
                        <th style="text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        @php
                            $total = (float) $project->total_revenue;
                            $paid = (float) $project->total_paid;
                            $percent = $total > 0 ? min(100, round($paid / $total * 100)) : 0;
                            $isSub = in_array($project->billing_type, ['monthly', 'yearly']);
                        @endphp
                        <tr>
                            <td>
                                <div class="prj-client">{{ $project->client->name }}</div>
                                <div class="prj-sub">#{{ $project->id }} · {{ $project->service->name }}</div>
                                <div class="prj-badges">
                                    @if($isSub)
                                        <span class="badge badge-vacation">{{ $project->billing_type === 'yearly' ? __('Annual Subscription') : __('Monthly Sub') }}</span>
                                        @if($project->subscription_status === 'active')
                                            <span class="badge badge-present">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge badge-absent">{{ __('Stopped') }}@if($project->pauses->last()) · {{ $project->pauses->last()->paused_at->format('Y-m-d') }}@endif</span>
                                        @endif
                                    @elseif($project->billing_type === 'per_page')
                                        <span class="badge badge-excused">{{ __('Per Page (Books)') }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ __('One Time Payment') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($project->employees->count() > 0)
                                    <div class="prj-team">
                                        @foreach($project->employees as $emp)
                                            <span class="prj-chip">{{ $emp->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="badge badge-absent">{{ __('Unassigned') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $statusBadge[$project->status] ?? 'badge-excused' }}">{{ $statusLabel[$project->status] ?? $project->status }}</span>
                            </td>
                            <td class="prj-money">
                                <div class="row"><span>{{ __('Total Billed') }}</span><strong>{{ number_format($total, 2) }}</strong></div>
                                <div class="prj-bar"><span style="width: {{ $percent }}%;"></span></div>
                                <div class="row"><span>{{ __('Paid') }}</span><strong style="color: var(--green);">{{ number_format($paid, 2) }}</strong></div>
                                <div class="row"><span>{{ __('Due') }}</span><strong style="color: var(--orange);">{{ number_format($project->due_amount, 2) }}</strong></div>
                                @if($isSub)
                                    <div class="prj-sub" style="margin-top: 4px;">{{ __('Rate:') }} {{ number_format($project->agreed_price, 2) }} · {{ $project->cycles->count() }} {{ __('Months') }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="prj-actions">
                                    @if($isSub)
                                        <form action="{{ route('admin.projects.subscription.toggle', $project->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            @method('PATCH')
                                            @if($project->subscription_status === 'stopped')
                                                <button type="submit" class="btn-icon btn-success" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);" title="{{ __('Start the subscription again') }}">
                                                    <i class="ri-play-circle-line"></i>
                                                </button>
                                            @else
                                                <button type="submit" class="btn-icon btn-secondary" style="color: var(--orange);" title="{{ __('Stop the subscription (it can be started again later)') }}">
                                                    <i class="ri-pause-circle-line"></i>
                                                </button>
                                            @endif
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.projects.edit', $project->id) }}#payments" class="btn-icon btn-success" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);" title="{{ __('Manage Payments / Installments') }}">
                                        <i class="ri-money-dollar-circle-fill"></i>
                                    </a>
                                    <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn-icon btn-secondary" title="{{ __('Edit Project') }}">
                                        <i class="ri-edit-2-line"></i>
                                    </a>
                                    <form id="delete-project-form-{{ $project->id }}" action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-icon" title="{{ __('Delete Project') }}" onclick="showGlobalConfirmPopup('delete-project-form-{{ $project->id }}', '{{ __('Are you sure you want to delete this project?') }}')">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">
                                <i class="ri-briefcase-4-line" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
                                <p style="color: var(--text-secondary); margin: 0;">{{ __('No projects found. Create one!') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
            <div style="margin-top: 20px;">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
@endsection
