@extends('layouts.app')

@section('title', __('My Projects'))
@section('page_header', __('My Projects'))

@section('sidebar_menu')
    @include('layouts.sidebar_employee')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="margin: 0 0 10px; font-family: var(--font-title); font-weight: 700;">{{ __('Assigned Tasks & Projects') }}</h3>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">{{ __('View and update the status of projects assigned to you.') }}</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @forelse($projects as $project)
            <div class="card" style="position: relative; padding: 24px;">
                @php
                    $statusColor = match($project->status) {
                        'completed' => 'badge-present',
                        'in_progress' => 'badge-vacation',
                        'cancelled' => 'badge-absent',
                        default => 'badge-excused'
                    };
                @endphp
                
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                    <div>
                        <h4 style="margin: 0 0 5px; font-family: var(--font-title); font-size: 1.1rem; color: var(--color-primary-light);">
                            <i class="ri-briefcase-4-fill" style="margin-right: 5px;"></i> {{ $project->service->name }}
                        </h4>
                        <span style="font-size: 0.85rem; color: var(--text-secondary);">{{ __('Client') }}: <strong>{{ $project->client->name }}</strong></span>
                    </div>
                    <span class="badge {{ $statusColor }}">{{ __(ucfirst(str_replace('_', ' ', $project->status))) }}</span>
                </div>

                <div style="margin-bottom: 20px; font-size: 0.9rem;">
                    <div style="margin-bottom: 8px;">
                        <i class="ri-calendar-event-line" style="color: var(--text-secondary); width: 20px; display: inline-block;"></i> 
                        {{ __('Start') }}: <span style="color: var(--text-primary);">{{ $project->start_date ? $project->start_date->format('Y-m-d') : '-' }}</span>
                    </div>
                    <div>
                        <i class="ri-timer-line" style="color: var(--text-secondary); width: 20px; display: inline-block;"></i> 
                        {{ __('Deadline') }}: <span style="color: var(--text-primary);">{{ $project->deadline ? $project->deadline->format('Y-m-d') : '-' }}</span>
                    </div>
                </div>

                @if($project->notes)
                    <div style="background: rgba(255,255,255,0.02); padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.85rem; border-left: 3px solid var(--border-color);">
                        <strong style="display: block; margin-bottom: 5px; color: var(--text-secondary);">{{ __('Notes & Requirements:') }}</strong>
                        <p style="margin: 0; line-height: 1.5; color: var(--text-primary);">{{ $project->notes }}</p>
                    </div>
                @endif

                @if($project->status !== 'cancelled')
                    <hr style="border-color: var(--border-color); margin: 20px 0;">
                    
                    <form action="{{ route('employee.projects.update_status', $project->id) }}" method="POST" style="display: flex; gap: 10px;">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-control" style="padding: 8px; font-size: 0.85rem;">
                            <option value="pending" {{ $project->status == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                            <option value="in_progress" {{ $project->status == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                            <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.85rem; white-space: nowrap;">
                            {{ __('Update') }}
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <i class="ri-cup-line" style="font-size: 4rem; color: var(--text-secondary); display: block; margin-bottom: 15px;"></i>
                <h4 style="margin: 0 0 10px; color: var(--text-primary);">{{ __('No Assigned Projects') }}</h4>
                <p style="color: var(--text-secondary); margin: 0;">{{ __('You currently have no active projects or tasks assigned to you. Enjoy your free time!') }}</p>
            </div>
        @endforelse
    </div>
@endsection
