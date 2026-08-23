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
                <label class="form-label">{{ __('Assign To Employee') }}</label>
                <select name="employee_id" class="form-control">
                    <option value="">-- {{ __('Unassigned') }} --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id', $project->employee_id) == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Agreed Price (Client Pays)') }} <span style="color: red;">*</span></label>
                    <input type="number" step="0.01" name="agreed_price" class="form-control" required value="{{ old('agreed_price', $project->agreed_price) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('Estimated Cost (Expenses)') }} <span style="color: red;">*</span></label>
                    <input type="number" step="0.01" name="cost" class="form-control" required value="{{ old('cost', $project->cost) }}">
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
        });
    </script>
@endsection
