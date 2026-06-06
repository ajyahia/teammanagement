@extends('layouts.app')

@section('title', __('My Attendance Summary'))
@section('page_header', __('My Attendance Summary'))

@section('sidebar_menu')
    @include('layouts.sidebar_employee')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Employee Monthly Metrics') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">
                    {{ __('Summarizing logs for') }}: <strong>{{ __(date('F', mktime(0, 0, 0, $month, 10))) }} {{ $year ?? date('Y') }}</strong>
                </p>
            </div>
            
            <form method="GET" action="/employee/attendance" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- Month Custom Dropdown -->
                <div class="custom-dropdown" id="dropdown-month" style="width: 160px;">
                    <div class="dropdown-trigger">
                        <span class="selected-text">{{ __(date('F', mktime(0, 0, 0, $month, 10))) }}</span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="dropdown-menu" style="max-height: 250px; overflow-y: auto;">
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $mVal = sprintf('%02d', $m);
                                $mText = __(date('F', mktime(0, 0, 0, $m, 10)));
                            @endphp
                            <div class="dropdown-item {{ $month == $mVal ? 'selected' : '' }}" data-value="{{ $mVal }}">{{ $mText }}</div>
                        @endfor
                    </div>
                    <input type="hidden" name="month" class="dropdown-value-input" value="{{ $month }}">
                </div>

                <!-- Year Custom Dropdown -->
                <div class="custom-dropdown" id="dropdown-year" style="width: 110px;">
                    <div class="dropdown-trigger">
                        <span class="selected-text">{{ $year ?? date('Y') }}</span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="dropdown-menu" style="max-height: 250px; overflow-y: auto;">
                        @for($y = date('Y') - 5; $y <= date('Y'); $y++)
                            <div class="dropdown-item {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}" data-value="{{ $y }}">{{ $y }}</div>
                        @endfor
                    </div>
                    <input type="hidden" name="year" class="dropdown-value-input" value="{{ $year ?? date('Y') }}">
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 9px 16px;">
                    <i class="ri-filter-line"></i>
                    <span>{{ __('Filter') }}</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Stats widgets for selected month -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-green">
                <i class="ri-checkbox-circle-fill"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Present') }}</h3>
                <p>{{ $summary['present'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-red">
                <i class="ri-close-circle-fill"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Absent') }}</h3>
                <p>{{ $summary['absent'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-yellow">
                <i class="ri-plane-fill"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Vacation') }}</h3>
                <p>{{ $summary['vacation'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-primary">
                <i class="ri-file-warning-fill"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Excused') }}</h3>
                <p>{{ $summary['excused'] }}</p>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title">
            <i class="ri-calendar-todo-fill"></i>
            <span>{{ __('Weekly Calendar View') }}</span>
        </h3>
        <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 0.95rem;">
            {{ __('Click below to view your daily attendance status mapped on a Saturday-to-Friday weekly grid.') }}
        </p>
        <a href="/employee/attendance/details/{{ $month }}/{{ $year }}" class="btn btn-primary">
            <i class="ri-eye-fill"></i>
            <span>{{ __('Weekly Calendar View') }}</span>
        </a>
    </div>
@endsection
