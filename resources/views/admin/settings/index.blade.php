@extends('layouts.app')

@section('title', __('System Settings'))
@section('page_header', __('System Settings'))

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
    <div class="settings-grid">
        
        <!-- Weekly Holidays -->
        <div class="card" style="margin-bottom: 0;">
            <h3 class="card-title">
                <i class="ri-calendar-event-fill" style="color: var(--color-primary);"></i>
                <span>{{ __('Weekly Holidays') }}</span>
            </h3>
            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 20px;">
                {{ __('Select which days of the week are considered weekends/holidays.') }}
            </p>
            
            <form method="POST" action="/admin/settings/weekly">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px;">
                    @php
                        $days = [
                            6 => __('Saturday'),
                            0 => __('Sunday'),
                            1 => __('Monday'),
                            2 => __('Tuesday'),
                            3 => __('Wednesday'),
                            4 => __('Thursday'),
                            5 => __('Friday'),
                        ];
                    @endphp
                    @foreach($days as $idx => $name)
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; user-select: none;">
                            <input type="checkbox" name="weekly_holidays[]" value="{{ $idx }}" {{ in_array($idx, $weeklyHolidays) ? 'checked' : '' }}>
                            <span style="font-size: 0.95rem; font-weight: 500;">{{ $name }}</span>
                        </label>
                    @endforeach
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="ri-save-fill"></i>
                    <span>{{ __('Save Weekly Settings') }}</span>
                </button>
            </form>
        </div>
        
        <!-- Specific Calendar Holidays -->
        <div class="card" style="margin-bottom: 0;">
            <h3 class="card-title">
                <i class="ri-bookmark-3-fill" style="color: var(--orange);"></i>
                <span>{{ __('Calendar Holiday Dates') }}</span>
            </h3>
            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 20px;">
                {{ __('Add specific holiday dates (such as national days or official vacation days) that are unique to this year.') }}
            </p>
            
            <form method="POST" action="/admin/settings/specific" style="display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin-bottom: 24px;">
                @csrf
                <div class="form-group" style="gap: 6px; flex: 1; min-width: 150px;">
                    <label for="date">{{ __('Date') }} *</label>
                    <input type="text" name="date" id="holiday_date" class="custom-datepicker-input" placeholder="YYYY-MM-DD" required readonly style="padding: 10px 14px; width: 100%;">
                </div>
                <div class="form-group" style="gap: 6px; flex: 1.5; min-width: 180px;">
                    <label for="name">{{ __('Holiday Label') }}</label>
                    <input type="text" name="name" id="holiday_name" placeholder="{{ __('e.g. National Day') }}" style="padding: 10px 14px; width: 100%;">
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 11px 20px; height: 48px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="ri-add-line" style="font-size: 1.2rem;"></i>
                </button>
            </form>
            
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                <table class="data-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th style="padding: 12px 16px; font-size: 0.8rem;">{{ __('Date') }}</th>
                            <th style="padding: 12px 16px; font-size: 0.8rem;">{{ __('Holiday Name') }}</th>
                            <th style="padding: 12px 16px; font-size: 0.8rem; text-align: center; width: 60px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($specificHolidays as $holiday)
                            <tr>
                                <td style="padding: 12px 16px; font-size: 0.85rem;">{{ $holiday->date->format('Y-m-d') }}</td>
                                <td style="padding: 12px 16px; font-size: 0.85rem; color: var(--color-primary-light);">{{ $holiday->name ?: __('Official Holiday') }}</td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <form action="/admin/settings/specific/{{ $holiday->id }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to remove this holiday date?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-clear-record" title="{{ __('Delete') }}" style="width: 28px; height: 28px;">
                                            <i class="ri-delete-bin-fill" style="font-size: 0.95rem;"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-secondary); padding: 30px 10px; font-size: 0.85rem;">
                                    {{ __('No specific holiday dates registered.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Flatpickr for holiday date picker
            const localeStr = "{{ app()->getLocale() === 'ar' ? 'ar' : 'default' }}";
            flatpickr("#holiday_date", {
                locale: localeStr,
                dateFormat: "Y-m-d",
                disableMobile: true
            });
        });
    </script>
@endsection
