@extends('layouts.app')

@section('title', __('Add Client'))
@section('page_header', __('Add Client'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 24px; font-weight: 600;">{{ __('Client Information') }}</h3>

        <form method="POST" action="{{ route('admin.clients.store') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">{{ __('Name') }} <span style="color: red;">*</span></label>
                <input type="text" name="name" class="form-control" required placeholder="{{ __('e.g. John Doe') }}" value="{{ old('name') }}">
                @error('name')<span style="color: var(--red); font-size: 0.85rem; margin-top: 4px; display: block;">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Company') }}</label>
                <input type="text" name="company" class="form-control" placeholder="{{ __('e.g. Acme Corp') }}" value="{{ old('company') }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Phone') }}</label>
                <input type="text" name="phone" class="form-control" placeholder="{{ __('e.g. +1234567890') }}" value="{{ old('phone') }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" class="form-control" placeholder="{{ __('e.g. john@example.com') }}" value="{{ old('email') }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Notes / Requirements') }}</label>
                <textarea name="notes" class="form-control" rows="4" placeholder="{{ __('Any specific requirements for this client...') }}">{{ old('notes') }}</textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line"></i> {{ __('Save Client') }}
                </button>
                <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection
