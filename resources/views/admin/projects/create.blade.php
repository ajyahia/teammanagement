@extends('layouts.app')

@section('title', __('Create Project'))
@section('page_header', __('Create Project'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="max-width: 1100px; margin: 0 auto;">
        <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 16px; font-weight: 600;">{{ __('Project Details') }}</h3>

        <form method="POST" action="{{ route('admin.projects.store') }}">
            @csrf
            @include('admin.projects._form', ['project' => null])
        </form>
    </div>
@endsection
