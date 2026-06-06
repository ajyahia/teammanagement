@extends('layouts.app')

@section('title', __('Dashboard'))
@section('page_header', __('Dashboard'))

@section('sidebar_menu')
    @include('layouts.sidebar_employee')
@endsection

@section('content')
    <!-- Header Welcome Card -->
    <div class="card welcome-card" style="background: linear-gradient(135deg, rgba(33, 200, 246, 0.08) 0%, rgba(99, 123, 255, 0.08) 100%); border-color: rgba(33, 200, 246, 0.2); padding: 30px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 24px;">
        <div>
            <span style="background: rgba(33, 200, 246, 0.15); color: var(--cyan); padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                <i class="ri-user-smile-line"></i> {{ __('Employee Portal') }}
            </span>
            <h2 style="font-family: var(--font-title); font-size: 1.8rem; margin: 0 0 8px 0; font-weight: 800; background: linear-gradient(135deg, var(--text-primary) 0%, rgba(248, 250, 252, 0.8) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                {{ __('Welcome back') }}, {{ auth()->user()->name }}!
            </h2>
            <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; margin: 0;">
                {{ __('You are logged into the Employee Portal. Here is your monthly attendance status.') }}
            </p>
        </div>

        <!-- Today's Attendance Status Badge -->
        <div style="text-align: right;">
            <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; display: block; margin-bottom: 6px; letter-spacing: 0.05em;">
                {{ __('Today\'s Status') }}
            </span>
            @if($isHoliday)
                <span style="background-color: rgba(33, 200, 246, 0.1); color: var(--cyan); border: 1px solid rgba(33, 200, 246, 0.2); padding: 8px 16px; border-radius: var(--radius-md); font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ri-calendar-event-line"></i> {{ __('Holiday') }} ({{ $holidayName }})
                </span>
            @elseif($todayRecord)
                @if($todayRecord->status === 'present')
                    <span style="background-color: rgba(26, 171, 139, 0.1); color: var(--green); border: 1px solid rgba(26, 171, 139, 0.2); padding: 8px 16px; border-radius: var(--radius-md); font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="ri-checkbox-circle-line"></i> {{ __('Present Today') }}
                    </span>
                @elseif($todayRecord->status === 'absent')
                    <span style="background-color: rgba(236, 69, 79, 0.1); color: var(--red); border: 1px solid rgba(236, 69, 79, 0.2); padding: 8px 16px; border-radius: var(--radius-md); font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="ri-close-circle-line"></i> {{ __('Absent Today') }}
                    </span>
                @elseif($todayRecord->status === 'vacation')
                    <span style="background-color: rgba(255, 199, 60, 0.1); color: var(--yellow); border: 1px solid rgba(255, 199, 60, 0.2); padding: 8px 16px; border-radius: var(--radius-md); font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="ri-plane-line"></i> {{ __('On Vacation') }}
                    </span>
                @else
                    <span style="background-color: rgba(139, 96, 237, 0.1); color: var(--purple); border: 1px solid rgba(139, 96, 237, 0.2); padding: 8px 16px; border-radius: var(--radius-md); font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="ri-file-warning-line"></i> {{ __('Excused Today') }}
                    </span>
                @endif
            @else
                <span style="background-color: rgba(241, 154, 26, 0.1); color: var(--orange); border: 1px solid rgba(241, 154, 26, 0.2); padding: 8px 16px; border-radius: var(--radius-md); font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ri-time-line"></i> {{ __('Not Marked Yet') }}
                </span>
            @endif
        </div>
    </div>

    <!-- Working Hours Countdown Widget -->
    @if($isHoliday)
        <div class="countdown-widget card holiday-state" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(33, 200, 246, 0.02) 100%); margin-bottom: 24px;">
            <div class="holiday-content" style="display: flex; align-items: center; gap: 20px;">
                <div class="holiday-icon" style="background: rgba(33, 200, 246, 0.08); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--cyan); font-size: 28px; box-shadow: 0 0 15px rgba(33, 200, 246, 0.25);">
                    <i class="ri-calendar-event-line"></i>
                </div>
                <div class="holiday-info">
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 6px 0;">{{ __('Today is a Holiday') }} ({{ $holidayName }})</h3>
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">{{ __('Enjoy your official day off! Have a great time.') }}</p>
                </div>
            </div>
            <div class="countdown-details" style="margin-top: 20px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-secondary); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">{{ __('Standard Shift') }}</span>
                    <span style="font-weight: 700; font-size: 1rem; direction: ltr; display: inline-block; color: var(--text-primary);">{{ auth()->user()->work_start_time ? auth()->user()->work_start_time->format('H:i') : '09:00' }} - {{ auth()->user()->work_end_time ? auth()->user()->work_end_time->format('H:i') : '17:00' }}</span>
                </div>
            </div>
        </div>
    @else
        <div class="countdown-widget card" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(50, 138, 241, 0.02) 100%); margin-bottom: 24px;">
            <div class="countdown-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px;">
                <div class="widget-title" style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 1.1rem;">
                    <i class="ri-time-line" style="color: var(--color-primary-light);"></i>
                    <span>{{ __('Shift Countdown') }}</span>
                </div>
                <div id="shift-status-badge" class="badge">
                    <span class="pulse-dot"></span>
                    <span id="shift-status-text"></span>
                </div>
            </div>

            <div class="countdown-body" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; align-items: center;">
                <div class="countdown-timer" id="countdown-timer" style="display: flex; align-items: center; justify-content: center; gap: 10px; background: rgba(0, 0, 0, 0.15); padding: 20px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); direction: ltr;">
                    <div class="timer-segment" style="display: flex; flex-direction: column; align-items: center;">
                        <span class="timer-num" id="hours-val" style="font-family: var(--font-title); font-size: 2.2rem; font-weight: 800; color: var(--color-primary-light); text-shadow: 0 0 10px rgba(186, 217, 252, 0.3);">00</span>
                        <span class="timer-label" style="font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; margin-top: 4px; letter-spacing: 0.05em;">{{ __('Hours') }}</span>
                    </div>
                    <div class="timer-separator" style="font-size: 2rem; font-weight: 800; color: var(--border-color); padding-bottom: 18px;">:</div>
                    <div class="timer-segment" style="display: flex; flex-direction: column; align-items: center;">
                        <span class="timer-num" id="minutes-val" style="font-family: var(--font-title); font-size: 2.2rem; font-weight: 800; color: var(--color-primary-light); text-shadow: 0 0 10px rgba(186, 217, 252, 0.3);">00</span>
                        <span class="timer-label" style="font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; margin-top: 4px; letter-spacing: 0.05em;">{{ __('Minutes') }}</span>
                    </div>
                    <div class="timer-separator" style="font-size: 2rem; font-weight: 800; color: var(--border-color); padding-bottom: 18px;">:</div>
                    <div class="timer-segment" style="display: flex; flex-direction: column; align-items: center;">
                        <span class="timer-num" id="seconds-val" style="font-family: var(--font-title); font-size: 2.2rem; font-weight: 800; color: var(--teal); text-shadow: 0 0 10px rgba(110, 220, 196, 0.3);">00</span>
                        <span class="timer-label" style="font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; margin-top: 4px; letter-spacing: 0.05em;">{{ __('Seconds') }}</span>
                    </div>
                </div>

                <div class="countdown-details" style="display: flex; flex-direction: column; gap: 10px;">
                    <div class="detail-row" style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                        <span class="detail-label" style="color: var(--text-secondary); font-size: 0.85rem;">{{ __('Shift Start') }}</span>
                        <span class="detail-value" style="font-weight: 700; direction: ltr;">{{ auth()->user()->work_start_time ? auth()->user()->work_start_time->format('H:i') : '09:00' }}</span>
                    </div>
                    <div class="detail-row" style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                        <span class="detail-label" style="color: var(--text-secondary); font-size: 0.85rem;">{{ __('Shift End') }}</span>
                        <span class="detail-value" style="font-weight: 700; direction: ltr;">{{ auth()->user()->work_end_time ? auth()->user()->work_end_time->format('H:i') : '17:00' }}</span>
                    </div>
                    <div class="detail-row" style="display: flex; justify-content: space-between; padding-bottom: 4px;">
                        <span class="detail-label" style="color: var(--text-secondary); font-size: 0.85rem;">{{ __('Shift Duration') }}</span>
                        <span class="detail-value" id="shift-duration-val" style="font-weight: 700;">-</span>
                    </div>
                </div>
            </div>

            <div class="countdown-progress-container" style="margin-top: 20px;">
                <div class="progress-bar-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 0.8rem; font-weight: 700;">
                    <span id="progress-text" style="color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">{{ __('Shift Progress') }}</span>
                    <span id="progress-pct" style="color: var(--teal);">0%</span>
                </div>
                <div class="progress-bar-bg" style="background-color: var(--border-color); height: 8px; border-radius: 4px; overflow: hidden; position: relative;">
                    <div class="progress-bar-fill" id="progress-fill" style="width: 0%; height: 100%; background: linear-gradient(90deg, var(--color-primary) 0%, var(--teal) 100%); border-radius: 4px; transition: width 0.5s ease-in-out;"></div>
                </div>
            </div>
        </div>
    @endif

    <!-- Monthly Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(26, 171, 139, 0.03) 100%);">
            <div class="stat-icon icon-green" style="background-color: rgba(26, 171, 139, 0.08); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(26, 171, 139, 0.15);">
                <i class="ri-checkbox-circle-fill" style="font-size: 24px; color: var(--green);"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Present (This Month)') }}</h3>
                <p>{{ $stats['present_this_month'] }}</p>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(236, 69, 79, 0.03) 100%);">
            <div class="stat-icon icon-red" style="background-color: rgba(236, 69, 79, 0.08); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(236, 69, 79, 0.15);">
                <i class="ri-close-circle-fill" style="font-size: 24px; color: var(--red);"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Absent (This Month)') }}</h3>
                <p>{{ $stats['absent_this_month'] }}</p>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(255, 199, 60, 0.03) 100%);">
            <div class="stat-icon icon-yellow" style="background-color: rgba(255, 199, 60, 0.08); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255, 199, 60, 0.15);">
                <i class="ri-plane-fill" style="font-size: 24px; color: var(--yellow);"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Vacation (This Month)') }}</h3>
                <p>{{ $stats['vacation_this_month'] }}</p>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(148, 163, 184, 0.03) 100%);">
            <div class="stat-icon icon-primary" style="background-color: rgba(148, 163, 184, 0.08); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--text-secondary); box-shadow: 0 4px 12px rgba(148, 163, 184, 0.15);">
                <i class="ri-file-warning-fill" style="font-size: 24px;"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Excused (This Month)') }}</h3>
                <p>{{ $stats['excused_this_month'] }}</p>
            </div>
        </div>
    </div>

    <div class="two-col-grid" style="margin-bottom: 24px; gap: 24px;">
        <!-- Vacation Quotas -->
        <div class="card" style="margin-bottom: 0;">
            <h3 class="card-title" style="margin: 0 0 16px 0; padding-bottom: 12px; font-weight: 700;">
                <i class="ri-plane-fill" style="color: var(--yellow);"></i>
                <span>{{ __('Vacation Balance') }} ({{ date('Y') }})</span>
            </h3>
            
            @if($stats['vacation_balance'])
                <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Annual Allowance') }}:</span>
                        <strong style="color: var(--text-primary);">{{ $stats['vacation_balance']->total_allowed }} {{ __('days') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Used Vacation Days') }}:</span>
                        <strong style="color: var(--red);">{{ $stats['vacation_balance']->used }} {{ __('days') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-bottom: 4px;">
                        <span style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Remaining Days') }}:</span>
                        <strong style="color: var(--green);">{{ $stats['vacation_balance']->total_allowed - $stats['vacation_balance']->used }} {{ __('days') }}</strong>
                    </div>
                    
                    @php
                        $used_pct = 0;
                        if ($stats['vacation_balance']->total_allowed > 0) {
                            $used_pct = round(($stats['vacation_balance']->used / $stats['vacation_balance']->total_allowed) * 100);
                        }
                    @endphp
                    <div style="margin-top: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 6px;">
                            <span>{{ __('Vacation Usage') }}</span>
                            <span>{{ $used_pct }}%</span>
                        </div>
                        <div style="background-color: var(--border-color); height: 8px; border-radius: 4px; overflow: hidden; position: relative;">
                            <div style="background: linear-gradient(90deg, var(--green) 0%, var(--yellow) 100%); width: {{ $used_pct }}%; height: 100%; border-radius: 4px; transition: width 0.5s ease-in-out;"></div>
                        </div>
                    </div>
                </div>
            @else
                <div style="text-align: center; padding: 30px 0; color: var(--text-secondary);">
                    <i class="ri-plane-line" style="font-size: 2rem; opacity: 0.5; display: block; margin-bottom: 8px;"></i>
                    <span>{{ __('No vacation quota assigned for this year.') }}</span>
                </div>
            @endif
        </div>

        <!-- Shift details -->
        <div class="card" style="margin-bottom: 0;">
            <h3 class="card-title" style="margin: 0 0 16px 0; padding-bottom: 12px; font-weight: 700;">
                <i class="ri-briefcase-fill" style="color: var(--color-primary-light);"></i>
                <span>{{ __('Employment Information') }}</span>
            </h3>
            <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Job Title') }}:</span>
                    <strong style="color: var(--text-primary);">{{ auth()->user()->job_title }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Shift Hours') }}:</span>
                    <strong style="color: var(--text-primary); direction: ltr; display: inline-block;">{{ auth()->user()->work_start_time ? auth()->user()->work_start_time->format('H:i') : '09:00' }} - {{ auth()->user()->work_end_time ? auth()->user()->work_end_time->format('H:i') : '17:00' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 4px;">
                    <span style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Official Email') }}:</span>
                    <strong style="color: var(--color-primary-light);">{{ auth()->user()->email }}</strong>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const startTimeStr = "{{ auth()->user()->work_start_time ? auth()->user()->work_start_time->format('H:i:s') : '09:00:00' }}";
        const endTimeStr = "{{ auth()->user()->work_end_time ? auth()->user()->work_end_time->format('H:i:s') : '17:00:00' }}";

        const hrsLabel = "{{ __('Hrs') }}";
        const minsLabel = "{{ __('Min') }}";

        // Calculate and display duration
        function updateDuration() {
            const durationEl = document.getElementById('shift-duration-val');
            if (!durationEl) return;
            
            const [startH, startM] = startTimeStr.split(':').map(Number);
            const [endH, endM] = endTimeStr.split(':').map(Number);
            
            let diffMins = (endH * 60 + endM) - (startH * 60 + startM);
            if (diffMins < 0) {
                diffMins += 24 * 60; // spans midnight
            }
            
            const hrs = Math.floor(diffMins / 60);
            const mins = diffMins % 60;
            
            if (mins > 0) {
                durationEl.textContent = `${hrs} ${hrsLabel} ${mins} ${minsLabel}`;
            } else {
                durationEl.textContent = `${hrs} ${hrsLabel}`;
            }
        }
        
        updateDuration();

        function updateCountdown() {
            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth();
            const day = now.getDate();

            const [startH, startM, startS] = startTimeStr.split(':').map(Number);
            const [endH, endM, endS] = endTimeStr.split(':').map(Number);

            let startDate = new Date(year, month, day, startH, startM, startS || 0);
            let endDate = new Date(year, month, day, endH, endM, endS || 0);

            const spansMidnight = endDate < startDate;

            if (spansMidnight) {
                if (now >= startDate) {
                    endDate.setDate(endDate.getDate() + 1);
                } else if (now <= endDate) {
                    startDate.setDate(startDate.getDate() - 1);
                } else {
                    endDate.setDate(endDate.getDate() + 1);
                }
            }

            const badge = document.getElementById('shift-status-badge');
            const badgeText = document.getElementById('shift-status-text');
            const progressFill = document.getElementById('progress-fill');
            const progressPct = document.getElementById('progress-pct');
            const progressText = document.getElementById('progress-text');
            
            const hrsVal = document.getElementById('hours-val');
            const minsVal = document.getElementById('minutes-val');
            const secondsVal = document.getElementById('seconds-val');

            if (!badge || !badgeText || !progressFill || !progressPct || !progressText) return;

            let diffMs;
            let percentage = 0;

            if (now < startDate) {
                // Before Shift
                diffMs = startDate - now;
                
                badge.className = 'badge badge-before';
                badgeText.textContent = "{{ __('Shift Not Started') }}";
                progressText.textContent = "{{ __('Starts In') }}";
                percentage = 0;
            } else if (now >= startDate && now <= endDate) {
                // During Shift
                diffMs = endDate - now;
                
                badge.className = 'badge badge-active';
                badgeText.textContent = "{{ __('Shift In Progress') }}";
                progressText.textContent = "{{ __('Time Remaining') }}";
                
                const totalDuration = endDate - startDate;
                const elapsed = now - startDate;
                percentage = Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
            } else {
                // After Shift
                diffMs = 0;
                badge.className = 'badge badge-ended';
                badgeText.textContent = "{{ __('Shift Ended') }}";
                progressText.textContent = "{{ __('Shift Ended') }}";
                percentage = 100;
            }

            // Format time difference
            const totalSeconds = Math.floor(diffMs / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            hrsVal.textContent = String(hours).padStart(2, '0');
            minsVal.textContent = String(minutes).padStart(2, '0');
            secondsVal.textContent = String(seconds).padStart(2, '0');

            // Update progress bar
            progressFill.style.width = `${percentage}%`;
            progressPct.textContent = `${Math.round(percentage)}%`;
        }

        if (document.getElementById('countdown-timer')) {
            updateCountdown();
            setInterval(updateCountdown, 1000);
        }
    });
</script>
@endsection
