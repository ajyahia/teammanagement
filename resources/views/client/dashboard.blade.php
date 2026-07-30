@extends('layouts.app')

@section('title', __('Client Dashboard'))
@section('page_header', __('Client Dashboard'))

@section('sidebar_menu')
    @include('layouts.sidebar_client')
@endsection

@section('content')
    <div style="margin-bottom: 24px;">
        <h2 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Welcome Back,') }} <span style="color: var(--color-primary);">{{ $client->name }}</span>!</h2>
        <p style="color: var(--text-secondary); margin-top: 5px;">{{ __('Here is a summary of your active projects and financial status.') }}</p>
    </div>

    <!-- Summary Widgets -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="card" style="padding: 24px; border-top: 4px solid var(--color-primary);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.95rem;">{{ __('Active Projects') }}</p>
                    <h3 style="margin: 0; color: var(--text-primary); font-family: var(--font-title); font-size: 1.8rem;">{{ $activeProjectsCount }}</h3>
                </div>
                <div style="width: 50px; height: 50px; background: rgba(0, 158, 253, 0.1); color: var(--color-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="ri-briefcase-4-line"></i>
                </div>
            </div>
        </div>
        
        <div class="card" style="padding: 24px; border-top: 4px solid var(--green);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.95rem;">{{ __('Total Paid') }}</p>
                    <h3 style="margin: 0; color: var(--green); font-family: var(--font-title); font-size: 1.8rem;">{{ number_format($totalPaid, 2) }}</h3>
                </div>
                <div style="width: 50px; height: 50px; background: rgba(80, 205, 137, 0.1); color: var(--green); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="ri-checkbox-circle-line"></i>
                </div>
            </div>
        </div>
        
        <div class="card" style="padding: 24px; border-top: 4px solid {{ $overduePayments > 0 ? 'var(--red)' : 'var(--orange)' }};">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.95rem;">{{ $overduePayments > 0 ? __('Overdue Payments') : __('Pending Payments') }}</p>
                    <h3 style="margin: 0; color: {{ $overduePayments > 0 ? 'var(--red)' : 'var(--orange)' }}; font-family: var(--font-title); font-size: 1.8rem;">
                        {{ number_format($overduePayments > 0 ? $overduePayments : $pendingPayments, 2) }}
                    </h3>
                </div>
                <div style="width: 50px; height: 50px; background: {{ $overduePayments > 0 ? 'rgba(241, 65, 108, 0.1)' : 'rgba(255, 152, 0, 0.1)' }}; color: {{ $overduePayments > 0 ? 'var(--red)' : 'var(--orange)' }}; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="ri-error-warning-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Projects -->
    <h3 style="margin: 0 0 15px; font-family: var(--font-title); font-weight: 700;">{{ __('Recent Projects') }}</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @forelse($recentProjects as $project)
            <div class="card" style="padding: 24px; position: relative;">
                @if($project->billing_type === 'monthly')
                    <span class="badge badge-vacation" style="position: absolute; top: 20px; right: 20px;">{{ __('Monthly Sub') }}</span>
                @endif
                
                <h4 style="margin: 0 0 10px; font-family: var(--font-title); color: var(--color-primary-light); padding-right: 80px;">
                    <i class="ri-briefcase-4-fill" style="margin-right: 5px;"></i> {{ $project->service->name }}
                </h4>
                
                <div style="margin-bottom: 15px; font-size: 0.9rem;">
                    <div style="margin-bottom: 8px;">
                        <span style="color: var(--text-secondary);">{{ __('Status') }}:</span>
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
                        <div>
                            <span style="color: var(--text-secondary);">{{ __('Subscription') }}:</span>
                            @if($project->subscription_status === 'active')
                                <strong style="color: var(--green);">{{ __('Active') }}</strong>
                            @else
                                <strong style="color: var(--red);">{{ __('Stopped') }}</strong>
                            @endif
                        </div>
                    @endif
                </div>
                
                <a href="{{ route('client.projects.show', $project->id) }}" class="btn btn-secondary" style="width: 100%; text-align: center;">
                    {{ __('View Details') }} <i class="ri-arrow-right-line" style="vertical-align: middle;"></i>
                </a>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <i class="ri-briefcase-4-line" style="font-size: 4rem; color: var(--text-secondary); display: block; margin-bottom: 15px;"></i>
                <h4 style="margin: 0 0 10px; color: var(--text-primary);">{{ __('No Projects Found') }}</h4>
                <p style="color: var(--text-secondary); margin: 0;">{{ __('You currently do not have any active projects.') }}</p>
            </div>
        @endforelse
    </div>
@endsection
