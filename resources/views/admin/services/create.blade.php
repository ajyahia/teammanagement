@extends('layouts.app')

@section('title', __('Add Service'))
@section('page_header', __('Add Service'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 24px; font-weight: 600;">{{ __('Service Details') }}</h3>

        <form method="POST" action="{{ route('admin.services.store') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">{{ __('Service Name') }} <span style="color: red;">*</span></label>
                <input type="text" name="name" class="form-control" required placeholder="{{ __('e.g. Website Development, Logo Design') }}" value="{{ old('name') }}">
                @error('name')<span style="color: var(--red); font-size: 0.85rem; margin-top: 4px; display: block;">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Default Price') }} <span style="color: red;">*</span></label>
                <input type="number" step="0.01" name="default_price" class="form-control" required value="{{ old('default_price', '0.00') }}">
                @error('default_price')<span style="color: var(--red); font-size: 0.85rem; margin-top: 4px; display: block;">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Description') }}</label>
                <textarea name="description" class="form-control" rows="4" placeholder="{{ __('Brief description about what this service includes...') }}">{{ old('description') }}</textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line"></i> {{ __('Save Service') }}
                </button>
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection
