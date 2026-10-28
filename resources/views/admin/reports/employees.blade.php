@extends('layouts.app')

@section('title', __('Team Report'))
@section('page_header', __('Team Report'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    @php
        $monthNames = collect(range(1, 12))->map(fn($m) => \Carbon\Carbon::create(2000, $m, 1)->translatedFormat('F'));
    @endphp

    <style>
        .team-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 18px; }
        .team-card { margin-bottom: 0; }
        .team-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .team-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin-bottom: 14px; }
        .team-stat { padding: 10px 12px; border-radius: var(--radius-md); background: var(--bg-hover); }
        .team-stat .label { font-size: 0.72rem; color: var(--text-secondary); }
        .team-stat .value { font-weight: 800; font-size: 1.05rem; font-variant-numeric: tabular-nums; }
        .team-section { font-size: 0.78rem; color: var(--text-secondary); margin: 12px 0 6px; }
        .team-chip { display: inline-block; padding: 3px 10px; margin: 0 0 4px; border-radius: 999px; font-size: 0.78rem; background: rgba(59,130,246,0.12); color: var(--color-primary-light); }
        .team-project { display: flex; justify-content: space-between; gap: 8px; padding: 6px 0; border-top: 1px solid rgba(255,255,255,0.06); font-size: 0.85rem; }
    </style>

    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Team Report') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 4px 0 0;">
                    {{ __('Who works on what, what each person costs and how present they are') }} · {{ $selectedDate->translatedFormat('F') }} {{ $year }}
                </p>
            </div>
            <form action="{{ route('admin.reports.employees') }}" method="GET" style="display: flex; gap: 10px; align-items: center;">
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

    <div class="card" style="margin-bottom: 24px; border-inline-start: 4px solid var(--color-primary);">
        <h4 style="margin-top: 0; font-weight: 600;"><i class="ri-information-line"></i> {{ __('How to read this report') }}</h4>
        <ul style="margin: 0; padding-inline-start: 20px; color: var(--text-secondary); font-size: 0.9rem; line-height: 1.9;">
            <li>{{ __('Revenue is not credited to individual employees: every project is a team effort. Revenue and profit are on the Financial Reports page, per project and per service.') }}</li>
            <li><strong>{{ __('Salary') }}</strong>: {{ __('salary paid for the selected month.') }} <strong>{{ __('Per project') }}</strong>: {{ __('that salary divided by the active projects the employee is assigned to. This is the amount charged to each project as team cost.') }}</li>
            <li><strong>{{ __('Attendance') }}</strong>: {{ __('a working day with no record counts as present.') }}</li>
        </ul>
    </div>

    <div class="team-grid">
        @forelse($employeeStats as $emp)
            <div class="card team-card">
                <div class="team-head">
                    <div class="user-avatar" style="width: 42px; height: 42px; flex-shrink: 0;">{{ strtoupper(mb_substr($emp['name'], 0, 1)) }}</div>
                    <div style="min-width: 0;">
                        <strong>{{ $emp['name'] }}</strong>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $emp['job_title'] }}</div>
                    </div>
                </div>

                <div class="team-stats">
                    <div class="team-stat">
                        <div class="label">{{ __('Active projects') }}</div>
                        <div class="value">{{ $emp['active_count'] }}</div>
                    </div>
                    <div class="team-stat">
                        <div class="label">{{ __('Salary') }}</div>
                        <div class="value">{{ number_format($emp['salary'], 2) }}</div>
                    </div>
                    <div class="team-stat">
                        <div class="label">{{ __('Per project') }}</div>
                        <div class="value">{{ number_format($emp['salary_per_project'], 2) }}</div>
                    </div>
                </div>

                <div style="font-size: 0.88rem;">
                    <span style="color: #22c55e; font-weight: 700;">{{ $emp['attendance']['present'] }}</span> {{ __('present') }} ·
                    <span style="color: #ef4444; font-weight: 700;">{{ $emp['attendance']['absent'] }}</span> {{ __('absent') }} ·
                    <span style="color: var(--orange); font-weight: 700;">{{ $emp['attendance']['vacation'] + $emp['attendance']['excused'] }}</span> {{ __('leave') }}
                </div>

                @if(count($emp['skills']))
                    <div class="team-section">{{ __('Skills') }}</div>
                    @foreach($emp['skills'] as $skill)
                        <span class="team-chip">{{ $skill }}</span>
                    @endforeach
                @endif

                <div class="team-section">{{ __('Working on') }} ({{ $emp['active_count'] }}) · {{ __('Completed') }}: {{ $emp['done_count'] }}</div>
                @forelse($emp['active_projects'] as $p)
                    <div class="team-project">
                        <a href="{{ route('admin.projects.edit', $p['id']) }}" style="color: inherit; text-decoration: none; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">#{{ $p['id'] }} {{ $p['label'] }}</a>
                        @if($p['role'])<span class="team-chip" style="margin: 0;">{{ $p['role'] }}</span>@endif
                    </div>
                @empty
                    <div style="color: var(--text-secondary); font-size: 0.85rem;">{{ __('No active projects.') }}</div>
                @endforelse
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; color: var(--text-secondary);">{{ __('No employees found.') }}</div>
        @endforelse
    </div>
@endsection
