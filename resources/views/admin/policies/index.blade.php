@extends('layouts.app')

@section('title', __('Company Policies'))
@section('page_header', __('Company Policies'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div style="display: flex; align-items: center; justify-content: flex-end; margin-bottom: 24px;">
        <a href="{{ route('admin.policies.create') }}" class="btn btn-primary">
            <i class="ri-add-line"></i>
            <span>{{ __('Create Policy') }}</span>
        </a>
    </div>

    <div class="card">
        <h3 class="card-title">
            <i class="ri-file-text-fill"></i>
            <span>{{ __('Manage Policies') }}</span>
        </h3>

        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">#</th>
                        <th style="width: 50px; text-align: center;">{{ __('Icon') }}</th>
                        <th>{{ __('Title (English)') }}</th>
                        <th>{{ __('Title (Arabic)') }}</th>
                        <th style="width: 100px; text-align: center;">{{ __('Order') }}</th>
                        <th style="width: 150px; text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                        <tr>
                            <td style="text-align: center;">{{ $policy->id }}</td>
                            <td style="text-align: center;">
                                <div style="font-size: 1.4rem; color: var(--color-primary-light);">
                                    <i class="{{ $policy->icon }}"></i>
                                </div>
                            </td>
                            <td><strong>{{ $policy->title }}</strong></td>
                            <td><strong>{{ $policy->title_ar }}</strong></td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--border-color);">
                                    {{ $policy->sort_order }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <a href="{{ route('admin.policies.edit', $policy->id) }}" class="btn-icon btn-secondary" title="{{ __('Edit') }}">
                                        <i class="ri-edit-2-line"></i>
                                    </a>
                                    <form action="{{ route('admin.policies.destroy', $policy->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this policy?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon btn-danger" title="{{ __('Delete') }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                {{ __('No policies registered yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
