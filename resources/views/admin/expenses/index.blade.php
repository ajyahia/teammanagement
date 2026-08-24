@extends('layouts.app')

@section('title', 'إدارة المصروفات')
@section('page_header', 'المصروفات')

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">المصروفات العامة</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">إدارة جميع المصروفات الخارجية للشركة</p>
            </div>
            <div>
                <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ri-add-line"></i>
                    <span>إضافة مصروف</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: rgba(46, 204, 113, 0.1); color: #27ae60; border: 1px solid rgba(46, 204, 113, 0.2);">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>البيان</th>
                        <th>المبلغ</th>
                        <th>تاريخ الدفع</th>
                        <th>ملاحظات</th>
                        <th style="text-align: center;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td><strong>{{ $expense->title }}</strong></td>
                            <td>{{ number_format($expense->amount, 2) }}</td>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td>{{ $expense->notes ?: '-' }}</td>
                            <td>
                                <div style="display: flex; justify-content: center; gap: 8px;">
                                    <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn-icon btn-secondary" title="تعديل">
                                        <i class="ri-edit-2-line"></i>
                                    </a>
                                    <form id="delete-expense-form-{{ $expense->id }}" action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-icon" title="حذف" onclick="showGlobalConfirmPopup('delete-expense-form-{{ $expense->id }}', 'هل أنت متأكد من حذف هذا المصروف؟')">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">
                                <i class="ri-money-dollar-circle-line" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
                                <p style="color: var(--text-secondary); margin: 0;">لا يوجد أي مصروفات مسجلة.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div style="margin-top: 20px;">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
@endsection
