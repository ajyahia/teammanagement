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
                        <th style="background: rgba(24, 24, 38, 0.5);">{{ __('Monthly Profit') }}</th>
                        <th>{{ __('All-Time Revenue') }}</th>
                        <th>{{ __('All-Time Profit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($serviceStats as $stat)
                        <tr>
                            <td><strong>{{ $stat['name'] }}</strong></td>
                            <td style="background: rgba(24, 24, 38, 0.5); font-weight: bold; color: var(--color-primary-light);">{{ number_format($stat['monthly_revenue'], 2) }}</td>
                            <td style="background: rgba(24, 24, 38, 0.5); font-weight: bold; color: var(--green);">{{ number_format($stat['monthly_profit'], 2) }}</td>
                            <td style="font-weight: bold;">{{ number_format($stat['all_time_revenue'], 2) }}</td>
                            <td style="font-weight: bold; color: var(--green);">{{ number_format($stat['all_time_profit'], 2) }}</td>
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
                        <th style="background: rgba(24, 24, 38, 0.5);">{{ __('Monthly Profit') }}</th>
                        <th>{{ __('All-Time Revenue') }}</th>
                        <th>{{ __('All-Time Profit') }}</th>
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
                            <td style="background: rgba(24, 24, 38, 0.5); font-weight: bold; color: var(--green);">{{ number_format($stat['monthly_profit'], 2) }}</td>
                            <td style="font-weight: bold;">{{ number_format($stat['all_time_revenue'], 2) }}</td>
                            <td style="font-weight: bold; color: var(--green);">{{ number_format($stat['all_time_profit'], 2) }}</td>
                        </tr>
                    @endforeach
                    @if(count($employeeStats) === 0)
                        <tr><td colspan="5" style="text-align: center; color: var(--text-secondary);">{{ __('No employees found.') }}</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
