@extends('layouts.app')

@section('title', __('Reports & Analytics'))
@section('page_header', __('Reports & Analytics'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Financial Reports') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">{{ __('Track revenue and profits by service and employee') }}</p>
            </div>
            
            <form action="{{ route('admin.reports.employees') }}" method="GET" style="display: flex; gap: 10px; align-items: center;">
                <select name="month" class="form-control" style="width: auto;">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="form-control" style="width: auto;">
                    @for($y=date('Y'); $y>=2020; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
            </form>
        </div>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start;">
        <!-- Employee Chart -->
        <div class="card" style="flex: 1; min-width: 400px; margin-bottom: 0;">
            <div style="width: 100%; height: 350px;">
                <canvas id="employeeChart"></canvas>
            </div>
        </div>

        <!-- Employees Report Table -->
        <div class="card" style="flex: 2; min-width: 500px; margin-bottom: 0;">
            <h4 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 20px; font-weight: 600;">
                <i class="ri-table-line" style="color: var(--orange); margin-right: 8px;"></i>
                {{ __('Revenue by Employee') }}
            </h4>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th style="background: rgba(24, 24, 38, 0.5);">{{ __('Monthly Revenue') }}<br><small style="color: var(--text-secondary); font-weight: normal;">({{ $selectedDate->format('M Y') }})</small></th>
                            <th style="background: rgba(24, 24, 38, 0.5);">{{ __('Monthly Salary') }}</th>
                            <th style="background: rgba(24, 24, 38, 0.5);">{{ __('Monthly Net') }}</th>
                            <th>{{ __('All-Time Revenue') }}</th>
                            <th>{{ __('All-Time Net') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employeeStats as $stat)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            {{ strtoupper(substr($stat['name'], 0, 1)) }}
                                        </div>
                                        <strong>{{ $stat['name'] }}</strong>
                                    </div>
                                </td>
                                <td style="background: rgba(24, 24, 38, 0.5); font-weight: bold; color: var(--color-primary-light);">{{ number_format($stat['monthly_revenue'], 2) }}</td>
                                <td style="background: rgba(24, 24, 38, 0.5); font-weight: bold; color: var(--red);">- {{ number_format($stat['monthly_salary'], 2) }}</td>
                                <td style="background: rgba(24, 24, 38, 0.5); font-weight: bold; color: {{ $stat['monthly_profit'] >= 0 ? 'var(--green)' : 'var(--red)' }};">{{ number_format($stat['monthly_profit'], 2) }}</td>
                                <td style="font-weight: bold;">{{ number_format($stat['all_time_revenue'], 2) }}</td>
                                <td style="font-weight: bold; color: {{ $stat['all_time_profit'] >= 0 ? 'var(--green)' : 'var(--red)' }};">{{ number_format($stat['all_time_profit'], 2) }}</td>
                            </tr>
                        @endforeach
                        @if(count($employeeStats) === 0)
                            <tr><td colspan="6" style="text-align: center; color: var(--text-secondary);">{{ __('No employees found.') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Chart.js Integration -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Common Chart Defaults for Dark Theme
        Chart.defaults.color = '#a1a1aa';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
        Chart.defaults.font.family = "'Inter', 'Tajawal', sans-serif";

        // Employee Bar Chart Data
        const employeeNames = @json($employeeStats->pluck('name'));
        const employeeRevenue = @json($employeeStats->pluck('monthly_revenue'));
        const employeeSalary = @json($employeeStats->pluck('monthly_salary'));

        new Chart(document.getElementById('employeeChart'), {
            type: 'bar',
            data: {
                labels: employeeNames,
                datasets: [
                    {
                        label: '{{ __("Revenue Generated") }}',
                        data: employeeRevenue,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)', // Green
                        borderRadius: 4
                    },
                    {
                        label: '{{ __("Salary Paid") }}',
                        data: employeeSalary,
                        backgroundColor: 'rgba(239, 68, 68, 0.8)', // Red
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: '{{ __("Employee Revenue vs Salary (Monthly)") }}',
                        color: '#fff',
                        font: { size: 16, weight: 'bold' }
                    }
                }
            }
        });
    </script>
@endsection
