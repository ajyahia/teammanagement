@extends('layouts.app')

@section('title', __('Add Expense'))
@section('page_header', __('Add Expense'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card">
        <h4 style="margin-top:0;">{{ __('Add New Expense') }}</h4>
        <form action="{{ route('admin.expenses.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>{{ __('Description') }}</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>{{ __('Amount') }}</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>{{ __('Date') }}</label>
                <input type="date" name="expense_date" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>{{ __('Notes (Optional)') }}</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">{{ __('Save Expense') }}</button>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
