@extends('layouts.app')

@section('title', __('Treasury Ledger'))
@section('page_header', __('Treasury Ledger'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Treasury (الخزنة)') }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">{{ __('Detailed ledger of all incomes and expenses') }}</p>
            </div>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div class="card" style="padding: 20px; border-left: 4px solid var(--color-primary);">
            <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.9rem;">{{ __('Opening Budget') }}</p>
            <h3 style="margin: 0; color: var(--text-primary); font-family: var(--font-title);">{{ number_format($budget, 0) }}</h3>
        </div>
        <div class="card" style="padding: 20px; border-left: 4px solid var(--green);">
            <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.9rem;">{{ __('Total Incomes') }}</p>
            <h3 style="margin: 0; color: var(--green); font-family: var(--font-title);">+ {{ number_format($totalIn, 0) }}</h3>
        </div>
        <div class="card" style="padding: 20px; border-left: 4px solid var(--red);">
            <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.9rem;">{{ __('Total Expenses') }}</p>
            <h3 style="margin: 0; color: var(--red); font-family: var(--font-title);">- {{ number_format($totalOut, 0) }}</h3>
        </div>
        <div class="card" style="padding: 20px; border-left: 4px solid {{ $currentBalance >= 0 ? 'var(--color-primary)' : 'var(--red)' }};">
            <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.9rem;">{{ __('Current Balance') }}</p>
            <h3 style="margin: 0; color: {{ $currentBalance >= 0 ? 'var(--text-primary)' : 'var(--red)' }}; font-family: var(--font-title);">{{ number_format($currentBalance, 0) }}</h3>
        </div>
    </div>

    <div class="card">
        <h4 style="margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">{{ __('Transaction History') }}</h4>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th style="text-align: right;">{{ __('Incoming (In)') }}</th>
                        <th style="text-align: right;">{{ __('Outgoing (Out)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td style="color: var(--text-secondary); font-size: 0.95rem;">
                                {{ $transaction['date'] instanceof \Carbon\Carbon ? $transaction['date']->format('Y-m-d H:i') : $transaction['date'] }}
                            </td>
                            <td>
                                @php
                                    $badgeColor = match($transaction['type']) {
                                        'salary' => 'badge-vacation',
                                        'expense' => 'badge-absent',
                                        'project_payment' => 'badge-present',
                                        'project_cycle' => 'badge-present',
                                        default => 'badge-secondary'
                                    };
                                    $typeLabel = match($transaction['type']) {
                                        'salary' => __('Salary'),
                                        'expense' => __('Expense'),
                                        'project_payment' => __('Installment'),
                                        'project_cycle' => __('Subscription'),
                                        default => $transaction['type']
                                    };
                                @endphp
                                <span class="badge {{ $badgeColor }}">{{ $typeLabel }}</span>
                            </td>
                            <td>{{ $transaction['description'] }}</td>
                            <td style="text-align: right; font-weight: 600; color: var(--green);">
                                {{ $transaction['direction'] === 'in' ? '+ ' . number_format($transaction['amount'], 0) : '-' }}
                            </td>
                            <td style="text-align: right; font-weight: 600; color: var(--red);">
                                {{ $transaction['direction'] === 'out' ? '- ' . number_format($transaction['amount'], 0) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">
                                <i class="ri-history-line" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
                                <p style="color: var(--text-secondary); margin: 0;">{{ __('No transactions found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
