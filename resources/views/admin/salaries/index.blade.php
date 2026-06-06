@extends('layouts.app')

@section('title', __('Monthly Salaries'))
@section('page_header', __('Monthly Salaries'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Monthly Salaries Calculation') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">
                    {{ __('Expected Working Days for this month') }}: <strong>{{ $expectedWorkingDays }} {{ __('Days') }}</strong> &bull; 
                    <strong>{{ __(date('F', mktime(0, 0, 0, $month, 10))) }} {{ $year }}</strong>
                </p>
            </div>
            
            <form method="GET" action="/admin/salaries" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
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

    <div class="table-responsive">
        <div class="salary-cards-list">
        @forelse($reports as $r)
            @php
                $emp = $r['employee'];
            @endphp
            <div class="salary-row-card">
                <!-- Employee details block -->
                <div class="salary-emp-block">
                    <div class="user-avatar" style="width: 44px; height: 44px; font-size: 1.1rem;">
                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                    </div>
                    <div class="emp-details">
                        <h4 class="emp-name">{{ $emp->name }}</h4>
                        <span class="emp-title">{{ $emp->job_title }}</span>
                    </div>
                </div>

                <!-- Basic Salary (المرتب قبل) -->
                <div class="salary-expected-block">
                    <div class="salary-expected-title">{{ __('Basic Salary') }}</div>
                    <div class="salary-expected-value">
                        {{ number_format($r['base_salary'], 2) }}
                    </div>
                </div>

                <!-- Total Deductions (إجمالي الخصومات) -->
                <div class="salary-expected-block">
                    <div class="salary-expected-title" style="color: var(--red);">{{ __('Total Deductions') }}</div>
                    <div class="salary-expected-value" style="color: var(--red);">
                        {{ $r['total_deductions'] > 0 ? '-' : '' }}{{ number_format($r['total_deductions'], 2) }}
                    </div>
                </div>

                <!-- Total Bonuses (إجمالي العلاوات) -->
                <div class="salary-expected-block">
                    <div class="salary-expected-title" style="color: var(--green);">{{ __('Total Bonuses') }}</div>
                    <div class="salary-expected-value" style="color: var(--green);">
                        {{ $r['total_bonuses'] > 0 ? '+' : '' }}{{ number_format($r['total_bonuses'], 2) }}
                    </div>
                </div>

                <!-- Net Salary Display (الصافي / المرتب بعد) -->
                <div class="salary-net-block">
                    <div class="net-salary-title">{{ __('Net Salary') }}</div>
                    <div class="net-salary-amount">
                        {{ number_format($r['net_salary'], 2) }}
                    </div>
                </div>

                <!-- Payment Status -->
                <div class="salary-expected-block" style="display: flex; flex-direction: column; justify-content: center; align-items: center; min-width: 90px;">
                    <div class="salary-expected-title" style="margin-bottom: 4px;">{{ __('Status') }}</div>
                    @if($r['is_paid'])
                        <span style="background-color: rgba(16, 185, 129, 0.1); color: rgb(16, 185, 129); border: 1px solid rgba(16, 185, 129, 0.2); padding: 4px 10px; border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 600;">{{ __('Paid') }}</span>
                    @else
                        <span style="background-color: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); border: 1px solid rgba(239, 68, 68, 0.2); padding: 4px 10px; border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 600;">{{ __('Unpaid') }}</span>
                    @endif
                </div>

                <!-- Action Button -->
                <div style="display: flex; align-items: center; justify-content: flex-end;">
                    <a href="/admin/salaries/{{ $emp->id }}/details?month={{ $month }}&year={{ $year }}" class="btn-salary-action" title="{{ __('Salary Details') }}">
                        <i class="ri-eye-line"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="card" style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                <i class="ri-money-dollar-circle-line" style="font-size: 3rem; color: var(--text-secondary); display: block; margin-bottom: 12px;"></i>
                {{ __('No employees registered in the system.') }}
            </div>
        @endforelse
        </div>
    </div>
@endsection
