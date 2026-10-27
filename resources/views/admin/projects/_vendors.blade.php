{{-- External workers (subcontractors): the client pays us, we pay the worker, we keep the difference. --}}
@php
    $project->load('vendors.payments');
    $isSubscription = in_array($project->billing_type, ['monthly', 'yearly']);
    $period = $project->billing_type === 'yearly' ? __('year') : __('month');
    $agreedCost = (float) $project->vendors->sum('agreed_cost');
    $expectedMargin = (float) $project->agreed_price - $agreedCost;
@endphp

<div id="vendors" class="card" style="max-width: 1100px; margin: 24px auto 0;">
    <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 6px; font-weight: 600;">
        <i class="ri-user-shared-line" style="color: var(--color-primary); margin-inline-end: 8px;"></i>
        {{ __('External Workers (Subcontractors)') }}
    </h3>
    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0; margin-bottom: 18px;">
        {{ __('Use this when you hand the client\'s job to someone outside the team and pay them part of what the client pays you.') }}
    </p>

    @if($project->vendors->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px;">
            <div style="padding: 14px; border-radius: var(--radius-md); background: var(--bg-hover);">
                <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ __('Client pays') }}{{ $isSubscription ? ' / ' . $period : '' }}</div>
                <div style="font-size: 1.2rem; font-weight: 700;">{{ number_format($project->agreed_price, 2) }}</div>
            </div>
            <div style="padding: 14px; border-radius: var(--radius-md); background: var(--bg-hover);">
                <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ __('Worker cost') }}{{ $isSubscription ? ' / ' . $period : '' }}</div>
                <div style="font-size: 1.2rem; font-weight: 700; color: #ef4444;">{{ number_format($agreedCost, 2) }}</div>
            </div>
            <div style="padding: 14px; border-radius: var(--radius-md); background: rgba(34,197,94,0.08);">
                <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ __('My margin') }}{{ $isSubscription ? ' / ' . $period : '' }}</div>
                <div style="font-size: 1.2rem; font-weight: 700; color: {{ $expectedMargin >= 0 ? '#22c55e' : '#ef4444' }};">{{ number_format($expectedMargin, 2) }}</div>
            </div>
            <div style="padding: 14px; border-radius: var(--radius-md); background: var(--bg-hover);">
                <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ __('Paid to workers so far') }}</div>
                <div style="font-size: 1.2rem; font-weight: 700;">{{ number_format($project->vendor_paid, 2) }}</div>
            </div>
        </div>
    @endif

    {{-- Add worker --}}
    <form action="{{ route('admin.projects.vendors.store', $project->id) }}" method="POST" style="display: flex; gap: 12px; margin-bottom: 24px; align-items: flex-end; flex-wrap: wrap;">
        @csrf
        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
            <label class="form-label">{{ __('Worker Name') }} <span style="color: red;">*</span></label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group" style="flex: 1; min-width: 120px; margin-bottom: 0;">
            <label class="form-label">{{ __('Phone') }}</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="form-group" style="flex: 1; min-width: 130px; margin-bottom: 0;">
            <label class="form-label">{{ $isSubscription ? __('Cost per period') : __('Total Cost') }} <span style="color: red;">*</span></label>
            <input type="number" step="0.01" min="0" name="agreed_cost" class="form-control" required>
        </div>
        @if($isSubscription)
            <label style="display: flex; align-items: center; gap: 8px; height: 48px; cursor: pointer;" title="{{ __('A payment to the worker is scheduled automatically with every new billing cycle.') }}">
                <input type="checkbox" name="is_recurring" value="1" checked>
                <span>{{ __('Repeats every cycle') }}</span>
            </label>
        @else
            <div class="form-group" style="flex: 0 0 110px; margin-bottom: 0;">
                <label class="form-label" title="{{ __('The total cost is split into equal monthly payments. Leave empty to add payments manually.') }}">{{ __('Months') }} <i class="ri-information-line"></i></label>
                <input type="number" min="1" max="120" name="months" class="form-control">
            </div>
            <div class="form-group" style="flex: 1; min-width: 140px; margin-bottom: 0;">
                <label class="form-label">{{ __('First Due Date') }}</label>
                <input type="date" name="first_due_date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
        @endif
        <button type="submit" class="btn btn-primary" style="height: 48px;">
            <i class="ri-add-line"></i> {{ __('Add Worker') }}
        </button>
    </form>

    @forelse($project->vendors as $vendor)
        <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-bottom: 16px;">
            <form action="{{ route('admin.projects.vendors.update', $vendor->id) }}" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 12px;">
                @csrf
                @method('PUT')
                <input type="text" name="name" class="form-control" required value="{{ $vendor->name }}" style="flex: 1; min-width: 140px;">
                <input type="text" name="phone" class="form-control" placeholder="{{ __('Phone') }}" value="{{ $vendor->phone }}" style="flex: 1; min-width: 110px;">
                <input type="number" step="0.01" min="0" name="agreed_cost" class="form-control" required value="{{ $vendor->agreed_cost }}" style="flex: 0 0 140px;" title="{{ $vendor->is_recurring ? __('Cost per period') : __('Total Cost') }}">
                <button class="btn btn-secondary btn-icon" title="{{ __('Save') }}"><i class="ri-save-line"></i></button>
            </form>
            <form id="delete-vendor-{{ $vendor->id }}" action="{{ route('admin.projects.vendors.destroy', $vendor->id) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>

            <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 0.9rem; margin-bottom: 12px; align-items: center;">
                @if($vendor->is_recurring)
                    <span class="badge" style="background: rgba(59,130,246,0.12); color: var(--color-primary-light);"><i class="ri-repeat-line"></i> {{ __('Repeats every cycle') }}</span>
                @endif
                <span>{{ __('Paid') }}: <strong style="color: #22c55e;">{{ number_format($vendor->paid_total, 2) }}</strong></span>
                <span>{{ __('Pending') }}: <strong style="color: #f59e0b;">{{ number_format($vendor->pending_total, 2) }}</strong></span>
                <button type="button" class="btn btn-danger btn-icon" style="margin-inline-start: auto;" title="{{ __('Delete Worker') }}" onclick="showGlobalConfirmPopup('delete-vendor-{{ $vendor->id }}', '{{ __('Are you sure you want to delete this worker?') }}')">
                    <i class="ri-delete-bin-fill"></i>
                </button>
            </div>

            <div class="table-responsive">
                <table class="data-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>{{ __('Title / Description') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Due Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Paid Date') }}</th>
                            <th style="text-align: center; width: 110px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendor->payments->sortBy('due_date') as $p)
                            <tr>
                                <form id="vendor-payment-{{ $p->id }}" action="{{ route('admin.projects.vendors.payments.update', $p->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <td><input form="vendor-payment-{{ $p->id }}" type="text" name="title" class="form-control" value="{{ $p->title }}"></td>
                                <td><input form="vendor-payment-{{ $p->id }}" type="number" step="0.01" min="0.01" name="amount" class="form-control" required value="{{ $p->amount }}"></td>
                                <td><input form="vendor-payment-{{ $p->id }}" type="date" name="due_date" class="form-control" required value="{{ $p->due_date->format('Y-m-d') }}"></td>
                                <td>
                                    <select form="vendor-payment-{{ $p->id }}" name="status" class="form-control">
                                        <option value="pending" {{ $p->status === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                        <option value="paid" {{ $p->status === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                                    </select>
                                </td>
                                <td><input form="vendor-payment-{{ $p->id }}" type="date" name="paid_at" class="form-control" value="{{ $p->paid_at?->format('Y-m-d') }}"></td>
                                <td style="text-align: center;">
                                    <button form="vendor-payment-{{ $p->id }}" class="btn btn-icon btn-success" title="{{ __('Save') }}"><i class="ri-save-line"></i></button>
                                    <button type="submit" form="delete-vendor-payment-{{ $p->id }}" class="btn btn-icon btn-danger" title="{{ __('Delete') }}"><i class="ri-delete-bin-fill"></i></button>
                                    <form id="delete-vendor-payment-{{ $p->id }}" action="{{ route('admin.projects.vendors.payments.destroy', $p->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <form id="new-vendor-payment-{{ $vendor->id }}" action="{{ route('admin.projects.vendors.payments.store', $vendor->id) }}" method="POST">
                                @csrf
                            </form>
                            <td><input form="new-vendor-payment-{{ $vendor->id }}" type="text" name="title" class="form-control" placeholder="{{ __('Title / Description') }}"></td>
                            <td><input form="new-vendor-payment-{{ $vendor->id }}" type="number" step="0.01" min="0.01" name="amount" class="form-control" required placeholder="{{ __('Amount') }}"></td>
                            <td><input form="new-vendor-payment-{{ $vendor->id }}" type="date" name="due_date" class="form-control" required value="{{ date('Y-m-d') }}"></td>
                            <td>
                                <select form="new-vendor-payment-{{ $vendor->id }}" name="status" class="form-control">
                                    <option value="paid">{{ __('Paid') }}</option>
                                    <option value="pending">{{ __('Pending') }}</option>
                                </select>
                            </td>
                            <td></td>
                            <td style="text-align: center;">
                                <button form="new-vendor-payment-{{ $vendor->id }}" class="btn btn-icon btn-primary" title="{{ __('Add Payment') }}"><i class="ri-add-line"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 24px; color: var(--text-secondary); background: var(--bg-hover); border-radius: var(--radius-md);">
            {{ __('No external workers added yet.') }}
        </div>
    @endforelse
</div>
