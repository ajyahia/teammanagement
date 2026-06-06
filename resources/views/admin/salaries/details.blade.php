@extends('layouts.app')

@section('title', __('Salary Details'))
@section('page_header', __('Salary Details'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <!-- Header Navigation & Action -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
        <a href="/admin/salaries?month={{ $month }}&year={{ $year }}" class="btn btn-secondary" style="padding: 9px 16px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
            <i class="ri-arrow-left-line" style="font-size: 1.1rem; transform: scaleX(1);"></i>
            <span>{{ __('Back to Salaries') }}</span>
        </a>
        
        <form method="GET" action="/admin/salaries/{{ $user->id }}/details" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
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

    <!-- Employee Profile Info Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="user-avatar" style="width: 60px; height: 60px; font-size: 1.5rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ $user->name }}</h2>
                    <p style="color: var(--text-secondary); margin: 4px 0 0 0; font-size: 0.9rem;">
                        {{ $user->job_title }} &bull; {{ $user->email }} &bull; {{ $user->phone ?? __('N/A') }}
                    </p>
                </div>
            </div>
            
            <div style="text-align: right;">
                <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; display: block; margin-bottom: 4px;">
                    {{ __('Active Period') }}
                </span>
                <strong style="font-size: 1.1rem; color: var(--color-primary-light);">
                    {{ __(date('F', mktime(0, 0, 0, $month, 10))) }} {{ $year }}
                </strong>
            </div>
        </div>
    </div>

    <!-- Two Column Grid for Current Month Calculations & Add Adjustment Form -->
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

                <!-- Payment Status Section -->
                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-secondary); font-weight: 600;">{{ __('Payment Status') }}:</span>
                        @if($payment)
                            <span style="background-color: rgba(16, 185, 129, 0.1); color: rgb(16, 185, 129); border: 1px solid rgba(16, 185, 129, 0.2); padding: 6px 12px; border-radius: var(--radius-md); font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="ri-checkbox-circle-line"></i> {{ __('Paid') }}
                            </span>
                        @else
                            <span style="background-color: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); border: 1px solid rgba(239, 68, 68, 0.2); padding: 6px 12px; border-radius: var(--radius-md); font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="ri-error-warning-line"></i> {{ __('Unpaid') }}
                            </span>
                        @endif
                    </div>

                    @if($payment)
                        <div style="font-size: 0.85rem; color: var(--text-secondary); background-color: rgba(255, 255, 255, 0.02); padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                            <div><strong>{{ __('Payment Date') }}:</strong> {{ $payment->paid_at->format('Y-m-d H:i') }}</div>
                            @if($payment->notes)
                                <div style="margin-top: 4px;"><strong>{{ __('Notes') }}:</strong> {{ $payment->notes }}</div>
                            @endif
                        </div>

                        <form method="POST" action="/admin/salaries/{{ $user->id }}/unpay" onsubmit="return confirm('{{ __('Are you sure you want to cancel this payment record?') }}')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="month" value="{{ (int)$month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 10px; border-radius: var(--radius-md); font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 6px; background-color: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); border-color: rgba(239, 68, 68, 0.2);">
                                <i class="ri-close-circle-line" style="font-size: 1.1rem;"></i>
                                <span>{{ __('Cancel Payment') }}</span>
                            </button>
                        </form>
                    @else
                        <button type="button" id="recordPaymentBtn" class="btn btn-primary" onclick="document.getElementById('recordPaymentSection').style.display = 'block'; this.style.display = 'none';" style="width: 100%; padding: 10px; border-radius: var(--radius-md); font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="ri-hand-coin-line" style="font-size: 1.1rem;"></i>
                            <span>{{ __('Record Payment') }}</span>
                        </button>

                        <div id="recordPaymentSection" style="display: none; background-color: rgba(255, 255, 255, 0.02); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-top: 5px;">
                            <form method="POST" action="/admin/salaries/{{ $user->id }}/pay">
                                @csrf
                                <input type="hidden" name="month" value="{{ (int)$month }}">
                                <input type="hidden" name="year" value="{{ $year }}">
                                <input type="hidden" name="amount" value="{{ $activeReport['net_salary'] }}">
                                
                                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px;">
                                    <label for="payment_notes" style="font-weight: 600; font-size: 0.8rem;">{{ __('Notes / Details') }}</label>
                                    <textarea name="notes" id="payment_notes" rows="2" placeholder="{{ __('Write payment details (e.g. Bank Transfer ID, Cash receipt reference)...') }}" style="width: 100%; padding: 8px 12px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-body, var(--bg-main)); color: var(--text-primary); resize: none; font-size: 0.85rem; font-family: var(--font-body);"></textarea>
                                </div>

                                <div style="display: flex; gap: 8px;">
                                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 8px; font-size: 0.85rem; font-weight: 600; border-radius: var(--radius-md);">
                                        {{ __('Confirm') }}
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('recordPaymentSection').style.display = 'none'; document.getElementById('recordPaymentBtn').style.display = 'flex';" style="flex: 1; padding: 8px; font-size: 0.85rem; border-radius: var(--radius-md);">
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Column 2: Add New Adjustment -->
        <div class="card" style="height: 100%;">
            <h3 style="margin: 0 0 16px 0; font-family: var(--font-title); font-weight: 600; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i class="ri-add-circle-line" style="color: var(--color-primary-light);"></i>
                <span>{{ __('Add Adjustment') }}</span>
            </h3>

            <form method="POST" action="/admin/salaries" style="display: flex; flex-direction: column; gap: 16px;">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="month" value="{{ (int)$month }}">
                <input type="hidden" name="year" value="{{ $year }}">

                @if ($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 0; padding: 12px; border-radius: var(--radius-md); background-color: rgba(236, 69, 79, 0.1); border: 1px solid rgba(236, 69, 79, 0.2); color: var(--red); font-size: 0.85rem;">
                        <ul style="margin: 0; padding-inline-start: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Type Select -->
                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                    <label for="type" style="font-weight: 600; font-size: 0.85rem;">{{ __('Type') }}</label>
                    <select name="type" id="type" class="adj-form-select" required>
                        <option value="bonus">{{ __('Bonus') }} (+)</option>
                        <option value="deduction">{{ __('Deduction') }} (-)</option>
                    </select>
                </div>

                <!-- Amount Input -->
                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                    <label for="amount" style="font-weight: 600; font-size: 0.85rem;">{{ __('Amount') }}</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" placeholder="0.00" required style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-body, var(--bg-main)); color: var(--text-primary);">
                </div>

                <!-- Notes Input -->
                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                    <label for="notes" style="font-weight: 600; font-size: 0.85rem;">{{ __('Reason / Notes') }}</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="{{ __('Write reasons for deductions or bonuses here...') }}" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-body, var(--bg-main)); color: var(--text-primary); resize: none; font-family: var(--font-body);"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 12px; font-weight: 600; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 8px;">
                    <i class="ri-add-line" style="font-size: 1.2rem;"></i>
                    <span>{{ __('Add Adjustment') }}</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Adjustments Records for Current Month -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="margin: 0 0 16px 0; font-family: var(--font-title); font-weight: 600; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <i class="ri-file-list-3-line" style="color: var(--color-primary-light);"></i>
            <span>{{ __('Adjustments for') }} {{ __(date('F', mktime(0, 0, 0, $month, 10))) }} {{ $year }}</span>
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
                            <th style="text-align: center;">{{ __('Actions') }}</th>
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
                                <td style="text-align: center;">
                                    <form method="POST" action="/admin/salaries/adjustments/{{ $adj->id }}" style="display: inline;" onsubmit="return confirm('{{ __('Are you sure you want to delete this adjustment?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-adj-delete" title="{{ __('Delete') }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="adj-empty-state">
                <i class="ri-inbox-unarchive-line"></i>
                <p>{{ __('No adjustments recorded for this month yet.') }}</p>
            </div>
        @endif
    </div>

    <!-- Chronological Salaries History -->
    <div class="card">
        <h3 style="margin: 0 0 16px 0; font-family: var(--font-title); font-weight: 600; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <i class="ri-history-line" style="color: var(--color-primary-light);"></i>
            <span>{{ __('Salary Adjustments & Earnings History') }}</span>
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
                                {{ __('No salary logs recorded for this employee.') }}
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
