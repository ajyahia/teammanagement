@extends('layouts.app')

@section('title', __('Project Details'))
@section('page_header', __('Project Details'))

@section('sidebar_menu')
    @include('layouts.sidebar_client')
@endsection

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('client.projects.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; font-size: 0.9rem;">
            <i class="ri-arrow-left-line"></i> {{ __('Back to Projects') }}
        </a>
    </div>

    <!-- Project Details -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; margin-bottom: 20px;">
            <div>
                <h2 style="margin: 0 0 10px; font-family: var(--font-title); color: var(--color-primary-light);">
                    <i class="ri-briefcase-4-fill" style="margin-right: 5px;"></i> {{ $project->service->name }}
                </h2>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <span class="badge {{ $project->billing_type === 'monthly' ? 'badge-vacation' : 'badge-secondary' }}">
                        {{ $project->billing_type === 'monthly' ? __('Monthly Subscription') : __('One Time Payment') }}
                    </span>
                    
                    @php
                        $statusClass = match($project->status) {
                            'completed' => 'badge-present',
                            'in_progress' => 'badge-vacation',
                            'cancelled' => 'badge-absent',
                            default => 'badge-excused'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ __(ucfirst(str_replace('_', ' ', $project->status))) }}</span>
                    
                    @if($project->billing_type === 'monthly')
                        <span class="badge {{ $project->subscription_status === 'active' ? 'badge-present' : 'badge-absent' }}">
                            {{ __('Sub:') }} {{ __($project->subscription_status === 'active' ? 'Active' : 'Stopped') }}
                        </span>
                    @endif
                </div>
            </div>
            
            <div style="text-align: right; background: rgba(255,255,255,0.02); padding: 15px 25px; border-radius: 10px; border: 1px solid var(--border-color);">
                <span style="display: block; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 5px;">{{ __('Agreed Price') }}</span>
                <span style="font-size: 1.5rem; font-weight: bold; font-family: var(--font-title); color: var(--text-main);">
                    {{ number_format($project->agreed_price, 2) }}
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 0.95rem;">
            <div>
                <p style="margin: 0 0 10px;"><strong style="color: var(--text-secondary); display: inline-block; width: 100px;">{{ __('Start Date:') }}</strong> {{ $project->start_date ? $project->start_date->format('Y-m-d') : '-' }}</p>
                <p style="margin: 0 0 10px;"><strong style="color: var(--text-secondary); display: inline-block; width: 100px;">{{ __('Deadline:') }}</strong> {{ $project->deadline ? $project->deadline->format('Y-m-d') : '-' }}</p>
            </div>
            @if($project->notes)
                <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px;">
                    <strong style="color: var(--text-secondary); display: block; margin-bottom: 5px;">{{ __('Notes:') }}</strong>
                    <p style="margin: 0; line-height: 1.6;">{{ $project->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Payments / Installments -->
    <h3 style="margin: 0 0 15px; font-family: var(--font-title); font-weight: 700;">{{ __('Payments & Installments') }}</h3>
    
    <div class="card">
        @if($project->payments->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Title / Description') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Due Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Paid At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->payments as $payment)
                            <tr>
                                <td>{{ $payment->title }}</td>
                                <td><strong>{{ number_format($payment->amount, 2) }}</strong></td>
                                <td>{{ $payment->due_date->format('Y-m-d') }}</td>
                                <td>
                                    @if($payment->status === 'paid')
                                        <span class="badge badge-present"><i class="ri-check-line"></i> {{ __('Paid') }}</span>
                                    @elseif($payment->status === 'overdue')
                                        <span class="badge badge-absent"><i class="ri-error-warning-line"></i> {{ __('Overdue') }}</span>
                                    @else
                                        <span class="badge badge-excused"><i class="ri-time-line"></i> {{ __('Pending') }}</span>
                                    @endif
                                </td>
                                <td>{{ $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 40px 20px;">
                <i class="ri-file-list-3-line" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 15px; display: block;"></i>
                <p style="color: var(--text-secondary); margin: 0;">{{ __('No payments or installments have been generated for this project yet.') }}</p>
            </div>
        @endif
    </div>
@endsection
