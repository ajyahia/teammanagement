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
                    <label for="age">{{ __('Age') }}</label>
                    <input type="number" name="age" id="age" value="{{ old('age', $employee->age) }}">
                    @error('age') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="job_title">{{ __('Job Title') }} *</label>
                    <input type="text" name="job_title" id="job_title" value="{{ old('job_title', $employee->job_title) }}" required>
                    @error('job_title') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="phone">{{ __('Phone Number') }} *</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $employee->phone) }}" required>
                    @error('phone') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="whatsapp">{{ __('WhatsApp Number') }}</label>
                    <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $employee->whatsapp) }}">
                    @error('whatsapp') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="email">{{ __('Email Address') }} *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $employee->email) }}" required>
                    @error('email') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
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
                    <label for="work_start_time">{{ __('Shift Start Time') }} (HH:MM) *</label>
                    <input type="text" name="work_start_time" id="work_start_time" placeholder="{{ __('e.g. 09:00') }}" value="{{ old('work_start_time', $employee->work_start_time ? $employee->work_start_time->format('H:i') : '09:00') }}" required>
                    @error('work_start_time') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="work_end_time">{{ __('Shift End Time') }} (HH:MM) *</label>
                    <input type="text" name="work_end_time" id="work_end_time" placeholder="{{ __('e.g. 17:00') }}" value="{{ old('work_end_time', $employee->work_end_time ? $employee->work_end_time->format('H:i') : '17:00') }}" required>
                    @error('work_end_time') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="salary">{{ __('Basic Salary') }} *</label>
                    <input type="number" step="0.01" name="salary" id="salary" placeholder="{{ __('e.g. 5000') }}" value="{{ old('salary', $employee->salary) }}" required>
                    @error('salary') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="linkedin">{{ __('LinkedIn Profile URL') }}</label>
                    <input type="url" name="linkedin" id="linkedin" value="{{ old('linkedin', $employee->linkedin) }}">
                    @error('linkedin') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="facebook">{{ __('Facebook Profile URL') }}</label>
                    <input type="url" name="facebook" id="facebook" value="{{ old('facebook', $employee->facebook) }}">
                    @error('facebook') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="instagram">{{ __('Instagram Profile URL') }}</label>
                    <input type="url" name="instagram" id="instagram" value="{{ old('instagram', $employee->instagram) }}">
                    @error('instagram') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>
            </div>
            
            <div class="form-actions">
                <a href="/admin/employees" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Update Profile') }}</button>
            </div>
        </form>
    </div>
@endsection
