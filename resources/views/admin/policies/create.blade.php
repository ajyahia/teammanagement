@extends('layouts.app')

@section('title', __('Create Policy'))
@section('page_header', __('Create Policy'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div style="margin-bottom: 24px;">
        <a href="{{ route('admin.policies.index') }}" class="btn btn-secondary">
            <i class="ri-arrow-left-line"></i>
            <span>{{ __('Back to Policies') }}</span>
        </a>
    </div>

    <div class="card">
        <h3 class="card-title">
            <i class="ri-file-add-fill"></i>
            <span>{{ __('New Company Policy') }}</span>
        </h3>

        <form action="{{ route('admin.policies.store') }}" method="POST">
            @csrf
            
            <div class="form-grid">
                <!-- Title English -->
                <div class="form-group">
                    <label for="title">{{ __('Title (English)') }} <span style="color: var(--red);">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="e.g. Working Hours & Attendance" required>
                    @error('title')
                        <span style="color: var(--red); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Title Arabic -->
                <div class="form-group">
                    <label for="title_ar">{{ __('Title (Arabic)') }} <span style="color: var(--red);">*</span></label>
                    <input type="text" id="title_ar" name="title_ar" value="{{ old('title_ar') }}" placeholder="مثال: مواعيد العمل والحضور" required>
                    @error('title_ar')
                        <span style="color: var(--red); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Icon -->
                <div class="form-group">
                    <label for="icon">{{ __('Icon (Remixicon)') }}</label>
                    <select id="icon" name="icon">
                        <option value="ri-file-text-line" selected>📄 {{ __('Default Document') }} (ri-file-text-line)</option>
                        <option value="ri-time-line">⏰ {{ __('Time / Shift') }} (ri-time-line)</option>
                        <option value="ri-plane-line">✈️ {{ __('Vacation / Travel') }} (ri-plane-line)</option>
                        <option value="ri-money-dollar-circle-line">💰 {{ __('Salaries') }} (ri-money-dollar-circle-line)</option>
                        <option value="ri-shield-line">🛡️ {{ __('Conduct / Rules') }} (ri-shield-line)</option>
                        <option value="ri-team-line">👥 {{ __('Team / Employee') }} (ri-team-line)</option>
                        <option value="ri-award-line">🏆 {{ __('Awards / Bonuses') }} (ri-award-line)</option>
                        <option value="ri-heart-line">❤️ {{ __('Health / Benefits') }} (ri-heart-line)</option>
                    </select>
                    @error('icon')
                        <span style="color: var(--red); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Sort Order -->
                <div class="form-group">
                    <label for="sort_order">{{ __('Sort Order') }}</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" placeholder="e.g. 1" min="0">
                    @error('sort_order')
                        <span style="color: var(--red); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Content English -->
                <div class="form-group full-width">
                    <label for="content">{{ __('Content (English)') }} <span style="color: var(--red);">*</span></label>
                    <textarea id="content" name="content" rows="6" placeholder="Enter policy details in English..." required>{{ old('content') }}</textarea>
                    @error('content')
                        <span style="color: var(--red); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Content Arabic -->
                <div class="form-group full-width">
                    <label for="content_ar">{{ __('Content (Arabic)') }} <span style="color: var(--red);">*</span></label>
                    <textarea id="content_ar" name="content_ar" rows="6" placeholder="اكتب تفاصيل السياسة باللغة العربية..." required>{{ old('content_ar') }}</textarea>
                    @error('content_ar')
                        <span style="color: var(--red); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.policies.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
@endsection
