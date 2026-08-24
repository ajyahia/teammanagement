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
                        <th>{{ __('Job Title') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Services') }}</th>
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
                            <td>{{ $emp->job_title }}</td>
                            <td>
                                <span class="badge {{ $emp->role === 'admin' ? 'badge-present' : 'badge-excused' }}">
                                    {{ __($emp->role === 'admin' ? 'Admin' : 'Employee') }}
                                </span>
                            </td>
                            <td>
                                @if($emp->services->count() > 0)
                                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                        @foreach($emp->services as $service)
                                            <span class="badge badge-vacation" style="font-size: 0.75rem; padding: 2px 6px;">{{ $service->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="color: var(--text-secondary); font-size: 0.8rem;">{{ __('None') }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="/admin/employees/{{ $emp->id }}/edit" class="btn btn-secondary btn-icon" title="{{ __('Edit Profile') }}">
                                        <i class="ri-pencil-fill"></i>
                                    </a>
                                    <form action="/admin/employees/{{ $emp->id }}" method="POST" id="delete-form-{{ $emp->id }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-icon" onclick="showConfirmPopup('{{ $emp->id }}')" title="{{ __('Delete Employee') }}">
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
        @if($employees->hasPages())
            <div style="margin-top: 20px;">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

    <!-- Custom Confirm Modal -->
    <div id="confirmModal" class="custom-modal-overlay" style="display: none;">
        <div class="custom-modal">
            <div class="modal-icon">
                <i class="ri-alert-line"></i>
            </div>
            <h4 class="modal-title">{{ __('Are you sure?') }}</h4>
            <p class="modal-text">{{ __('Are you sure you want to delete this employee? This action cannot be undone.') }}</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmPopup()">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    .custom-modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        animation: fadeIn 0.3s forwards;
    }
    .custom-modal {
        background: var(--bg-card, #1e1e2d);
        border-radius: 12px;
        padding: 24px;
        width: 90%;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        transform: scale(0.9);
        animation: scaleUp 0.3s forwards;
        border: 1px solid var(--border-color, #2b2b40);
    }
    .modal-icon {
        width: 60px;
        height: 60px;
        background: rgba(241, 65, 108, 0.1);
        color: #f1416c;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        margin: 0 auto 16px;
    }
    .modal-title {
        color: var(--text-main, #fff);
        margin: 0 0 10px;
        font-family: var(--font-title, sans-serif);
        font-weight: 600;
        font-size: 1.25rem;
    }
    .modal-text {
        color: var(--text-secondary, #92929f);
        margin: 0 0 24px;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    .modal-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
    }
    @keyframes fadeIn {
        to { opacity: 1; }
    }
    @keyframes scaleUp {
        to { transform: scale(1); }
    }
</style>
@endsection

@section('scripts')
<script>
    let formToSubmitId = null;

    function showConfirmPopup(empId) {
        formToSubmitId = 'delete-form-' + empId;
        const modal = document.getElementById('confirmModal');
        modal.style.display = 'flex';
    }

    function closeConfirmPopup() {
        const modal = document.getElementById('confirmModal');
        modal.style.display = 'none';
        formToSubmitId = null;
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (formToSubmitId) {
            document.getElementById(formToSubmitId).submit();
        }
    });
</script>
@endsection
