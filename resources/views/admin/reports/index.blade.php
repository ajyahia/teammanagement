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
                    <span style="color: var(--text-secondary);">المصروفات</span>
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
                    <span style="color: var(--text-secondary);">إجمالي المصروفات</span>
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

    <!-- Services Report -->
    <div class="card" style="margin-bottom: 24px;">
        <h4 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 20px; font-weight: 600;">
            <i class="ri-customer-service-2-fill" style="color: var(--color-primary); margin-right: 8px;"></i>
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

    <!-- Employees Report -->
    <div class="card">
        <h4 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 20px; font-weight: 600;">
            <i class="ri-user-settings-fill" style="color: var(--orange); margin-right: 8px;"></i>
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
@endsection
