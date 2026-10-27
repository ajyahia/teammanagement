@extends('layouts.app')

@section('title', __('Manage Expenses'))
@section('page_header', __('Expenses'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('General Expenses') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">{{ __('Manage all external company expenses') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ri-add-line"></i>
                    <span>{{ __('Add Expense') }}</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: rgba(46, 204, 113, 0.1); color: #27ae60; border: 1px solid rgba(46, 204, 113, 0.2);">
            {{ session('success') }}
        </div>
    @endif

    {{-- Monthly / yearly summary --}}
    <div class="card" style="margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.expenses.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 18px;">
            <div class="form-group" style="margin-bottom: 0; min-width: 120px;">
                <label class="form-label">{{ __('Year') }}</label>
                <select name="year" class="form-control" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 160px;">
                <label class="form-label">{{ __('Month') }}</label>
                <select name="month" class="form-control" onchange="this.form.submit()">
                    <option value="">{{ __('All year') }}</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $m }} - {{ \Carbon\Carbon::create($year, $m, 1)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-inline-start: auto;">
                @if($month)
                    <div style="padding: 12px 18px; border-radius: var(--radius-md); background: rgba(59,130,246,0.1);">
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ __('Spent in') }} {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}</div>
                        <div style="font-size: 1.3rem; font-weight: 700;">{{ number_format($monthTotal, 2) }}</div>
                    </div>
                @endif
                <div style="padding: 12px 18px; border-radius: var(--radius-md); background: var(--bg-hover);">
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ __('Spent in year') }} {{ $year }}</div>
                    <div style="font-size: 1.3rem; font-weight: 700;">{{ number_format($yearTotal, 2) }}</div>
                </div>
            </div>
        </form>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px;">
            @foreach($monthlyTotals as $m => $total)
                <a href="{{ route('admin.expenses.index', ['year' => $year, 'month' => $m]) }}"
                   style="display: block; padding: 10px 12px; border-radius: var(--radius-md); text-decoration: none; color: inherit; border: 1px solid {{ $m == $month ? 'var(--color-primary)' : 'var(--border-color)' }}; background: {{ $m == $month ? 'rgba(59,130,246,0.08)' : 'transparent' }};">
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ \Carbon\Carbon::create($year, $m, 1)->translatedFormat('F') }}</div>
                    <div style="font-weight: 700; {{ $total == 0 ? 'color: var(--text-secondary);' : '' }}">{{ number_format($total, 2) }}</div>
                </a>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Notes') }}</th>
                        <th style="text-align: center;">{{ __('Actions') }}</th>
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
                                    <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn-icon btn-secondary" title="{{ __('Edit') }}">
                                        <i class="ri-edit-2-line"></i>
                                    </a>
                                    <form id="delete-expense-form-{{ $expense->id }}" action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-icon" title="{{ __('Delete') }}" onclick="showGlobalConfirmPopup('delete-expense-form-{{ $expense->id }}', '{{ __('Are you sure you want to delete this expense?') }}')">
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
                                <p style="color: var(--text-secondary); margin: 0;">{{ __('No expenses found.') }}</p>
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
