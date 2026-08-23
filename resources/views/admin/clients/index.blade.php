@extends('layouts.app')

@section('title', __('Clients Management'))
@section('page_header', __('Clients Management'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('All Clients') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">{{ __('Manage your company clients') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.clients.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ri-add-line"></i>
                    <span>{{ __('Add Client') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Company') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th style="text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td><strong>{{ $client->name }}</strong></td>
                            <td>{{ $client->company ?: '-' }}</td>
                            <td>{{ $client->phone ?: '-' }}</td>
                            <td>{{ $client->email ?: '-' }}</td>
                            <td>
                                <div style="display: flex; justify-content: center; gap: 8px;">
                                    <a href="{{ route('admin.clients.show', $client->id) }}" class="btn-icon btn-primary" title="{{ __('View Profile & Finances') }}">
                                        <i class="ri-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('admin.clients.edit', $client->id) }}" class="btn-icon btn-secondary" title="{{ __('Edit Client') }}">
                                        <i class="ri-edit-2-line"></i>
                                    </a>
                                    <form id="delete-client-form-{{ $client->id }}" action="{{ route('admin.clients.destroy', $client->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-icon" title="{{ __('Delete Client') }}" onclick="showGlobalConfirmPopup('delete-client-form-{{ $client->id }}', '{{ __('Are you sure you want to delete this client?') }}')">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">
                                <i class="ri-folder-user-line" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
                                <p style="color: var(--text-secondary); margin: 0;">{{ __('No clients found. Add your first client!') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
