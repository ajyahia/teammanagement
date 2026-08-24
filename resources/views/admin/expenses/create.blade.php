@extends('layouts.app')

@section('title', 'إضافة مصروف')
@section('page_header', 'إضافة مصروف')

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card">
        <h4 style="margin-top:0;">إضافة مصروف جديد</h4>
        <form action="{{ route('admin.expenses.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>اسم المصروف (البيان)</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>المبلغ</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>التاريخ</label>
                <input type="date" name="expense_date" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>ملاحظات (اختياري)</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">حفظ المصروف</button>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
