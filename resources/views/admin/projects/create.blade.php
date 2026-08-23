@extends('layouts.app')

@section('title', __('Create Project'))
@section('page_header', __('Create Project'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 24px; font-weight: 600;">{{ __('Project Details') }}</h3>

        <form method="POST" action="{{ route('admin.projects.store') }}">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Select Client') }} <span style="color: red;">*</span></label>
                    <select name="client_id" class="form-control" required>
                        <option value="">-- {{ __('Choose Client') }} --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')<span style="color: var(--red); font-size: 0.85rem;">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('Select Service') }} <span style="color: red;">*</span></label>
                    <select name="service_id" class="form-control" required>
                        <option value="">-- {{ __('Choose Service') }} --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>{{ $service->name }} ({{ number_format($service->default_price, 2) }})</option>
                        @endforeach
                    </select>
                    @error('service_id')<span style="color: var(--red); font-size: 0.85rem;">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Assign To Employees') }}</label>
                <select name="employee_ids[]" id="employee_ids" class="form-control" multiple style="height: 120px;">
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" data-services="{{ json_encode($emp->services->pluck('id')) }}" {{ (is_array(old('employee_ids')) && in_array($emp->id, old('employee_ids'))) ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
                <small style="color: var(--text-secondary); display: block; margin-top: 4px;">{{ __('Hold Ctrl (Windows) or Command (Mac) to select multiple. Only employees matching the selected service are shown.') }}</small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Agreed Price (Client Pays)') }} <span style="color: red;">*</span></label>
                    <input type="number" step="0.01" name="agreed_price" class="form-control" required value="{{ old('agreed_price', '0.00') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('Estimated Cost (Expenses)') }} <span style="color: red;">*</span></label>
                    <input type="number" step="0.01" name="cost" class="form-control" required value="{{ old('cost', '0.00') }}">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">{{ __('Does not include employee base salary.') }}</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Status') }} <span style="color: red;">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Billing Type') }} <span style="color: red;">*</span></label>
                    <select name="billing_type" class="form-control" required id="billing_type_select">
                        <option value="one_time" {{ old('billing_type') == 'one_time' ? 'selected' : '' }}>{{ __('One Time Payment (Installments)') }}</option>
                        <option value="monthly" {{ old('billing_type') == 'monthly' ? 'selected' : '' }}>{{ __('Monthly Subscription') }}</option>
                    </select>
                </div>
                
                <div class="form-group" id="subscription_status_group" style="display: none;">
                    <label class="form-label">{{ __('Subscription Status') }}</label>
                    <select name="subscription_status" class="form-control">
                        <option value="active" {{ old('subscription_status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="stopped" {{ old('subscription_status') == 'stopped' ? 'selected' : '' }}>{{ __('Stopped') }}</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Start Date') }}</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('Deadline') }}</label>
                    <input type="date" name="deadline" class="form-control" value="{{ old('deadline') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Project Notes / Requirements') }}</label>
                <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line"></i> {{ __('Create Project') }}
                </button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>

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
                        option.style.display = 'block';
                        option.disabled = false;
                        return;
                    }
                    
                    const services = JSON.parse(option.getAttribute('data-services') || '[]');
                    if (services.includes(serviceId)) {
                        option.style.display = 'block';
                        option.disabled = false;
                    } else {
                        option.style.display = 'none';
                        option.disabled = true;
                        option.selected = false;
                    }
                });
            }
            
            if(serviceSelect) {
                serviceSelect.addEventListener('change', filterEmployees);
                filterEmployees();
            }
        });
    </script>
@endsection
