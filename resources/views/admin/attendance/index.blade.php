@extends('layouts.app')

@section('title', __('Manage Daily Attendance'))
@section('page_header', __('Manage Daily Attendance'))

@section('styles')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar {
            background-color: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: var(--radius-lg) !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4) !important;
            font-family: var(--font-body) !important;
            color: var(--text-primary) !important;
        }

        .flatpickr-calendar::before,
        .flatpickr-calendar::after {
            border-bottom-color: var(--border-color) !important;
        }

        .flatpickr-months {
            background-color: transparent !important;
            padding-top: 10px !important;
        }

        .flatpickr-months .flatpickr-month {
            color: var(--text-primary) !important;
        }

        .flatpickr-current-month {
            font-family: var(--font-title) !important;
            font-weight: 700 !important;
            color: var(--text-primary) !important;
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
        }

        .flatpickr-monthDropdown-months {
            background-color: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border: none !important;
            outline: none !important;
            padding: 2px 6px !important;
            border-radius: var(--radius-sm) !important;
            cursor: pointer !important;
            font-family: var(--font-title) !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
        }

        .flatpickr-monthDropdown-months:hover {
            background-color: var(--bg-hover) !important;
        }

        .flatpickr-monthDropdown-months option {
            background-color: var(--bg-card) !important;
            color: var(--text-primary) !important;
            padding: 8px !important;
        }

        .flatpickr-current-month input.cur-year {
            background: transparent !important;
            color: var(--text-primary) !important;
            border: none !important;
            font-weight: 700 !important;
            font-family: var(--font-title) !important;
        }

        .flatpickr-current-month .numInputWrapper span.arrowUp::after {
            border-bottom-color: var(--text-secondary) !important;
        }
        .flatpickr-current-month .numInputWrapper span.arrowDown::after {
            border-top-color: var(--text-secondary) !important;
        }

        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            color: var(--text-secondary) !important;
            fill: var(--text-secondary) !important;
            top: 10px !important;
            padding: 10px !important;
            border-radius: var(--radius-sm) !important;
            transition: var(--transition) !important;
        }

        .flatpickr-months .flatpickr-prev-month:hover,
        .flatpickr-months .flatpickr-next-month:hover {
            color: var(--text-primary) !important;
            fill: var(--text-primary) !important;
            background-color: var(--bg-hover) !important;
        }

        .flatpickr-weekday {
            color: var(--text-secondary) !important;
            font-family: var(--font-title) !important;
            font-weight: 600 !important;
        }

        .flatpickr-day {
            color: var(--text-primary) !important;
            border-radius: var(--radius-md) !important;
            transition: var(--transition) !important;
            font-family: var(--font-body) !important;
            font-weight: 500 !important;
        }

        .flatpickr-day:hover,
        .flatpickr-day:focus {
            background-color: var(--bg-hover) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: var(--text-primary) !important;
        }

        .flatpickr-day.selected,
        .flatpickr-day.selected:hover,
        .flatpickr-day.selected:focus {
            background-color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
            color: white !important;
            box-shadow: 0 4px 10px rgba(50, 138, 241, 0.3) !important;
        }

        .flatpickr-day.today {
            border-color: rgba(50, 138, 241, 0.4) !important;
            color: var(--color-primary-light) !important;
        }

        .flatpickr-day.today:hover {
            background-color: var(--bg-hover) !important;
            color: var(--text-primary) !important;
        }

        .flatpickr-day.prevMonthDay,
        .flatpickr-day.nextMonthDay {
            color: rgba(148, 163, 184, 0.25) !important;
        }

        .flatpickr-day.flatpickr-disabled {
            color: rgba(148, 163, 184, 0.15) !important;
        }

        .custom-datepicker-input {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2394a3b8'%3e%3cpath d='M9 1v2h6V1h2v2h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4V1h2zm11 8H4v10h16V9zm-2 2v2h-2v-2h2zm-4 0v2h-2v-2h2zm-4 0v2H6v-2h2z'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-size: 18px !important;
            cursor: pointer !important;
            padding-right: 44px !important;
            background-position: right 16px center !important;
            min-width: 140px;
        }

        /* RTL adjustments for datepicker input */
        [dir="rtl"] .custom-datepicker-input {
            padding-right: 16px !important;
            padding-left: 44px !important;
            background-position: left 16px center !important;
        }

        /* RTL Overrides for Flatpickr */
        [dir="rtl"] .flatpickr-calendar {
            direction: rtl;
        }

        [dir="rtl"] .flatpickr-calendar .dayContainer, 
        [dir="rtl"] .flatpickr-calendar .flatpickr-weekdaycontainer {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .flatpickr-calendar .flatpickr-months .flatpickr-prev-month {
            left: unset !important;
            right: 0 !important;
        }

        [dir="rtl"] .flatpickr-calendar .flatpickr-months .flatpickr-next-month {
            right: unset !important;
            left: 0 !important;
        }
    </style>
@endsection

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Daily Attendance Grid') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">
                    {{ __('Configure status logs for') }}: <strong>{{ date('d', strtotime($date)) }} {{ __(date('F', strtotime($date))) }} {{ date('Y', strtotime($date)) }}</strong>
                </p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <form method="GET" action="/admin/attendance" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="date" id="date" class="custom-datepicker-input" value="{{ $date }}" readonly>
                    <button type="submit" class="btn btn-secondary" style="padding: 9px 16px;">
                        <i class="ri-refresh-line"></i>
                        <span>{{ __('Fetch') }}</span>
                    </button>
                </form>

                @if(!$isHoliday)
                <form method="POST" action="/admin/attendance/mark-all-present" onsubmit="return confirm('{{ __('Are you sure you want to mark all employees as present for this date?') }}');">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" class="btn btn-primary" style="padding: 9px 16px; background-color: var(--green); border-color: var(--green);">
                        <i class="ri-checkbox-circle-fill"></i>
                        <span>{{ __('Mark All Present') }}</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    @if($isHoliday)
        <div class="card" style="text-align: center; padding: 50px 20px; border-color: rgba(241, 154, 26, 0.2); background: linear-gradient(135deg, rgba(241, 154, 26, 0.05) 0%, rgba(255, 199, 60, 0.05) 100%);">
            <i class="ri-calendar-close-fill" style="font-size: 4rem; color: var(--orange); text-shadow: 0 0 20px rgba(241, 154, 26, 0.4); display: block; margin-bottom: 20px;"></i>
            <h3 style="font-family: var(--font-title); font-size: 1.5rem; font-weight: 700; margin-bottom: 10px;">{{ __('Holiday / Weekend') }}</h3>
            <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto 20px; font-size: 0.95rem;">
                {{ __('Attendance registration is disabled because this day is marked as a holiday.') }}
            </p>
            @if($holidayName)
                <span class="badge badge-vacation" style="font-size: 0.9rem; padding: 6px 16px; border-radius: var(--radius-sm);">
                    <i class="ri-bookmark-3-fill"></i> {{ $holidayName }}
                </span>
            @endif
        </div>
    @else
        <div class="attendance-cards-list">
            @foreach($employees as $emp)
                @php
                    $record = $attendanceRecords->get($emp->id);
                    $status = $record ? $record->status : 'none';
                    $notes = $record ? $record->notes : '';
                @endphp
                <div class="attendance-row-card">
                    <!-- Employee Profile Info -->
                    <div class="emp-info-block">
                        <div class="user-avatar" style="width: 44px; height: 44px; font-size: 1.1rem;">
                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                        </div>
                        <div class="emp-details">
                            <h4 class="emp-name">{{ $emp->name }}</h4>
                            <span class="emp-title">{{ $emp->job_title }}</span>
                        </div>
                    </div>
                    
                    <!-- Current Status Indicator -->
                    <div class="emp-status-block">
                        <span class="badge badge-{{ $status }}">
                            {{ $status === 'none' ? __('not marked') : __(ucfirst($status)) }}
                        </span>
                        @if($notes)
                            <div class="emp-notes-text">
                                <i class="ri-chat-1-line" style="color: var(--text-secondary);"></i>
                                <span>{{ $notes }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Update Actions Form -->
                    <div class="emp-action-block">
                        <form method="POST" action="/admin/attendance" class="attendance-inline-form">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $emp->id }}">
                            <input type="hidden" name="date" value="{{ $date }}">
                            
                            <!-- Custom Select Dropdown -->
                            <div class="custom-dropdown" id="dropdown-status-{{ $emp->id }}">
                                <div class="dropdown-trigger">
                                    <span class="selected-text">{{ $status === 'none' ? __('Present') : __(ucfirst($status)) }}</span>
                                    <i class="ri-arrow-down-s-line"></i>
                                </div>
                                <div class="dropdown-menu">
                                    <div class="dropdown-item {{ $status === 'present' || $status === 'none' ? 'selected' : '' }}" data-value="present">{{ __('Present') }}</div>
                                    <div class="dropdown-item {{ $status === 'absent' ? 'selected' : '' }}" data-value="absent">{{ __('Absent') }}</div>
                                    <div class="dropdown-item {{ $status === 'vacation' ? 'selected' : '' }}" data-value="vacation">{{ __('Vacation') }}</div>
                                    <div class="dropdown-item {{ $status === 'excused' ? 'selected' : '' }}" data-value="excused">{{ __('Excused') }}</div>
                                </div>
                                <input type="hidden" name="status" class="dropdown-value-input" value="{{ $status === 'none' ? 'present' : $status }}" required>
                            </div>
                            
                            <!-- Notes Input -->
                            <input type="text" name="notes" placeholder="{{ __('Notes / Detail') }}" value="{{ $notes }}" class="notes-input">
                            
                            <!-- Save Button -->
                            <button type="submit" class="btn-save" title="{{ __('Save') }}">
                                <i class="ri-check-line" style="font-size: 1.3rem;"></i>
                                <span class="btn-text">{{ __('Save') }}</span>
                            </button>
                        </form>
                        
                        @if($record)
                            <form action="/admin/attendance/{{ $record->id }}" method="POST" class="clear-form" onsubmit="return confirm('{{ __('Do you want to clear this attendance record?') }}');" style="display: block; width: 100%;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-clear-record" title="{{ __('Clear Record') }}">
                                    <i class="ri-delete-bin-fill" style="font-size: 1.1rem;"></i>
                                    <span class="btn-text">{{ __('Clear Record') }}</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Flatpickr for custom date picker
            const localeStr = "{{ app()->getLocale() === 'ar' ? 'ar' : 'default' }}";
            flatpickr("#date", {
                locale: localeStr,
                dateFormat: "Y-m-d",
                defaultDate: "{{ $date }}",
                disableMobile: true,
                onChange: function(selectedDates, dateStr, instance) {
                    // Submit the form automatically when date changes
                    instance.element.closest('form').submit();
                }
            });
        });
    </script>
@endsection
