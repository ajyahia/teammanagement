@extends('layouts.app')

@section('title', __('Dashboard'))
@section('page_header', __('Dashboard'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <!-- Command Center Welcome Card -->
    <div class="card welcome-card" style="background: linear-gradient(135deg, rgba(50, 138, 241, 0.08) 0%, rgba(99, 123, 255, 0.08) 100%); border-color: rgba(50, 138, 241, 0.2); padding: 30px; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: center; margin-bottom: 24px;">
        <!-- Welcome Info -->
        <div>
            <span style="background: rgba(50, 138, 241, 0.15); color: var(--color-primary-light); padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                <i class="ri-shield-user-line"></i> {{ __('Admin Portal') }}
            </span>
            <h2 style="font-family: var(--font-title); font-size: 1.8rem; margin: 0 0 8px 0; font-weight: 800; background: linear-gradient(135deg, var(--text-primary) 0%, rgba(248, 250, 252, 0.8) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                {{ __('Welcome back') }}, {{ auth()->user()->name }}!
            </h2>
            <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; margin-bottom: 16px;">
                {{ __("Here is a quick overview of today's attendance logs and employee counts.") }}
            </p>
            <div style="font-size: 0.85rem; color: var(--text-secondary); display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ri-calendar-line" style="color: var(--color-primary-light); font-size: 16px;"></i> 
                    <span id="current-date-span">{{ date('Y-m-d') }}</span>
                </span>
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ri-time-line" style="color: var(--color-primary-light); font-size: 16px;"></i> 
                    <span id="current-time-span">--:--:--</span>
                </span>
            </div>
        </div>
        
        <!-- Radial Gauge -->
        <div style="display: flex; justify-content: center; align-items: center; border-inline-start: 1px solid var(--border-color); padding-inline-start: 30px;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <div style="position: relative; width: 120px; height: 120px;">
                    <svg style="transform: rotate(-90deg); width: 120px; height: 120px;">
                        <circle cx="60" cy="60" r="50" stroke="var(--border-color)" stroke-width="8" fill="transparent" />
                        <circle cx="60" cy="60" r="50" stroke="url(#attendanceGrad)" stroke-width="8" fill="transparent" 
                                stroke-dasharray="314" stroke-dashoffset="{{ 314 - (314 * $attendance_rate) / 100 }}" 
                                stroke-linecap="round" style="transition: stroke-dashoffset 1s ease-in-out;" />
                        <defs>
                            <linearGradient id="attendanceGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="var(--color-primary)" />
                                <stop offset="100%" stop-color="var(--teal)" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <span style="font-size: 1.6rem; font-weight: 800; font-family: var(--font-title); color: var(--text-primary); display: block; line-height: 1;">{{ $attendance_rate }}%</span>
                        <span style="font-size: 0.6rem; color: var(--text-secondary); display: block; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px;">{{ __('Rate') }}</span>
                    </div>
                </div>
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-align: center; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('Attendance Rate') }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="stats-grid">
        <!-- Card 1: Total Employees -->
        <div class="stat-card" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(50, 138, 241, 0.03) 100%);">
            <div class="stat-icon icon-primary" style="background-color: rgba(50, 138, 241, 0.08); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(50, 138, 241, 0.15);">
                <i class="ri-group-fill" style="font-size: 24px; color: var(--color-primary);"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Total Employees') }}</h3>
                <p>{{ $stats['total_employees'] }}</p>
            </div>
        </div>

        <!-- Card 2: Present Today -->
        <div class="stat-card" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(26, 171, 139, 0.03) 100%);">
            <div class="stat-icon icon-green" style="background-color: rgba(26, 171, 139, 0.08); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(26, 171, 139, 0.15);">
                <i class="ri-checkbox-circle-fill" style="font-size: 24px; color: var(--green);"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Present Today') }}</h3>
                <p>{{ $stats['present_today'] }}</p>
            </div>
        </div>

        <!-- Card 3: Absent Today -->
        <div class="stat-card" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(236, 69, 79, 0.03) 100%);">
            <div class="stat-icon icon-red" style="background-color: rgba(236, 69, 79, 0.08); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(236, 69, 79, 0.15);">
                <i class="ri-close-circle-fill" style="font-size: 24px; color: var(--red);"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Absent Today') }}</h3>
                <p>{{ $stats['absent_today'] }}</p>
            </div>
        </div>

        <!-- Card 4: On Vacation -->
        <div class="stat-card" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(255, 199, 60, 0.03) 100%);">
            <div class="stat-icon icon-yellow" style="background-color: rgba(255, 199, 60, 0.08); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255, 199, 60, 0.15);">
                <i class="ri-plane-fill" style="font-size: 24px; color: var(--yellow);"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('On Vacation') }}</h3>
                <p>{{ $stats['vacation_today'] }}</p>
            </div>
        </div>

        <!-- Card 5: Excused Today -->
        <div class="stat-card" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(139, 96, 237, 0.03) 100%);">
            <div class="stat-icon icon-purple" style="background-color: rgba(139, 96, 237, 0.08); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(139, 96, 237, 0.15);">
                <i class="ri-file-warning-fill" style="font-size: 24px; color: var(--purple);"></i>
            </div>
            <div class="stat-info">
                <h3>{{ __('Excused Today') }}</h3>
                <p>{{ $stats['excused_today'] }}</p>
            </div>
        </div>
    </div>

    <!-- Live Activity & Actions -->
    <div class="two-col-grid" style="margin-bottom: 24px;">
        <!-- Left: Activity Feed -->
        <div class="card" style="margin-bottom: 0;">
            <h3 class="card-title" style="margin: 0 0 16px 0; padding-bottom: 12px; font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                <i class="ri-pulse-line" style="color: var(--color-primary);"></i>
                <span>{{ __('Recent Attendance Updates') }}</span>
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
                @forelse($recent_activities as $activity)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(255, 255, 255, 0.01); border: 1px solid var(--border-color); border-radius: var(--radius-md); transition: var(--transition);" onmouseover="this.style.borderColor='rgba(50, 138, 241, 0.3)'; this.style.background='rgba(255, 255, 255, 0.03)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='rgba(255, 255, 255, 0.01)';">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem; box-shadow: none;">
                                {{ strtoupper(substr($activity->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <strong style="font-size: 0.9rem; display: block; color: var(--text-primary);">{{ $activity->user->name }}</strong>
                                <span style="font-size: 0.75rem; color: var(--text-secondary);">
                                    {{ $activity->date->format('Y-m-d') }}
                                </span>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            @if($activity->status === 'present')
                                <span class="badge badge-present">{{ __('Present') }}</span>
                            @elseif($activity->status === 'absent')
                                <span class="badge badge-absent">{{ __('Absent') }}</span>
                            @elseif($activity->status === 'vacation')
                                <span class="badge badge-vacation">{{ __('Vacation') }}</span>
                            @else
                                <span class="badge badge-excused">{{ __('Excused') }}</span>
                            @endif
                            
                            <span style="font-size: 0.75rem; color: var(--text-secondary);" title="{{ __('Last Updated') }}">
                                {{ $activity->updated_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                        <i class="ri-inbox-line" style="font-size: 2rem; opacity: 0.5; display: block; margin-bottom: 8px;"></i>
                        <span>{{ __('No recent activity recorded today.') }}</span>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Actions Control Center -->
        <div class="card" style="margin-bottom: 0;">
            <h3 class="card-title" style="margin: 0 0 16px 0; padding-bottom: 12px; font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                <i class="ri-rocket-line" style="color: var(--color-primary);"></i>
                <span>{{ __('Quick Actions') }}</span>
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
                <a href="/admin/employees/create" style="display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: rgba(50, 138, 241, 0.05); border: 1px solid rgba(50, 138, 241, 0.1); border-radius: var(--radius-md); text-decoration: none; color: var(--text-primary); transition: var(--transition);" onmouseover="this.style.background='rgba(50, 138, 241, 0.1)'; this.style.borderColor='var(--color-primary)'" onmouseout="this.style.background='rgba(50, 138, 241, 0.05)'; this.style.borderColor='rgba(50, 138, 241, 0.1)'">
                    <div style="background: rgba(50, 138, 241, 0.15); width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                        <i class="ri-user-add-line" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <strong style="font-size: 0.875rem; display: block;">{{ __('Add New Employee') }}</strong>
                        <span style="font-size: 0.75rem; color: var(--text-secondary);">{{ __('Register a new team member') }}</span>
                    </div>
                </a>

                <a href="/admin/attendance" style="display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: rgba(26, 171, 139, 0.05); border: 1px solid rgba(26, 171, 139, 0.1); border-radius: var(--radius-md); text-decoration: none; color: var(--text-primary); transition: var(--transition);" onmouseover="this.style.background='rgba(26, 171, 139, 0.1)'; this.style.borderColor='var(--green)'" onmouseout="this.style.background='rgba(26, 171, 139, 0.05)'; this.style.borderColor='rgba(26, 171, 139, 0.1)'">
                    <div style="background: rgba(26, 171, 139, 0.15); width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--green);">
                        <i class="ri-calendar-check-line" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <strong style="font-size: 0.875rem; display: block;">{{ __('Mark Attendance') }}</strong>
                        <span style="font-size: 0.75rem; color: var(--text-secondary);">{{ __('Track today\'s presence') }}</span>
                    </div>
                </a>

                <a href="/admin/attendance/months" style="display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: rgba(99, 123, 255, 0.05); border: 1px solid rgba(99, 123, 255, 0.1); border-radius: var(--radius-md); text-decoration: none; color: var(--text-primary); transition: var(--transition);" onmouseover="this.style.background='rgba(99, 123, 255, 0.1)'; this.style.borderColor='var(--indigo)'" onmouseout="this.style.background='rgba(99, 123, 255, 0.05)'; this.style.borderColor='rgba(99, 123, 255, 0.1)'">
                    <div style="background: rgba(99, 123, 255, 0.15); width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--indigo);">
                        <i class="ri-file-chart-2-line" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <strong style="font-size: 0.875rem; display: block;">{{ __('View Monthly Summary') }}</strong>
                        <span style="font-size: 0.75rem; color: var(--text-secondary);">{{ __('Analyze monthly metrics') }}</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Live Clock
        function updateClock() {
            const timeSpan = document.getElementById('current-time-span');
            if (!timeSpan) return;
            const now = new Date();
            timeSpan.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        }
        updateClock();
        setInterval(updateClock, 1000);
    });
</script>
@endsection
