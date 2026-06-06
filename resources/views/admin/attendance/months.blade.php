@extends('layouts.app')

@section('title', __('Monthly Summary'))
@section('page_header', __('Monthly Summary'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Employee Monthly Metrics') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">
                    {{ __('Summarizing logs for') }}: <strong>{{ __(date('F', mktime(0, 0, 0, $month, 10))) }} {{ $year }}</strong>
                </p>
            </div>
            
            <form method="GET" action="/admin/attendance/months" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
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
                        <span class="selected-text">{{ $year }}</span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="dropdown-menu" style="max-height: 250px; overflow-y: auto;">
                        @for($y = date('Y') - 5; $y <= date('Y'); $y++)
                            <div class="dropdown-item {{ $year == $y ? 'selected' : '' }}" data-value="{{ $y }}">{{ $y }}</div>
                        @endfor
                    </div>
                    <input type="hidden" name="year" class="dropdown-value-input" value="{{ $year }}">
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 9px 16px;">
                    <i class="ri-filter-line"></i>
                    <span>{{ __('Filter') }}</span>
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Job Title') }}</th>
                        <th style="text-align: center;">{{ __('Present') }}</th>
                        <th style="text-align: center;">{{ __('Absent') }}</th>
                        <th style="text-align: center;">{{ __('Vacation') }}</th>
                        <th style="text-align: center;">{{ __('Excused') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                        @php
                            $sum = $summaries[$emp->id];
                        @endphp
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    <strong>{{ $emp->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $emp->job_title }}</td>
                            <td style="text-align: center;">
                                <span class="badge badge-present" style="font-size: 0.85rem; padding: 4px 10px;">{{ $sum['present'] }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-absent" style="font-size: 0.85rem; padding: 4px 10px;">{{ $sum['absent'] }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-vacation" style="font-size: 0.85rem; padding: 4px 10px;">{{ $sum['vacation'] }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-excused" style="font-size: 0.85rem; padding: 4px 10px;">{{ $sum['excused'] }}</span>
                            </td>
                            <td>
                                <a href="/admin/attendance/details/{{ $emp->id }}/{{ $month }}/{{ $year }}" class="btn btn-secondary btn-icon" title="{{ __('Weekly Calendar View') }}">
                                    <i class="ri-calendar-todo-fill"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
