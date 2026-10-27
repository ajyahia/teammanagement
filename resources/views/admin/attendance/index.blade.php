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

        [dir="rtl"] .custom-datepicker-input {
            padding-right: 16px !important;
            padding-left: 44px !important;
            background-position: left 16px center !important;
        }

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

        /* Checkbox Attendance Cards Styling */
        .attendance-cards-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 15px;
            margin-bottom: 90px;
        }

        .attendance-check-card {
            background-color: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            user-select: none;
        }

        .attendance-check-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        }

        .attendance-check-card.is-present {
            border-color: rgba(34, 197, 94, 0.45);
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.08) 0%, rgba(34, 197, 94, 0.02) 100%);
        }

        .attendance-check-card.is-absent {
            border-color: rgba(239, 68, 68, 0.25);
            background: rgba(239, 68, 68, 0.02);
        }

        .attendance-check-card.is-other {
            border-color: rgba(245, 158, 11, 0.4);
            background: rgba(245, 158, 11, 0.04);
        }

        .attendance-checkbox-label {
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            margin: 0;
            flex: 1;
        }

        .custom-check-box {
            width: 26px;
            height: 26px;
            min-width: 26px;
            border-radius: 7px;
            border: 2px solid var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            background: var(--bg-hover);
            color: transparent;
            font-size: 1.1rem;
        }

        .attendance-check-card.is-present .custom-check-box {
            background-color: #22c55e;
            border-color: #22c55e;
            color: #ffffff;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);
        }

        .custom-check-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        /* Sticky Control Bar */
        .sticky-attendance-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-card);
            border-top: 1px solid var(--border-color);
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(10px);
        }

        @media (max-width: 768px) {
            .sticky-attendance-bar {
                flex-direction: column;
                align-items: stretch;
                padding: 12px 16px;
            }
            .sticky-attendance-bar > div {
                justify-content: center;
            }
        }
    </style>
