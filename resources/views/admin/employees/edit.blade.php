@extends('layouts.app')

@section('title', __('Edit Employee'))
@section('page_header', __('Edit Employee'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card">
        <h3 class="card-title">
            <i class="ri-edit-box-line"></i>
            <span>{{ __('Modify Employee Profile') }}</span>
        </h3>
        
        <form method="POST" action="/admin/employees/{{ $employee->id }}">
            @csrf
            @method('PUT')
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">{{ __('Full Name') }} *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $employee->name) }}" required>
                    @error('name') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>



                <div class="form-group">
                    <label for="job_title">{{ __('Job Title') }} *</label>
                    <input type="text" name="job_title" id="job_title" value="{{ old('job_title', $employee->job_title) }}" required>
                    @error('job_title') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>





                <div class="form-group">
                    <label for="username">{{ __('Username') }} *</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $employee->username) }}" required>
                    @error('username') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="password">{{ __('Password') }} *</label>
                    <input type="text" name="password" id="password" value="{{ old('password', $employee->password_text) }}" required>
                    @error('password') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="role">{{ __('Role') }} *</label>
                    <select name="role" id="role" required>
                        <option value="employee" {{ old('role', $employee->role) === 'employee' ? 'selected' : '' }}>{{ __('Employee') }}</option>
                        <option value="admin" {{ old('role', $employee->role) === 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                    </select>
                    @error('role') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="services">{{ __('Services') }}</label>
                    <select name="services[]" id="services" class="form-control select2-multiple" multiple>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ (in_array($service->id, old('services', $employee->services->pluck('id')->toArray()))) ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                    <small style="color: var(--text-secondary); font-size: 0.8rem;">{{ __('Hold Ctrl (Windows) or Command (Mac) to select multiple.') }}</small>
                    @error('services') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="payment_type">{{ __('Payment Type') }} *</label>
                    <select name="payment_type" id="payment_type" required onchange="togglePaymentFields()">
                        <option value="monthly_salary" {{ old('payment_type', $employee->payment_type) === 'monthly_salary' ? 'selected' : '' }}>{{ __('Monthly Salary') }}</option>
                        <option value="per_project" {{ old('payment_type', $employee->payment_type) === 'per_project' ? 'selected' : '' }}>{{ __('Per Project') }}</option>
                    </select>
                    @error('payment_type') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group" id="salary_group">
                    <label for="salary">{{ __('Basic Salary') }} *</label>
                    <input type="number" step="0.01" name="salary" id="salary" placeholder="{{ __('e.g. 5000') }}" value="{{ old('salary', $employee->salary) }}">
                    @error('salary') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group" id="project_rate_group" style="display: none;">
                    <label for="project_rate">{{ __('Rate Per Project') }} *</label>
                    <input type="number" step="0.01" name="project_rate" id="project_rate" placeholder="{{ __('e.g. 4000') }}" value="{{ old('project_rate', $employee->project_rate) }}">
                    @error('project_rate') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>


            </div>
            
            <div class="form-actions">
                <a href="/admin/employees" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Update Profile') }}</button>
            </div>
        </form>
    </div>
    <script>
        function togglePaymentFields() {
            const paymentType = document.getElementById('payment_type').value;
            const salaryGroup = document.getElementById('salary_group');
            const projectRateGroup = document.getElementById('project_rate_group');
            const salaryInput = document.getElementById('salary');
            const projectRateInput = document.getElementById('project_rate');
            
            if (paymentType === 'monthly_salary') {
                salaryGroup.style.display = 'block';
                salaryInput.required = true;
                
                projectRateGroup.style.display = 'none';
                projectRateInput.required = false;
            } else {
                salaryGroup.style.display = 'none';
                salaryInput.required = false;
                
                projectRateGroup.style.display = 'block';
                projectRateInput.required = true;
            }
        }
        
        document.addEventListener('DOMContentLoaded', togglePaymentFields);
    </script>
@endsection
