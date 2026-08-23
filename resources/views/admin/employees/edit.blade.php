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
                    <label for="salary">{{ __('Basic Salary') }} *</label>
                    <input type="number" step="0.01" name="salary" id="salary" placeholder="{{ __('e.g. 5000') }}" value="{{ old('salary', $employee->salary) }}" required>
                    @error('salary') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>


            </div>
            
            <div class="form-actions">
                <a href="/admin/employees" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Update Profile') }}</button>
            </div>
        </form>
    </div>
@endsection
