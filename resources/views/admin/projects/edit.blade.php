@extends('layouts.app')

@section('title', __('Edit Project'))
@section('page_header', __('Edit Project'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 24px; font-weight: 600;">{{ __('Edit Project') }} #{{ $project->id }}</h3>

        <form method="POST" action="{{ route('admin.projects.update', $project->id) }}">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Select Client') }} <span style="color: red;">*</span></label>
                    <select name="client_id" class="form-control" required>
                        <option value="">-- {{ __('Choose Client') }} --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', $project->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('Select Service') }} <span style="color: red;">*</span></label>
                    <select name="service_id" class="form-control" required>
                        <option value="">-- {{ __('Choose Service') }} --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id', $project->service_id) == $service->id ? 'selected' : '' }}>{{ $service->name }} ({{ number_format($service->default_price, 2) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Assign To Employees') }}</label>
                <select name="employee_ids[]" id="employee_ids" class="form-control select2-multiple" multiple>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" data-services="{{ json_encode($emp->services->pluck('id')) }}" {{ (is_array(old('employee_ids', $project->employees->pluck('id')->toArray())) && in_array($emp->id, old('employee_ids', $project->employees->pluck('id')->toArray()))) ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
                <small style="color: var(--text-secondary); display: block; margin-top: 4px;">{{ __('Hold Ctrl (Windows) or Command (Mac) to select multiple. Only employees matching the selected service are shown.') }}</small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Agreed Price (Client Pays)') }} <span style="color: red;">*</span></label>
                    <input type="number" step="0.01" name="agreed_price" class="form-control" required value="{{ old('agreed_price', $project->agreed_price) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('Amount Paid (By Client)') }} <span style="color: red;">*</span></label>
                    <input type="number" step="0.1" name="paid_amount" class="form-control" required value="{{ old('paid_amount', $project->paid_amount) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Status') }} <span style="color: red;">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="pending" {{ old('status', $project->status) == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="in_progress" {{ old('status', $project->status) == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                    <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    <option value="cancelled" {{ old('status', $project->status) == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Billing Type') }} <span style="color: red;">*</span></label>
                    <select name="billing_type" class="form-control" required id="billing_type_select">
                        <option value="one_time" {{ old('billing_type', $project->billing_type) == 'one_time' ? 'selected' : '' }}>{{ __('One Time Payment (Installments)') }}</option>
                        <option value="monthly" {{ old('billing_type', $project->billing_type) == 'monthly' ? 'selected' : '' }}>{{ __('Monthly Subscription') }}</option>
                    </select>
                </div>
                
                <div class="form-group" id="subscription_status_group" style="display: none;">
                    <label class="form-label">{{ __('Subscription Status') }}</label>
                    <select name="subscription_status" class="form-control">
                        <option value="active" {{ old('subscription_status', $project->subscription_status) == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="stopped" {{ old('subscription_status', $project->subscription_status) == 'stopped' ? 'selected' : '' }}>{{ __('Stopped') }}</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Start Date') }}</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('Deadline') }}</label>
                    <input type="date" name="deadline" class="form-control" value="{{ old('deadline', $project->deadline ? $project->deadline->format('Y-m-d') : '') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Project Notes / Requirements') }}</label>
                <textarea name="notes" class="form-control" rows="4">{{ old('notes', $project->notes) }}</textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line"></i> {{ __('Update Project') }}
                </button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>

    @if($project->billing_type === 'monthly')
    <div class="card" style="max-width: 800px; margin: 24px auto 0;">
        <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 24px; font-weight: 600;">
            <i class="ri-calendar-event-line" style="color: var(--color-primary); margin-right: 8px;"></i>
            {{ __('Monthly Billing Cycles') }}
        </h3>

        <form action="{{ route('admin.projects.cycles.store', $project->id) }}" method="POST" style="display: flex; gap: 15px; margin-bottom: 24px; align-items: flex-end;">
            @csrf
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label class="form-label">{{ __('Billing Date') }} <span style="color: red;">*</span></label>
                <input type="date" name="billing_date" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label class="form-label">{{ __('Amount') }} <span style="color: red;">*</span></label>
                <input type="number" step="0.01" name="amount" class="form-control" required value="{{ $project->agreed_price }}">
            </div>
            <button type="submit" class="btn btn-primary" style="height: 48px;">
                <i class="ri-add-line"></i> {{ __('Add Cycle') }}
            </button>
        </form>

        @if($project->cycles->count() > 0)
            <div class="table-responsive" style="border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                <table class="data-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th style="padding: 12px 16px;">{{ __('Date') }}</th>
                            <th style="padding: 12px 16px;">{{ __('Amount') }}</th>
                            <th style="padding: 12px 16px;">{{ __('Status') }}</th>
                            <th style="padding: 12px 16px; text-align: center; width: 100px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->cycles()->orderBy('billing_date', 'desc')->get() as $cycle)
                            <tr>
                                <td style="padding: 12px 16px; font-size: 0.95rem;">
                                    {{ $cycle->billing_date->format('M Y') }}
                                    <span style="display:block; font-size: 0.8rem; color: var(--text-secondary);">{{ $cycle->billing_date->format('Y-m-d') }}</span>
                                </td>
                                <td style="padding: 12px 16px; font-weight: 600; color: var(--color-primary-light);">
                                    {{ number_format($cycle->amount, 2) }}
                                </td>
                                <td style="padding: 12px 16px;">
                                    @if($cycle->is_paid)
                                        <span class="badge" style="background: rgba(34,197,94,0.1); color: #22c55e;">
                                            <i class="ri-check-line"></i> {{ __('Paid on') }} {{ $cycle->paid_at->format('Y-m-d') }}
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                                            <i class="ri-close-line"></i> {{ __('Unpaid') }}
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px; text-align: center; display: flex; gap: 8px; justify-content: center;">
                                    <form action="{{ route('admin.projects.cycles.toggle_paid', $cycle->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-icon {{ $cycle->is_paid ? 'btn-secondary' : 'btn-success' }}" title="{{ $cycle->is_paid ? __('Mark as Unpaid') : __('Mark as Paid') }}">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('admin.projects.cycles.destroy', $cycle->id) }}" method="POST" style="margin:0;" onsubmit="return confirm('{{ __('Are you sure you want to delete this cycle?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-icon" title="{{ __('Delete Cycle') }}">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 30px; color: var(--text-secondary); background: var(--bg-hover); border-radius: var(--radius-md);">
                {{ __('No billing cycles added yet.') }}
            </div>
        @endif
    </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const billingSelect = document.getElementById('billing_type_select');
            const subGroup = document.getElementById('subscription_status_group');
            
            function toggleSubscriptionStatus() {
                if(billingSelect.value === 'monthly') {
                    subGroup.style.display = 'block';
                } else {
                    subGroup.style.display = 'none';
                }
            }
            
            billingSelect.addEventListener('change', toggleSubscriptionStatus);
            toggleSubscriptionStatus();

            // Employee Service Filtering
            const serviceSelect = document.querySelector('select[name="service_id"]');
            const employeeSelect = document.getElementById('employee_ids');
            
            function filterEmployees() {
                const serviceId = parseInt(serviceSelect.value);
                
                Array.from(employeeSelect.options).forEach(option => {
                    if (!serviceId) {
                        option.disabled = false;
                        return;
                    }
                    
                    const services = JSON.parse(option.getAttribute('data-services') || '[]');
                    if (services.includes(serviceId)) {
                        option.disabled = false;
                    } else {
                        option.disabled = true;
                        // Don't deselect automatically in edit mode to avoid losing data if service isn't changed manually
                    }
                });
                // Re-initialize Select2 to apply the disabled states
                $(employeeSelect).select2({
                    placeholder: "{{ __('Select options...') }}",
                    allowClear: true,
                    width: '100%'
                });
            }
            
            if(serviceSelect) {
                serviceSelect.addEventListener('change', function() {
                    Array.from(employeeSelect.options).forEach(opt => {
                        if(opt.disabled) opt.selected = false;
                    });
                    filterEmployees();
                });
                filterEmployees();
            }
        });
    </script>
@endsection
