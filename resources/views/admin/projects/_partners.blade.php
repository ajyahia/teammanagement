{{-- Partners, their capital contributions and the profit split. $project is the edited project. --}}
@php
    $project->load(['partners.contributions', 'expenses']);
    $netProfit = $project->net_profit;
@endphp

<div id="partners" class="card" style="max-width: 1100px; margin: 24px auto 0;">
    <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 16px; font-weight: 600;">
        <i class="ri-team-line" style="color: var(--color-primary); margin-inline-end: 8px;"></i>
        {{ __('Partners & Profit Split') }}
    </h3>

    {{-- Profit summary --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div style="padding: 14px; border-radius: var(--radius-md); background: var(--bg-hover);">
            <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ __('Received from Client') }}</div>
            <div style="font-size: 1.2rem; font-weight: 700;">{{ number_format($project->total_paid, 2) }}</div>
        </div>
        <div style="padding: 14px; border-radius: var(--radius-md); background: var(--bg-hover);">
            <div style="font-size: 0.8rem; color: var(--text-secondary);">
                {{ __('Project Expenses') }}
                <a href="{{ route('admin.expenses.create', ['project_id' => $project->id]) }}" title="{{ __('Add Expense') }}"><i class="ri-add-circle-line"></i></a>
            </div>
            <div style="font-size: 1.2rem; font-weight: 700;">{{ number_format($project->expenses->sum('amount'), 2) }}</div>
        </div>
        <div style="padding: 14px; border-radius: var(--radius-md); background: var(--bg-hover);">
            <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ __('Net Profit') }}</div>
            <div style="font-size: 1.2rem; font-weight: 700; color: {{ $netProfit >= 0 ? '#22c55e' : '#ef4444' }};">{{ number_format($netProfit, 2) }}</div>
        </div>
        <div style="padding: 14px; border-radius: var(--radius-md); background: rgba(59,130,246,0.08);">
            <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ __('My Share') }} ({{ rtrim(rtrim(number_format($project->owner_share_percent, 2), '0'), '.') }}%)</div>
            <div style="font-size: 1.2rem; font-weight: 700;">{{ number_format($project->owner_profit_share, 2) }}</div>
        </div>
    </div>

    {{-- Add partner --}}
    <form action="{{ route('admin.projects.partners.store', $project->id) }}" method="POST" style="display: flex; gap: 12px; margin-bottom: 24px; align-items: flex-end; flex-wrap: wrap;">
        @csrf
        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
            <label class="form-label">{{ __('Partner Name') }} <span style="color: red;">*</span></label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>
        <div class="form-group" style="flex: 1; min-width: 120px; margin-bottom: 0;">
            <label class="form-label">{{ __('Phone') }}</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
        </div>
        <div class="form-group" style="flex: 0 0 100px; margin-bottom: 0;">
            <label class="form-label">{{ __('Share %') }} <span style="color: red;">*</span></label>
            <input type="number" step="0.01" min="0.01" max="100" name="share_percent" class="form-control" required value="{{ old('share_percent') }}">
        </div>
        <div class="form-group" style="flex: 1; min-width: 130px; margin-bottom: 0;">
            <label class="form-label">{{ __('Capital to Pay') }} <span style="color: red;">*</span></label>
            <input type="number" step="0.01" min="0" name="committed_capital" class="form-control" required value="{{ old('committed_capital', '0') }}">
        </div>
        <div class="form-group" style="flex: 0 0 110px; margin-bottom: 0;">
            <label class="form-label" title="{{ __('Capital is split into equal monthly installments. Leave empty to add them manually.') }}">{{ __('Months') }} <i class="ri-information-line"></i></label>
            <input type="number" min="1" max="120" name="months" class="form-control" value="{{ old('months') }}">
        </div>
        <div class="form-group" style="flex: 1; min-width: 140px; margin-bottom: 0;">
            <label class="form-label">{{ __('First Due Date') }}</label>
            <input type="date" name="first_due_date" class="form-control" value="{{ old('first_due_date', date('Y-m-d')) }}">
        </div>
        <button type="submit" class="btn btn-primary" style="height: 48px;">
            <i class="ri-add-line"></i> {{ __('Add Partner') }}
        </button>
    </form>

    @forelse($project->partners as $partner)
        <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-bottom: 16px;">
            {{-- Partner header: editable --}}
            <form action="{{ route('admin.projects.partners.update', $partner->id) }}" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 12px;">
                @csrf
                @method('PUT')
                <input type="text" name="name" class="form-control" required value="{{ $partner->name }}" style="flex: 1; min-width: 140px;">
                <input type="text" name="phone" class="form-control" placeholder="{{ __('Phone') }}" value="{{ $partner->phone }}" style="flex: 1; min-width: 110px;">
                <input type="number" step="0.01" min="0.01" max="100" name="share_percent" class="form-control" required value="{{ $partner->share_percent }}" style="flex: 0 0 90px;" title="{{ __('Share %') }}">
                <input type="number" step="0.01" min="0" name="committed_capital" class="form-control" required value="{{ $partner->committed_capital }}" style="flex: 1; min-width: 120px;" title="{{ __('Capital to Pay') }}">
                <button class="btn btn-secondary btn-icon" title="{{ __('Save') }}"><i class="ri-save-line"></i></button>
            </form>
            <form id="delete-partner-{{ $partner->id }}" action="{{ route('admin.projects.partners.destroy', $partner->id) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>

            <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 0.9rem; margin-bottom: 12px;">
                <span>{{ __('Paid') }}: <strong style="color: #22c55e;">{{ number_format($partner->paid_capital, 2) }}</strong></span>
                <span>{{ __('Remaining Capital') }}: <strong style="color: #ef4444;">{{ number_format($partner->remaining_capital, 2) }}</strong></span>
                <span>{{ __('Profit Share') }}: <strong>{{ number_format($partner->profit_share, 2) }}</strong></span>
                <button type="button" class="btn btn-danger btn-icon" style="margin-inline-start: auto;" title="{{ __('Delete Partner') }}" onclick="showGlobalConfirmPopup('delete-partner-{{ $partner->id }}', '{{ __('Are you sure you want to delete this partner?') }}')">
                    <i class="ri-delete-bin-fill"></i>
                </button>
            </div>

            {{-- Contributions --}}
            <div class="table-responsive">
                <table class="data-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Due Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Paid Date') }}</th>
                            <th style="text-align: center; width: 110px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partner->contributions->sortBy('due_date') as $c)
                            <tr>
                                <form id="contribution-{{ $c->id }}" action="{{ route('admin.projects.partners.contributions.update', $c->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <td><input form="contribution-{{ $c->id }}" type="number" step="0.01" min="0.01" name="amount" class="form-control" required value="{{ $c->amount }}"></td>
                                <td><input form="contribution-{{ $c->id }}" type="date" name="due_date" class="form-control" required value="{{ $c->due_date->format('Y-m-d') }}"></td>
                                <td>
                                    <select form="contribution-{{ $c->id }}" name="status" class="form-control">
                                        <option value="pending" {{ $c->status === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                        <option value="paid" {{ $c->status === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                                    </select>
                                </td>
                                <td><input form="contribution-{{ $c->id }}" type="date" name="paid_at" class="form-control" value="{{ $c->paid_at?->format('Y-m-d') }}"></td>
                                <td style="text-align: center;">
                                    <button form="contribution-{{ $c->id }}" class="btn btn-icon btn-success" title="{{ __('Save') }}"><i class="ri-save-line"></i></button>
                                    <button type="submit" form="delete-contribution-{{ $c->id }}" class="btn btn-icon btn-danger" title="{{ __('Delete') }}"><i class="ri-delete-bin-fill"></i></button>
                                    <form id="delete-contribution-{{ $c->id }}" action="{{ route('admin.projects.partners.contributions.destroy', $c->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Add a contribution manually --}}
                        <tr>
                            <form id="new-contribution-{{ $partner->id }}" action="{{ route('admin.projects.partners.contributions.store', $partner->id) }}" method="POST">
                                @csrf
                            </form>
                            <td><input form="new-contribution-{{ $partner->id }}" type="number" step="0.01" min="0.01" name="amount" class="form-control" required placeholder="{{ __('Amount') }}"></td>
                            <td><input form="new-contribution-{{ $partner->id }}" type="date" name="due_date" class="form-control" required value="{{ date('Y-m-d') }}"></td>
                            <td>
                                <select form="new-contribution-{{ $partner->id }}" name="status" class="form-control">
                                    <option value="paid">{{ __('Paid') }}</option>
                                    <option value="pending">{{ __('Pending') }}</option>
                                </select>
                            </td>
                            <td></td>
                            <td style="text-align: center;">
                                <button form="new-contribution-{{ $partner->id }}" class="btn btn-icon btn-primary" title="{{ __('Add Contribution') }}"><i class="ri-add-line"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 24px; color: var(--text-secondary); background: var(--bg-hover); border-radius: var(--radius-md);">
            {{ __('No partners added yet.') }}
        </div>
    @endforelse
</div>
