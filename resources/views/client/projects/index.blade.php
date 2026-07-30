@extends('layouts.app')

@section('title', __('My Projects'))
@section('page_header', __('My Projects'))

@section('sidebar_menu')
    @include('layouts.sidebar_client')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="margin: 0 0 5px; font-family: var(--font-title); font-weight: 700;">{{ __('All Projects') }}</h3>
        <p style="color: var(--text-secondary); margin: 0;">{{ __('View and manage all your projects and services.') }}</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @forelse($projects as $project)
            <div class="card" style="padding: 24px; position: relative;">
                @if($project->billing_type === 'monthly')
                    <span class="badge badge-vacation" style="position: absolute; top: 20px; right: 20px;">{{ __('Monthly Sub') }}</span>
                @endif
                
                <h4 style="margin: 0 0 10px; font-family: var(--font-title); color: var(--color-primary-light); padding-right: 80px;">
                    <i class="ri-briefcase-4-fill" style="margin-right: 5px;"></i> {{ $project->service->name }}
                </h4>
                
                <div style="margin-bottom: 20px; font-size: 0.9rem;">
                    <div style="margin-bottom: 8px;">
                        <span style="color: var(--text-secondary); display: inline-block; width: 80px;">{{ __('Status:') }}</span>
                        @php
                            $statusColor = match($project->status) {
                                'completed' => 'var(--green)',
                                'in_progress' => 'var(--orange)',
                                'cancelled' => 'var(--red)',
                                default => 'var(--text-secondary)'
                            };
                        @endphp
                        <strong style="color: {{ $statusColor }}">{{ __(ucfirst(str_replace('_', ' ', $project->status))) }}</strong>
                    </div>
                    
                    @if($project->billing_type === 'monthly')
                        <div style="margin-bottom: 8px;">
                            <span style="color: var(--text-secondary); display: inline-block; width: 80px;">{{ __('Sub:') }}</span>
                            @if($project->subscription_status === 'active')
                                <strong style="color: var(--green);">{{ __('Active') }}</strong>
                            @else
                                <strong style="color: var(--red);">{{ __('Stopped') }}</strong>
                            @endif
                        </div>
                    @endif
                    
                    <div>
                        <span style="color: var(--text-secondary); display: inline-block; width: 80px;">{{ __('Price:') }}</span>
                        <strong>{{ number_format($project->agreed_price, 2) }}</strong>
                    </div>
                </div>
                
                <a href="{{ route('client.projects.show', $project->id) }}" class="btn btn-secondary" style="width: 100%; text-align: center;">
                    <i class="ri-eye-line" style="vertical-align: middle;"></i> {{ __('View Details & Payments') }}
                </a>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <i class="ri-briefcase-4-line" style="font-size: 4rem; color: var(--text-secondary); display: block; margin-bottom: 15px;"></i>
                <h4 style="margin: 0 0 10px; color: var(--text-primary);">{{ __('No Projects Found') }}</h4>
                <p style="color: var(--text-secondary); margin: 0;">{{ __('You currently do not have any projects.') }}</p>
            </div>
        @endforelse
    </div>
@endsection
