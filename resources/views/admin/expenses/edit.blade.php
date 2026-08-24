@extends('layouts.app')

@section('title', 'تعديل مصروف')
@section('page_header', 'تعديل مصروف')

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card">
        <h4 style="margin-top:0;">تعديل بيانات المصروف</h4>
        <form action="{{ route('admin.expenses.update', \->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>اسم المصروف (البيان)</label>
                <input type="text" name="title" class="form-control" value="{{ \->title }}" required>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>المبلغ</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="{{ \->amount }}" required>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>التاريخ</label>
                <input type="date" name="expense_date" class="form-control" value="{{ \->expense_date->format('Y-m-d') }}" required>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>ملاحظات (اختياري)</label>
                <textarea name="notes" class="form-control" rows="3">{{ \->notes }}</textarea>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">تحديث المصروف</button>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
