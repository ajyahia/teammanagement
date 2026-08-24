@extends('layouts.app')

@section('title', __('Services Management'))
@section('page_header', __('Services Management'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('All Services') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">{{ __('Manage services you offer to clients') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.services.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ri-add-line"></i>
                    <span>{{ __('Add Service') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Service Name') }}</th>
                        <th>{{ __('Default Price') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th style="text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td><strong>{{ $service->name }}</strong></td>
                            <td><span class="badge badge-present">{{ number_format($service->default_price, 2) }}</span></td>
                            <td>{{ Str::limit($service->description, 50) ?: '-' }}</td>
                            <td>
                                <div style="display: flex; justify-content: center; gap: 8px;">
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px;">
                                <i class="ri-customer-service-2-line" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
                                <p style="color: var(--text-secondary); margin: 0;">{{ __('No services found. Add your first service!') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($services->hasPages())
            <div style="margin-top: 20px;">
                {{ $services->links() }}
            </div>
        @endif
    </div>
@endsection
