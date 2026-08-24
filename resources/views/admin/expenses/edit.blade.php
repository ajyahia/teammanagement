@extends('layouts.app')

@section('title', 'تعديل مصروف')
@section('page_header', 'تعديل مصروف')

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card">
        <h4 style="margin-top:0;">{{ __('Edit Expense') }}</h4>
        <form action="{{ route('admin.expenses.update', $expense->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>{{ __('Description') }}</label>
                <input type="text" name="title" class="form-control" value="{{ $expense->title }}" required>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>{{ __('Amount') }}</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="{{ $expense->amount }}" required>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>{{ __('Date') }}</label>
                <input type="date" name="expense_date" class="form-control" value="{{ $expense->expense_date->format('Y-m-d') }}" required>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>{{ __('Notes (Optional)') }}</label>
                <textarea name="notes" class="form-control" rows="3">{{ $expense->notes }}</textarea>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">{{ __('Update Expense') }}</button>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
