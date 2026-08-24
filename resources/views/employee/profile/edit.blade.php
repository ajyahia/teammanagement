@extends('layouts.app')

@section('title', __('My Profile'))
@section('page_header', __('My Profile'))

@section('sidebar_menu')
    @include('layouts.sidebar_employee')
@endsection

@section('content')
    <div class="card">
        <h3 class="card-title">
            <i class="ri-user-shared-line"></i>
            <span>{{ __('Edit Profile Details') }}</span>
        </h3>
        
        <form method="POST" action="/employee/profile">
            @csrf
            @method('PUT')
            
            <h3 style="margin-top: 0; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">{{ __('Personal Details') }}</h3>
            <div class="form-grid" style="margin-top: 15px;">
                <div class="form-group">
                    <label for="name">{{ __('Full Name') }} *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $employee->name) }}" required>
                    @error('name') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="age">{{ __('Age') }} *</label>
                    <input type="number" name="age" id="age" value="{{ old('age', $employee->age) }}" required>
                    @error('age') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
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
            </div>

            <h3 style="margin-top: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">{{ __('Contact & Security Details') }}</h3>
            <div class="form-grid" style="margin-top: 15px;">
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
                    <label for="password">{{ __('Password') }} *</label>
                    <input type="text" name="password" id="password" value="{{ old('password', $employee->password_text) }}" required>
                    @error('password') <div class="error-message"><i class="ri-error-warning-line"></i> {{ $message }}</div> @enderror
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
            
            <div class="form-actions" style="margin-top: 25px;">
                <button type="submit" class="btn btn-primary">{{ __('Save Profile Changes') }}</button>
            </div>
        </form>
    </div>
@endsection
