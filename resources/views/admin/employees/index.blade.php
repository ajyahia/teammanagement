@extends('layouts.app')

@section('title', __('Manage Employees'))
@section('page_header', __('Manage Employees'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Employee Directory') }}</h3>
            <a href="/admin/employees/create" class="btn btn-primary">
                <i class="ri-user-add-line"></i>
                <span>{{ __('Add Employee') }}</span>
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Username') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Job Title') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Work Shift') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    <strong>{{ $emp->name }}</strong>
                                </div>
                            </td>
                            <td><code style="color: var(--color-primary-light);">{{ $emp->username }}</code></td>
                            <td>{{ $emp->email }}</td>
                            <td>{{ $emp->job_title }}</td>
                            <td>
                                <span class="badge {{ $emp->role === 'admin' ? 'badge-present' : 'badge-excused' }}">
                                    {{ __($emp->role === 'admin' ? 'Admin' : 'Employee') }}
                                </span>
                            </td>
                            <td>
                                <i class="ri-time-line" style="vertical-align: middle; color: var(--text-secondary);"></i>
                                <span style="vertical-align: middle;">
                                    {{ $emp->work_start_time ? $emp->work_start_time->format('H:i') : '09:00' }} - {{ $emp->work_end_time ? $emp->work_end_time->format('H:i') : '17:00' }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="/admin/employees/{{ $emp->id }}/edit" class="btn btn-secondary btn-icon" title="{{ __('Edit Profile') }}">
                                        <i class="ri-pencil-fill"></i>
                                    </a>
                                    <form action="/admin/employees/{{ $emp->id }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('Are you sure you want to delete this employee?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-icon" title="{{ __('Delete Employee') }}">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 40px 20px;">
                                <i class="ri-user-unfollow-line" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                                {{ __('No employees registered in the system yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
