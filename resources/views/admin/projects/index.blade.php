@extends('layouts.app')

@section('title', __('Projects & Orders'))
@section('page_header', __('Projects & Orders'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('All Projects') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">{{ __('Track services, assign employees, and monitor profits') }}</p>
            </div>
            <div>
                <form action="{{ route('admin.projects.cycles.generate_all') }}" method="POST" style="display: inline-block; margin-inline-end: 10px;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;" title="{{ __('Generate billing cycles for all active subscriptions for the current month') }}">
                        <i class="ri-refresh-line"></i>
                        <span>{{ __('Generate Monthly Bills') }}</span>
                    </button>
                </form>
                <a href="{{ route('admin.projects.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ri-add-line"></i>
                    <span>{{ __('Create Project') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div class="card" style="padding: 20px; border-left: 4px solid var(--color-primary);">
            <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.9rem;">{{ __('Total Revenue') }}</p>
            <h3 style="margin: 0; color: var(--text-primary); font-family: var(--font-title);">{{ number_format($projects->sum('total_revenue'), 2) }}</h3>
        </div>
        <div class="card" style="padding: 20px; border-left: 4px solid var(--green);">
            <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.9rem;">{{ __('Total Paid') }}</p>
            <h3 style="margin: 0; color: var(--green); font-family: var(--font-title);">{{ number_format($projects->sum('total_paid'), 2) }}</h3>
        </div>
        <div class="card" style="padding: 20px; border-left: 4px solid var(--orange);">
            <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.9rem;">{{ __('Total Due') }}</p>
            <h3 style="margin: 0; color: var(--orange); font-family: var(--font-title);">{{ number_format($projects->sum('due_amount'), 2) }}</h3>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Service') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Assigned To') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Total Billed') }}</th>
                        <th>{{ __('Paid') }}</th>
                        <th>{{ __('Due') }}</th>
                        <th style="text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>
                                <strong>{{ $project->service->name }}</strong>
                                @if($project->billing_type === 'monthly')
                                    <span class="badge badge-vacation" style="font-size: 0.65rem; padding: 2px 5px; margin-inline-start: 5px;">{{ __('Monthly Sub') }}</span>
                                @elseif($project->billing_type === 'yearly')
                                    <span class="badge badge-vacation" style="font-size: 0.65rem; padding: 2px 5px; margin-inline-start: 5px;">{{ __('Annual Subscription') }}</span>
                                @elseif($project->billing_type === 'per_page')
                                    <span class="badge badge-excused" style="font-size: 0.65rem; padding: 2px 5px; margin-inline-start: 5px;">{{ __('Per Page (Books)') }}</span>
                                @else
                                    <span class="badge badge-secondary" style="font-size: 0.65rem; padding: 2px 5px; margin-inline-start: 5px;">{{ __('One Time Payment') }}</span>
                                @endif
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">#{{ $project->id }}</div>
                            </td>
                            <td>{{ $project->client->name }}</td>
                            <td>
                                @if($project->employees->count() > 0)
                                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                        @foreach($project->employees as $emp)
                                            <span class="badge badge-present" style="font-size: 0.75rem; padding: 2px 6px;">{{ $emp->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="badge badge-absent">{{ __('Unassigned') }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColor = match($project->status) {
                                        'completed' => 'badge-present',
                                        'in_progress' => 'badge-vacation',
                                        'cancelled' => 'badge-absent',
                                        default => 'badge-excused'
                                    };
                                @endphp
                                <span class="badge {{ $statusColor }}">{{ __(ucfirst(str_replace('_', ' ', $project->status))) }}</span>
                            </td>
                            <td>
                                {{ number_format($project->total_revenue, 2) }}
                                @if(in_array($project->billing_type, ['monthly', 'yearly']))
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                        {{ __('Rate:') }} {{ number_format($project->agreed_price, 2) }} 
                                        ({{ $project->cycles->count() }} {{ __('Months') }})
                                    </div>
                                @endif
                            </td>
                            <td style="color: var(--green); font-weight: bold;">{{ number_format($project->total_paid, 2) }}</td>
                            <td style="color: var(--orange); font-weight: bold;">{{ number_format($project->due_amount, 2) }}</td>
                            <td>
                                <div style="display: flex; justify-content: center; gap: 8px;">
                                    <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn-icon btn-secondary" title="{{ __('Edit Project') }}">
                                        <i class="ri-edit-2-line"></i>
                                    </a>
                                    <form id="delete-project-form-{{ $project->id }}" action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-icon" title="{{ __('Delete Project') }}" onclick="showGlobalConfirmPopup('delete-project-form-{{ $project->id }}', '{{ __('Are you sure you want to delete this project?') }}')">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px;">
                                <i class="ri-briefcase-4-line" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
                                <p style="color: var(--text-secondary); margin: 0;">{{ __('No projects found. Create one!') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
            <div style="margin-top: 20px;">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
@endsection
