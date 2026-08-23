@extends('layouts.app')

@section('title', __('Edit Client'))
@section('page_header', __('Edit Client'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 24px; font-weight: 600;">{{ __('Edit Client Information') }}</h3>

        <form method="POST" action="{{ route('admin.clients.update', $client->id) }}">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">{{ __('Name') }} <span style="color: red;">*</span></label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $client->name) }}">
                @error('name')<span style="color: var(--red); font-size: 0.85rem; margin-top: 4px; display: block;">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Company') }}</label>
                <input type="text" name="company" class="form-control" value="{{ old('company', $client->company) }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Phone') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $client->phone) }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $client->email) }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Notes / Requirements') }}</label>
                <textarea name="notes" class="form-control" rows="4">{{ old('notes', $client->notes) }}</textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line"></i> {{ __('Update Client') }}
                </button>
                <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection
