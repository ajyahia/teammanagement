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
    @php $maxMonth = max($monthlyTotals) ?: 1; @endphp
    <style>
        .exp-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 20px; }
        .exp-stat { padding: 16px 20px; border-radius: var(--radius-md); background: var(--bg-hover); border: 1px solid var(--border-color); }
        .exp-stat.is-accent { background: rgba(59,130,246,0.1); border-color: rgba(59,130,246,0.35); }
        .exp-stat .label { font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 6px; }
        .exp-stat .value { font-size: 1.6rem; font-weight: 800; font-family: var(--font-title); line-height: 1.2; }
        .exp-filters { display: flex; gap: 12px; align-items: flex-end; }
        .exp-filters .form-group { margin-bottom: 0; }
        .exp-filters .form-control { min-width: 150px; }
        .exp-months { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; }
        .exp-month { display: block; padding: 12px 14px; border-radius: var(--radius-md); text-decoration: none; color: inherit; border: 1px solid var(--border-color); transition: var(--transition); }
        .exp-month:hover { background: var(--bg-hover); }
        .exp-month.is-active { border-color: var(--color-primary); background: rgba(59,130,246,0.1); }
        .exp-month .name { font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 4px; }
        .exp-month .amount { font-weight: 700; font-size: 1rem; }
        .exp-month .amount.is-zero { color: var(--text-secondary); font-weight: 500; }
        .exp-month .bar { height: 4px; border-radius: 2px; background: var(--border-color); margin-top: 8px; overflow: hidden; }
        .exp-month .bar span { display: block; height: 100%; background: var(--color-primary); border-radius: 2px; }
        @media (max-width: 1100px) { .exp-months { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (max-width: 700px) { .exp-months { grid-template-columns: repeat(2, minmax(0, 1fr)); } .exp-filters { flex-wrap: wrap; } }
    </style>

    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
            <form method="GET" action="{{ route('admin.expenses.index') }}" class="exp-filters">
                <div class="form-group">
                    <label class="form-label">{{ __('Year') }}</label>
                    <select name="year" class="form-control" onchange="this.form.submit()">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Month') }}</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        <option value="">{{ __('All year') }}</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ \Carbon\Carbon::create($year, $m, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
            </form>
        </div>

        <div class="exp-summary">
            <div class="exp-stat">
                <div class="label">{{ __('Spent in year') }} {{ $year }}</div>
                <div class="value">{{ number_format($yearTotal, 2) }}</div>
            </div>
            @if($month)
                <div class="exp-stat is-accent">
                    <div class="label">{{ __('Spent in') }} {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}</div>
                    <div class="value">{{ number_format($monthTotal, 2) }}</div>
                </div>
            @endif
        </div>

        <div class="exp-months">
            @foreach($monthlyTotals as $m => $total)
                <a href="{{ route('admin.expenses.index', ['year' => $year, 'month' => $m]) }}" class="exp-month {{ $m == $month ? 'is-active' : '' }}">
                    <div class="name">{{ \Carbon\Carbon::create($year, $m, 1)->translatedFormat('F') }}</div>
                    <div class="amount {{ $total == 0 ? 'is-zero' : '' }}">{{ number_format($total, 2) }}</div>
                    <div class="bar"><span style="width: {{ round($total / $maxMonth * 100) }}%;"></span></div>
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
