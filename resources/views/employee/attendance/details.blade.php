@extends('layouts.app')

@section('title', __('Weekly Attendance Calendar'))
@section('page_header', __('Attendance Details'))

@section('sidebar_menu')
    @include('layouts.sidebar_employee')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, rgba(33, 200, 246, 0.05) 0%, rgba(99, 123, 255, 0.05) 100%);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
            <div>
                <h2 style="font-family: var(--font-title); font-size: 1.35rem; margin-bottom: 4px;">{{ __('My Calendar Grid') }}</h2>
                <p style="color: var(--text-secondary); font-size: 0.85rem;">
                    {{ __('Shift') }}: {{ auth()->user()->work_start_time ? auth()->user()->work_start_time->format('H:i') : '09:00' }} - {{ auth()->user()->work_end_time ? auth()->user()->work_end_time->format('H:i') : '17:00' }}
                </p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Month') }}:</span>
                <strong style="color: var(--color-primary-light); font-size: 1rem; text-transform: uppercase;">
                    {{ __(date('F', mktime(0, 0, 0, $month, 10))) }} {{ $year }}
                </strong>
                <a href="/employee/attendance?month={{ $month }}&year={{ $year }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; margin-left: 10px;">
                    <i class="ri-arrow-left-line"></i> {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Monthly Stats Widgets -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon icon-green" style="width: 38px; height: 38px; font-size: 18px;">
                <i class="ri-checkbox-circle-line"></i>
            </div>
            <div class="stat-info">
                <h3 style="font-size: 0.7rem;">{{ __('Present') }}</h3>
                <p style="font-size: 1.35rem; margin-top: 0;">{{ $summary['present'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-red" style="width: 38px; height: 38px; font-size: 18px;">
                <i class="ri-close-circle-line"></i>
            </div>
            <div class="stat-info">
                <h3 style="font-size: 0.7rem;">{{ __('Absent') }}</h3>
                <p style="font-size: 1.35rem; margin-top: 0;">{{ $summary['absent'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-yellow" style="width: 38px; height: 38px; font-size: 18px;">
                <i class="ri-plane-line"></i>
            </div>
            <div class="stat-info">
                <h3 style="font-size: 0.7rem;">{{ __('Vacation') }}</h3>
                <p style="font-size: 1.35rem; margin-top: 0;">{{ $summary['vacation'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-primary" style="width: 38px; height: 38px; font-size: 18px; background-color: rgba(148, 163, 184, 0.1); color: var(--text-secondary);">
                <i class="ri-file-warning-line"></i>
            </div>
            <div class="stat-info">
                <h3 style="font-size: 0.7rem;">{{ __('Excused') }}</h3>
                <p style="font-size: 1.35rem; margin-top: 0;">{{ $summary['excused'] }}</p>
            </div>
        </div>
    </div>

    <!-- Weeks Saturday-to-Friday Calendar Table -->
    <div class="card">
        <h3 class="card-title">
            <i class="ri-calendar-2-line"></i>
            <span>{{ __('Weekly Calendar View') }}</span>
        </h3>
        
        <div class="table-responsive">
            <table class="weeks-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">{{ __('Week') }}</th>
                        <th>{{ __('Saturday') }}</th>
                        <th>{{ __('Sunday') }}</th>
                        <th>{{ __('Monday') }}</th>
                        <th>{{ __('Tuesday') }}</th>
                        <th>{{ __('Wednesday') }}</th>
                        <th>{{ __('Thursday') }}</th>
                        <th>{{ __('Friday') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weeks as $week)
                        <tr>
                            <td style="font-family: var(--font-title); font-weight: 700; background-color: rgba(31, 41, 61, 0.15); color: var(--color-primary-light);">
                                {{ __('Week') }} {{ $week['week_number'] }}
                            </td>
                            
                            @foreach([6, 0, 1, 2, 3, 4, 5] as $dayIndex)
                                @php
                                    $dayData = $week['days'][$dayIndex] ?? null;
                                @endphp
                                <td>
                                    @if($dayData)
                                        <div class="day-num">{{ $dayData['day_num'] }}</div>
                                        <div class="day-circle circle-{{ $dayData['status'] }}">
                                            <div class="day-tooltip">
                                                <strong>{{ __(ucfirst($dayData['status'])) }}</strong>
                                                @if($dayData['notes'])
                                                    <br><span style="font-size: 0.7rem; opacity: 0.85;">{{ $dayData['notes'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div style="opacity: 0.15; font-size: 0.8rem;">-</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
