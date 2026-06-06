@extends('layouts.app')

@section('title', __('My Salary'))
@section('page_header', __('My Salary'))

@section('sidebar_menu')
    @include('layouts.sidebar_employee')
@endsection

@section('content')
    <!-- Header Navigation & Action -->
    <div style="display: flex; align-items: center; justify-content: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
        <form method="GET" action="/employee/salary" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Month Dropdown -->
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

            <!-- Year Dropdown -->
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
                <span>{{ __('Select Month') }}</span>
            </button>
        </form>
    </div>

    <!-- Active Period Header Card -->
    <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, rgba(33, 200, 246, 0.05) 0%, rgba(99, 123, 255, 0.05) 100%);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div>
                <h2 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Salary details for') }} {{ __(date('F', mktime(0, 0, 0, $month, 10))) }} {{ $year }}</h2>
                <p style="color: var(--text-secondary); margin: 4px 0 0 0; font-size: 0.9rem;">
                    {{ __('Here you can track your basic salary, bonuses, deductions, and payment status.') }}
                </p>
            </div>
            
            <div style="text-align: right;">
                <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; display: block; margin-bottom: 4px;">
                    {{ __('Payment Status') }}
                </span>
                @if($payment)
                    <span style="background-color: rgba(16, 185, 129, 0.1); color: rgb(16, 185, 129); border: 1px solid rgba(16, 185, 129, 0.2); padding: 6px 12px; border-radius: var(--radius-md); font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="ri-checkbox-circle-line"></i> {{ __('Paid') }}
                    </span>
                @else
                    <span style="background-color: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); border: 1px solid rgba(239, 68, 68, 0.2); padding: 6px 12px; border-radius: var(--radius-md); font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="ri-error-warning-line"></i> {{ __('Unpaid') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Two Column Grid for Current Month Calculations & Adjustments -->
    <div class="two-col-grid" style="margin-bottom: 24px;">
        <!-- Column 1: Calculation details -->
        <div class="card" style="height: 100%;">
            <h3 style="margin: 0 0 16px 0; font-family: var(--font-title); font-weight: 600; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i class="ri-calculator-line" style="color: var(--color-primary-light);"></i>
                <span>{{ __('Calculations Breakdown') }}</span>
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">{{ __('Basic Salary') }}:</span>
                    <strong>{{ number_format($activeReport['base_salary'], 2) }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">{{ __('Expected Effort') }}:</span>
                    <strong>
                        {{ $activeReport['expected_working_days'] }} {{ __('Days') }} / 
                        {{ number_format($activeReport['expected_working_hours'], 1) }} {{ __('Hrs') }}
                    </strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">{{ __('Shift Rate') }}:</span>
                    <span>
                        {{ number_format($activeReport['shift_hours'], 1) }} {{ __('hrs/day') }}
                    </span>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">{{ __('Attendance (Present)') }}:</span>
                    <strong style="color: var(--green);">
                        {{ $activeReport['present_days'] }} {{ __('Days') }}
                    </strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">{{ __('Absence (Days)') }}:</span>
                    <strong style="color: {{ $activeReport['absent_days'] > 0 ? 'var(--red)' : 'var(--text-primary)' }}">
                        {{ $activeReport['absent_days'] }} {{ __('Days') }}
                    </strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">{{ __('Absence Deduction') }}:</span>
                    <strong style="color: var(--red);">
                        {{ $activeReport['absent_deduction'] > 0 ? '-' : '' }}{{ number_format($activeReport['absent_deduction'], 2) }}
                    </strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">{{ __('Total Bonuses') }}:</span>
                    <strong style="color: var(--green);">
                        {{ $activeReport['manual_bonus'] > 0 ? '+' : '' }}{{ number_format($activeReport['manual_bonus'], 2) }}
                    </strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">{{ __('Total Deductions') }}:</span>
                    <strong style="color: var(--red);">
                        {{ $activeReport['manual_deduction'] > 0 ? '-' : '' }}{{ number_format($activeReport['manual_deduction'], 2) }}
                    </strong>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; background-color: rgba(50, 138, 241, 0.05); padding: 12px; border-radius: var(--radius-md);">
                    <span style="font-weight: 700; color: var(--color-primary-light);">{{ __('Net Salary') }}:</span>
                    <div style="font-family: var(--font-title); font-size: 1.4rem; font-weight: 800; color: var(--teal); text-shadow: 0 0 10px rgba(26, 171, 139, 0.3);">
                        {{ number_format($activeReport['net_salary'], 2) }}
                    </div>
                </div>

                @if($payment)
                    <div style="margin-top: 10px; padding: 12px; border-radius: var(--radius-md); background-color: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.1); font-size: 0.85rem; color: var(--text-secondary);">
                        <div style="color: var(--green); font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;">
                            <i class="ri-checkbox-circle-fill"></i> {{ __('Salary Paid Successfully') }}
                        </div>
                        <div><strong>{{ __('Payment Date') }}:</strong> {{ $payment->paid_at->format('Y-m-d H:i') }}</div>
                        @if($payment->notes)
                            <div style="margin-top: 2px;"><strong>{{ __('Reference/Notes') }}:</strong> {{ $payment->notes }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Column 2: Adjustments List -->
        <div class="card" style="height: 100%;">
            <h3 style="margin: 0 0 16px 0; font-family: var(--font-title); font-weight: 600; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i class="ri-file-list-3-line" style="color: var(--color-primary-light);"></i>
                <span>{{ __('Adjustments List') }}</span>
            </h3>

            @if($adjustments->count() > 0)
                <div class="table-responsive">
                    <table class="data-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th style="text-align: center;">{{ __('Reason') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adjustments as $idx => $adj)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        @if($adj->type === 'bonus')
                                            <span class="badge-bonus">{{ __('Bonus') }}</span>
                                        @else
                                            <span class="badge-deduction">{{ __('Deduction') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong style="color: {{ $adj->type === 'bonus' ? 'var(--green)' : 'var(--red)' }};">
                                            {{ $adj->type === 'bonus' ? '+' : '-' }}{{ number_format($adj->amount, 2) }}
                                        </strong>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--text-secondary);">
                                        {{ $adj->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if($adj->notes)
                                            <button type="button" class="btn-adj-info" onclick="showReasonModal('{{ addslashes($adj->notes) }}')" title="{{ __('View Reason') }}">
                                                <i class="ri-information-line"></i>
                                            </button>
                                        @else
                                            <span style="color: var(--text-secondary); font-size: 0.85rem;">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="adj-empty-state" style="padding: 40px 20px;">
                    <i class="ri-inbox-unarchive-line" style="font-size: 2.5rem; color: var(--text-secondary); opacity: 0.5; display: block; margin-bottom: 10px;"></i>
                    <p style="color: var(--text-secondary);">{{ __('No adjustments recorded for this month yet.') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Chronological Salaries History -->
    <div class="card">
        <h3 style="margin: 0 0 16px 0; font-family: var(--font-title); font-weight: 600; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <i class="ri-history-line" style="color: var(--color-primary-light);"></i>
            <span>{{ __('Salary History') }}</span>
        </h3>

        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>{{ __('Month / Year') }}</th>
                        <th>{{ __('Basic Salary') }}</th>
                        <th>{{ __('Absences') }}</th>
                        <th>{{ __('Absence Deduction') }}</th>
                        <th>{{ __('Total Bonuses') }}</th>
                        <th>{{ __('Total Deductions') }}</th>
                        <th>{{ __('Net Salary') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historyReports as $hr)
                        <tr style="{{ $hr['month'] == $month && $hr['year'] == $year ? 'background-color: rgba(50, 138, 241, 0.08); font-weight: 600;' : '' }}">
                            <td>
                                <strong>{{ __(date('F', mktime(0, 0, 0, $hr['month'], 10))) }} {{ $hr['year'] }}</strong>
                                @if($hr['month'] == $month && $hr['year'] == $year)
                                    <span class="badge badge-present" style="font-size: 0.7rem; padding: 2px 6px; margin-inline-start: 6px;">
                                        {{ __('Selected') }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ number_format($hr['base_salary'], 2) }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $hr['absent_days'] > 0 ? 'rgba(236, 69, 79, 0.1)' : 'rgba(255, 255, 255, 0.05)' }}; color: {{ $hr['absent_days'] > 0 ? 'var(--red)' : 'var(--text-secondary)' }}">
                                    {{ $hr['absent_days'] }} {{ __('Days') }}
                                </span>
                            </td>
                            <td style="color: var(--red);">
                                {{ $hr['absent_deduction'] > 0 ? '-' : '' }}{{ number_format($hr['absent_deduction'], 2) }}
                            </td>
                            <td style="color: var(--green);">
                                {{ $hr['manual_bonus'] > 0 ? '+' : '' }}{{ number_format($hr['manual_bonus'], 2) }}
                            </td>
                            <td style="color: var(--red);">
                                {{ $hr['manual_deduction'] > 0 ? '-' : '' }}{{ number_format($hr['manual_deduction'], 2) }}
                            </td>
                            <td>
                                <strong style="color: var(--teal);">
                                    {{ number_format($hr['net_salary'], 2) }}
                                </strong>
                            </td>
                            <td>
                                @if($hr['is_paid'])
                                    <span style="background-color: rgba(16, 185, 129, 0.1); color: rgb(16, 185, 129); border: 1px solid rgba(16, 185, 129, 0.2); padding: 4px 8px; border-radius: var(--radius-sm); font-size: 0.75rem; font-weight: 600; display: inline-block;">{{ __('Paid') }}</span>
                                @else
                                    <span style="background-color: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); border: 1px solid rgba(239, 68, 68, 0.2); padding: 4px 8px; border-radius: var(--radius-sm); font-size: 0.75rem; font-weight: 600; display: inline-block;">{{ __('Unpaid') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                {{ __('No salary logs recorded.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reason Modal (Custom Pure CSS/JS) -->
    <div class="reason-modal-overlay" id="reasonModalOverlay" onclick="closeReasonModal(event)">
        <div class="reason-modal" onclick="event.stopPropagation()">
            <div class="reason-modal-header">
                <h4>
                    <i class="ri-chat-quote-line"></i>
                    <span>{{ __('Reason / Notes') }}</span>
                </h4>
                <button class="reason-modal-close" onclick="closeReasonModal()">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            <div class="reason-modal-body" id="reasonModalBody">
                <!-- Reason text will be injected here -->
            </div>
        </div>
    </div>

    <script>
        function showReasonModal(reason) {
            document.getElementById('reasonModalBody').textContent = reason;
            document.getElementById('reasonModalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeReasonModal(e) {
            if (e && e.target !== document.getElementById('reasonModalOverlay')) return;
            document.getElementById('reasonModalOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReasonModal();
            }
        });
    </script>
@endsection
