@extends('layouts.app')

@section('title', __('Services Management'))
@section('page_header', __('Services Management'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <style>
        .svc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 18px; margin-bottom: 20px; }
        .svc-card { margin-bottom: 0; display: flex; flex-direction: column; gap: 12px; }
        .svc-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
        .svc-name { margin: 0; font-family: var(--font-title); font-weight: 700; font-size: 1.1rem; }
        .svc-price { font-weight: 800; font-size: 1.05rem; white-space: nowrap; color: var(--color-primary-light); font-variant-numeric: tabular-nums; }
        .svc-desc { color: var(--text-secondary); font-size: 0.85rem; line-height: 1.7; margin: 0; min-height: 2.9em; }
        .svc-label { font-size: 0.72rem; color: var(--text-secondary); margin-bottom: 4px; }
        .svc-chip { display: inline-block; padding: 3px 10px; margin: 0 4px 4px 0; border-radius: 999px; font-size: 0.75rem; background: rgba(59,130,246,0.12); color: var(--color-primary-light); }
        .svc-stats { display: flex; gap: 18px; font-size: 0.85rem; color: var(--text-secondary); padding-top: 12px; border-top: 1px solid var(--border-color); margin-top: auto; }
        .svc-stats strong { color: var(--text-primary); }
        .svc-actions { display: flex; gap: 8px; margin-inline-start: auto; }
    </style>

    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('All Services') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 4px 0 0;">{{ __('Manage services you offer to clients') }}</p>
            </div>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="ri-add-line"></i>
                <span>{{ __('Add Service') }}</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: rgba(46, 204, 113, 0.1); color: #27ae60; border: 1px solid rgba(46, 204, 113, 0.2);">
            {{ session('success') }}
        </div>
    @endif

    @if($services->count())
        <div class="svc-grid">
            @foreach($services as $service)
                <div class="card svc-card">
                    <div class="svc-top">
                        <h4 class="svc-name">{{ $service->name }}</h4>
                        <span class="svc-price">{{ number_format($service->default_price, 2) }}</span>
                    </div>

                    <p class="svc-desc">{{ Str::limit($service->description, 90) ?: '—' }}</p>

                    @if($service->components->count())
                        <div>
                            <div class="svc-label">{{ __('Made of') }}</div>
                            @foreach($service->components as $c)
                                <span class="svc-chip">{{ $c->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="svc-stats">
                        <span><i class="ri-briefcase-4-line"></i> <strong>{{ $service->projects_count }}</strong> {{ __('Projects') }}</span>
                        <span><i class="ri-team-line"></i> <strong>{{ $service->users_count }}</strong> {{ __('Employees') }}</span>
                        <div class="svc-actions">
                            <a href="{{ route('admin.services.edit', $service->id) }}" class="btn-icon btn-secondary" title="{{ __('Edit Service') }}">
                                <i class="ri-edit-2-line"></i>
                            </a>
                            <form id="delete-service-form-{{ $service->id }}" action="{{ route('admin.services.destroy', $service->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-icon" title="{{ __('Delete Service') }}" onclick="showGlobalConfirmPopup('delete-service-form-{{ $service->id }}', '{{ __('Are you sure you want to delete this service?') }}')">
                                    <i class="ri-delete-bin-fill"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card" style="text-align: center; padding: 40px;">
            <i class="ri-customer-service-2-line" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
            <p style="color: var(--text-secondary); margin: 0;">{{ __('No services found. Add your first service!') }}</p>
        </div>
    @endif

    @if($services->hasPages())
        {{ $services->links() }}
    @endif
@endsection
