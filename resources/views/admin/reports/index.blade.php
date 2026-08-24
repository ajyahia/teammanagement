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
            
            <form action="{{ route('admin.reports.index') }}" method="GET" style="display: flex; gap: 10px; align-items: center;">
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

    <!-- Overall Financial Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Monthly Summary -->
        <div class="card" style="margin-bottom: 0; background: linear-gradient(135deg, rgba(24,24,38,1) 0%, rgba(30,30,46,1) 100%); border-top: 4px solid var(--color-primary);">
            <h4 style="color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 0;">
                <i class="ri-calendar-todo-line"></i> {{ __('Monthly Overview') }} ({{ $selectedDate->format('M Y') }})
            </h4>
            <div style="margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <span style="color: var(--text-secondary);">{{ __('Revenue') }}</span>
                    <span style="font-weight: bold; color: var(--color-primary-light);">+ {{ number_format($financialSummary['monthly_revenue'], 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <span style="color: var(--text-secondary);">{{ __('Salaries Paid') }}</span>
                    <span style="font-weight: bold; color: var(--red);">- {{ number_format($financialSummary['monthly_salaries'], 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <span style="color: var(--text-secondary);">{{ __('Expenses') }}</span>
                    <span style="font-weight: bold; color: var(--red);">- {{ number_format($financialSummary['monthly_expenses'], 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                    <span style="font-size: 1.1rem; font-weight: bold; color: var(--text-primary);">{{ __('Net Profit') }}</span>
                    <span style="font-size: 1.5rem; font-weight: 800; color: {{ $financialSummary['monthly_net_profit'] >= 0 ? 'var(--green)' : 'var(--red)' }};">
                        {{ number_format($financialSummary['monthly_net_profit'], 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- All-Time Summary -->
        <div class="card" style="margin-bottom: 0; background: linear-gradient(135deg, rgba(24,24,38,1) 0%, rgba(30,30,46,1) 100%); border-top: 4px solid var(--orange);">
            <h4 style="color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 0;">
                <i class="ri-history-line"></i> {{ __('All-Time Overview') }}
            </h4>
            <div style="margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <span style="color: var(--text-secondary);">{{ __('Total Revenue') }}</span>
                    <span style="font-weight: bold; color: var(--color-primary-light);">+ {{ number_format($financialSummary['all_time_revenue'], 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <span style="color: var(--text-secondary);">{{ __('Total Salaries Paid') }}</span>
                    <span style="font-weight: bold; color: var(--red);">- {{ number_format($financialSummary['all_time_salaries'], 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <span style="color: var(--text-secondary);">{{ __('Total Expenses') }}</span>
                    <span style="font-weight: bold; color: var(--red);">- {{ number_format($financialSummary['all_time_expenses'], 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                    <span style="font-size: 1.1rem; font-weight: bold; color: var(--text-primary);">{{ __('Total Net Profit') }}</span>
                    <span style="font-size: 1.5rem; font-weight: 800; color: {{ $financialSummary['all_time_net_profit'] >= 0 ? 'var(--green)' : 'var(--red)' }};">
                        {{ number_format($financialSummary['all_time_net_profit'], 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Tabs CSS -->
    <style>
        .report-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }
        .report-tab-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 10px 20px;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            font-family: var(--font-title);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .report-tab-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }
        .report-tab-btn.active {
            background: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
            box-shadow: 0 4px 10px rgba(50, 138, 241, 0.3);
        }
        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }
        .tab-content.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .chart-container {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 24px;
        }
    </style>

    <!-- Tabs Navigation -->
    <div class="report-tabs">
        <button type="button" class="report-tab-btn active" onclick="switchTab('services')">
            <i class="ri-customer-service-2-fill"></i>
            {{ __('Service Reports') }}
        </button>
        <button type="button" class="report-tab-btn" onclick="switchTab('employees')">
            <i class="ri-user-settings-fill"></i>
            {{ __('Employee Reports') }}
        </button>
    </div>

    <!-- TAB 1: Services -->
    <div id="tab-services" class="tab-content active">
        <!-- Service Chart -->
        <div class="chart-container" style="display: flex; justify-content: center;">
            <div style="width: 100%; max-width: 600px; height: 300px;">
                <canvas id="serviceChart"></canvas>
            </div>
        </div>

        <!-- Services Report Table -->
        <div class="card" style="margin-bottom: 24px;">
            <h4 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 20px; font-weight: 600;">
                <i class="ri-table-line" style="color: var(--color-primary); margin-right: 8px;"></i>
                {{ __('Revenue by Service') }}
            </h4>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Service') }}</th>
                            <th style="background: rgba(24, 24, 38, 0.5);">{{ __('Monthly Revenue') }}<br><small style="color: var(--text-secondary); font-weight: normal;">({{ $selectedDate->format('M Y') }})</small></th>
                            <th>{{ __('All-Time Revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceStats as $stat)
                            <tr>
                                <td><strong>{{ $stat['name'] }}</strong></td>
                                <td style="background: rgba(24, 24, 38, 0.5); font-weight: bold; color: var(--color-primary-light);">{{ number_format($stat['monthly_revenue'], 2) }}</td>
                                <td style="font-weight: bold;">{{ number_format($stat['all_time_revenue'], 2) }}</td>
                            </tr>
                        @endforeach
                        @if(count($serviceStats) === 0)
                            <tr><td colspan="5" style="text-align: center; color: var(--text-secondary);">{{ __('No services found.') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: Employees -->
    <div id="tab-employees" class="tab-content">
        <!-- Employee Chart -->
        <div class="chart-container">
            <div style="width: 100%; height: 350px;">
                <canvas id="employeeChart"></canvas>
            </div>
        </div>

        <!-- Employees Report Table -->
        <div class="card">
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
        // Tab Switching Logic
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.report-tab-btn').forEach(el => el.classList.remove('active'));
            
            document.getElementById('tab-' + tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        // Common Chart Defaults for Dark Theme
        Chart.defaults.color = '#a1a1aa';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
        Chart.defaults.font.family = "'Inter', 'Tajawal', sans-serif";

        // Service Pie Chart Data
        const serviceNames = @json($serviceStats->pluck('name'));
        const serviceRevenue = @json($serviceStats->pluck('monthly_revenue'));
        const serviceColors = [
            'rgba(50, 138, 241, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)'
        ];

        new Chart(document.getElementById('serviceChart'), {
            type: 'doughnut',
            data: {
                labels: serviceNames,
                datasets: [{
                    label: '{{ __("Monthly Revenue") }}',
                    data: serviceRevenue,
                    backgroundColor: serviceColors,
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' },
                    title: {
                        display: true,
                        text: '{{ __("Monthly Revenue Distribution by Service") }}',
                        color: '#fff',
                        font: { size: 16, weight: 'bold' }
                    }
                }
            }
        });

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