@endsection

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <!-- Top Date Filter & Instructions Card -->
    <div class="card" style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Manage Daily Attendance') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 4px 0 0;">
                    {{ __('Checked employees will be marked as Present, unchecked as Absent.') }}
                </p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <form method="GET" action="{{ route('admin.attendance.index') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="date" id="date" class="custom-datepicker-input" value="{{ $date }}" readonly>
                    <button type="submit" class="btn btn-secondary" style="padding: 9px 16px;">
                        <i class="ri-refresh-line"></i>
                        <span>{{ __('Fetch') }}</span>
                    </button>
                </form>
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
        <!-- Bulk Form -->
        <form id="bulk-attendance-form" method="POST" action="{{ route('admin.attendance.bulk_store') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <!-- Quick Controls Card -->
            <div class="card" style="margin-bottom: 20px; padding: 16px 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <!-- Counters -->
                    <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Total Employees') }}:</span>
                            <span class="badge" style="background: var(--bg-hover); font-size: 0.95rem; font-weight: 700;">{{ $employees->count() }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #22c55e; font-size: 0.9rem;"><i class="ri-check-line"></i> {{ __('Present Count') }}:</span>
                            <span id="badge-present-count" class="badge" style="background: rgba(34,197,94,0.15); color: #22c55e; font-size: 0.95rem; font-weight: 700;">0</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #ef4444; font-size: 0.9rem;"><i class="ri-close-line"></i> {{ __('Absent Count') }}:</span>
                            <span id="badge-absent-count" class="badge" style="background: rgba(239,68,68,0.15); color: #ef4444; font-size: 0.95rem; font-weight: 700;">0</span>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-secondary" onclick="setAllCheckboxes(true)" style="padding: 8px 14px; font-size: 0.9rem;">
                            <i class="ri-checkbox-multiple-line"></i> {{ __('Select All (Present)') }}
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="setAllCheckboxes(false)" style="padding: 8px 14px; font-size: 0.9rem;">
                            <i class="ri-checkbox-blank-line"></i> {{ __('Deselect All (Absent)') }}
                        </button>
                        <button type="submit" class="btn btn-primary" style="background-color: #22c55e; border-color: #22c55e; padding: 8px 20px; font-weight: 700; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(34,197,94,0.3);">
                            <i class="ri-save-3-fill"></i> {{ __('Save Attendance') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Employee Checkbox Cards List -->
            <div class="attendance-cards-list">
                @foreach($employees as $emp)
                    @php
                        $record = $attendanceRecords->get($emp->id);
                        $status = $record ? $record->status : 'none';
                        $notes = $record ? $record->notes : '';
                        $isRecordedPresent = $record && $record->status === 'present';
                        $isOther = in_array($status, ['vacation', 'excused']);
                    @endphp
                    
                    <div class="attendance-check-card {{ $isRecordedPresent ? 'is-present' : ($isOther ? 'is-other' : 'is-absent') }}" id="card-{{ $emp->id }}" onclick="handleCardClick(event, {{ $emp->id }})">
                        <!-- Custom Checkbox & Employee Info -->
                        <label class="attendance-checkbox-label" for="check-{{ $emp->id }}">
                            <input type="checkbox" name="present_ids[]" value="{{ $emp->id }}" id="check-{{ $emp->id }}" class="custom-check-input attendance-checkbox" {{ $isRecordedPresent ? 'checked' : '' }} onchange="updateCardState({{ $emp->id }})">
                            
                            <div class="custom-check-box" id="box-{{ $emp->id }}">
                                <i class="ri-check-line"></i>
                            </div>

                            <div class="user-avatar" style="width: 44px; height: 44px; font-size: 1.1rem; flex-shrink: 0;">
                                {{ strtoupper(substr($emp->name, 0, 1)) }}
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                <div style="font-weight: 700; font-family: var(--font-title); font-size: 1.05rem; color: var(--text-primary);">
                                    {{ $emp->name }}
                                </div>
                                <span style="font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $emp->job_title }}
                                </span>
                            </div>
                        </label>

                        <!-- Live Status Indicator & Detail Drawer Toggle -->
                        <div style="display: flex; align-items: center; gap: 14px;" onclick="event.stopPropagation();">
                            <span class="badge" id="badge-{{ $emp->id }}" style="font-size: 0.85rem; padding: 6px 14px; min-width: 90px; text-align: center;">
                                @if($isRecordedPresent)
                                    <span style="color: #22c55e;"><i class="ri-check-line"></i> {{ __('Present') }}</span>
                                @elseif($status === 'vacation')
                                    <span style="color: var(--orange);"><i class="ri-plane-line"></i> {{ __('Vacation') }}</span>
                                @elseif($status === 'excused')
                                    <span style="color: var(--color-primary);"><i class="ri-time-line"></i> {{ __('Excused') }}</span>
                                @else
                                    <span style="color: #ef4444;"><i class="ri-close-line"></i> {{ __('Absent') }}</span>
                                @endif
                            </span>

                            <!-- Toggle extra note/reason drawer -->
                            <button type="button" class="btn btn-icon btn-secondary" title="{{ __('Add Note / Vacation / Excused') }}" onclick="toggleDrawer({{ $emp->id }})" style="width: 36px; height: 36px;">
                                <i class="ri-more-2-fill"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden Drawer for Notes / Status Override (Vacation / Excused) -->
                    <div id="drawer-{{ $emp->id }}" style="display: {{ $notes || $isOther ? 'block' : 'none' }}; background: rgba(0,0,0,0.15); border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 14px 18px; margin-top: -6px; margin-bottom: 6px;" onclick="event.stopPropagation();">
                        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                            <div style="width: 170px;">
                                <label style="font-size: 0.75rem; color: var(--text-secondary); display: block; margin-bottom: 4px;">{{ __('Special Status Override') }}</label>
                                <select name="statuses[{{ $emp->id }}]" id="status-override-{{ $emp->id }}" class="form-control" style="padding: 6px 10px; font-size: 0.85rem;" onchange="handleOverrideChange({{ $emp->id }})">
                                    <option value="">-- {{ __('Follow Checkbox (Present/Absent)') }} --</option>
                                    <option value="vacation" {{ $status === 'vacation' ? 'selected' : '' }}>{{ __('Vacation') }}</option>
                                    <option value="excused" {{ $status === 'excused' ? 'selected' : '' }}>{{ __('Excused') }}</option>
                                </select>
                            </div>
                            <div style="flex: 1; min-width: 200px;">
                                <label style="font-size: 0.75rem; color: var(--text-secondary); display: block; margin-bottom: 4px;">{{ __('Notes / Detail') }}</label>
                                <input type="text" name="notes[{{ $emp->id }}]" value="{{ $notes }}" placeholder="{{ __('Notes / Detail') }}" class="form-control" style="padding: 6px 12px; font-size: 0.85rem;">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Sticky Bottom Save Bar -->
            <div class="sticky-attendance-bar">
                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <span style="color: #22c55e; font-weight: 700; font-size: 1rem;">
                        <i class="ri-checkbox-circle-fill" style="margin-right: 4px;"></i> {{ __('Present Count') }}: <strong id="sticky-present-count">0</strong>
                    </span>
                    <span style="color: #ef4444; font-weight: 700; font-size: 1rem;">
                        <i class="ri-close-circle-fill" style="margin-right: 4px;"></i> {{ __('Absent Count') }}: <strong id="sticky-absent-count">0</strong>
                    </span>
                </div>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <button type="submit" class="btn btn-primary" style="background-color: #22c55e; border-color: #22c55e; padding: 11px 28px; font-weight: 700; font-size: 1.05rem; box-shadow: 0 4px 16px rgba(34,197,94,0.45);">
                        <i class="ri-save-3-fill"></i> {{ __('Save Attendance') }}
                    </button>
                </div>
            </div>
        </form>
    @endif
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Flatpickr for custom date picker
            const localeStr = "{{ app()->getLocale() === 'ar' ? 'ar' : 'default' }}";
            const weeklyHolidays = @json($weeklyHolidays ?? []);
            flatpickr("#date", {
                locale: localeStr,
                dateFormat: "Y-m-d",
                defaultDate: "{{ $date }}",
                disableMobile: true,
                disable: [
                    function(date) {
                        return weeklyHolidays.includes(date.getDay());
                    }
                ],
                onChange: function(selectedDates, dateStr, instance) {
                    instance.element.closest('form').submit();
                }
            });

            recalculateCounters();
        });

        function handleCardClick(event, empId) {
            // Prevent double-toggling if directly clicking the label/input
            if (event.target.tagName.toLowerCase() === 'input' || event.target.tagName.toLowerCase() === 'select' || event.target.closest('button')) {
                return;
            }
            const checkbox = document.getElementById('check-' + empId);
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                updateCardState(empId);
            }
        }

        function updateCardState(empId) {
            const checkbox = document.getElementById('check-' + empId);
            const card = document.getElementById('card-' + empId);
            const badge = document.getElementById('badge-' + empId);
            const overrideSelect = document.getElementById('status-override-{{ $emp->id ?? 0 }}') || document.getElementById('status-override-' + empId);
            
            const overrideVal = overrideSelect ? overrideSelect.value : '';

            if (overrideVal === 'vacation') {
                card.className = 'attendance-check-card is-other';
                badge.innerHTML = '<span style="color: var(--orange);"><i class="ri-plane-line"></i> {{ __('Vacation') }}</span>';
                badge.style.background = 'rgba(245,158,11,0.15)';
            } else if (overrideVal === 'excused') {
                card.className = 'attendance-check-card is-other';
                badge.innerHTML = '<span style="color: var(--color-primary);"><i class="ri-time-line"></i> {{ __('Excused') }}</span>';
                badge.style.background = 'rgba(50,138,241,0.15)';
            } else if (checkbox.checked) {
                card.className = 'attendance-check-card is-present';
                badge.innerHTML = '<span style="color: #22c55e;"><i class="ri-check-line"></i> {{ __('Present') }}</span>';
                badge.style.background = 'rgba(34,197,94,0.15)';
            } else {
                card.className = 'attendance-check-card is-absent';
                badge.innerHTML = '<span style="color: #ef4444;"><i class="ri-close-line"></i> {{ __('Absent') }}</span>';
                badge.style.background = 'rgba(239,68,68,0.15)';
            }

            recalculateCounters();
        }

        function handleOverrideChange(empId) {
            const overrideSelect = document.getElementById('status-override-' + empId);
            const checkbox = document.getElementById('check-' + empId);
            if (overrideSelect && overrideSelect.value !== '') {
                // If vacation or excused is chosen, uncheck present
                checkbox.checked = false;
            }
            updateCardState(empId);
        }

        function setAllCheckboxes(checked) {
            const checkboxes = document.querySelectorAll('.attendance-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = checked;
                const empId = cb.value;
                const overrideSelect = document.getElementById('status-override-' + empId);
                if (overrideSelect) {
                    overrideSelect.value = '';
                }
                updateCardState(empId);
            });
            recalculateCounters();
        }

        function recalculateCounters() {
            const checkboxes = document.querySelectorAll('.attendance-checkbox');
            let presentCount = 0;
            let absentCount = 0;

            checkboxes.forEach(cb => {
                const empId = cb.value;
                const overrideSelect = document.getElementById('status-override-' + empId);
                const overrideVal = overrideSelect ? overrideSelect.value : '';

                if (overrideVal === 'vacation' || overrideVal === 'excused') {
                    // Not counted as regular present
                } else if (cb.checked) {
                    presentCount++;
                } else {
                    absentCount++;
                }
            });

            const badgePresent = document.getElementById('badge-present-count');
            const badgeAbsent = document.getElementById('badge-absent-count');
            const stickyPresent = document.getElementById('sticky-present-count');
            const stickyAbsent = document.getElementById('sticky-absent-count');

            if (badgePresent) badgePresent.textContent = presentCount;
            if (badgeAbsent) badgeAbsent.textContent = absentCount;
            if (stickyPresent) stickyPresent.textContent = presentCount;
            if (stickyAbsent) stickyAbsent.textContent = absentCount;
        }

        function toggleDrawer(empId) {
            const drawer = document.getElementById('drawer-' + empId);
            if (drawer) {
                drawer.style.display = drawer.style.display === 'none' ? 'block' : 'none';
            }
        }
    </script>
@endsection
