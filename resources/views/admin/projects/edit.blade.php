@extends('layouts.app')

@section('title', __('Edit Project'))
@section('page_header', __('Edit Project'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <div class="card" style="max-width: 1100px; margin: 0 auto;">
        <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 16px; font-weight: 600;">{{ __('Edit Project') }} #{{ $project->id }}</h3>

        <form method="POST" action="{{ route('admin.projects.update', $project->id) }}">
            @csrf
            @method('PUT')
            @include('admin.projects._form', ['project' => $project])
        </form>
    </div>

    <!-- Financial Overview Summary Card -->
    <div class="card" style="max-width: 1100px; margin: 24px auto 0; background: linear-gradient(135deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);">
        <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 16px; font-weight: 600;">
            <i class="ri-funds-line" style="color: var(--color-primary); margin-inline-end: 8px;"></i>
            {{ __('Financial Summary') }}
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div style="background: var(--bg-hover); padding: 14px 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <span style="font-size: 0.85rem; color: var(--text-secondary); display: block; margin-bottom: 4px;">{{ in_array($project->billing_type, ['monthly', 'yearly']) ? __('Total Invoiced') : __('Agreed Price') }}</span>
                <span style="font-size: 1.35rem; font-weight: 700; color: var(--text-main);">{{ number_format($project->total_revenue, 2) }}</span>
            </div>
            <div style="background: rgba(34,197,94,0.08); padding: 14px 18px; border-radius: var(--radius-md); border: 1px solid rgba(34,197,94,0.2);">
                <span style="font-size: 0.85rem; color: #22c55e; display: block; margin-bottom: 4px;">{{ __('Total Paid') }}</span>
                <span style="font-size: 1.35rem; font-weight: 700; color: #22c55e;">{{ number_format($project->total_paid, 2) }}</span>
            </div>
            <div style="background: rgba(239,68,68,0.08); padding: 14px 18px; border-radius: var(--radius-md); border: 1px solid rgba(239,68,68,0.2);">
                <span style="font-size: 0.85rem; color: #ef4444; display: block; margin-bottom: 4px;">{{ __('Remaining Due') }}</span>
                <span style="font-size: 1.35rem; font-weight: 700; color: #ef4444;">{{ number_format($project->due_amount, 2) }}</span>
            </div>
        </div>
    </div>

    @if(in_array($project->billing_type, ['monthly', 'yearly']))
    <!-- Subscription Billing Cycles Section -->
    <div id="cycles" class="card" style="max-width: 1100px; margin: 24px auto 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <h3 style="font-family: var(--font-title); margin: 0; font-weight: 600;">
                <i class="ri-calendar-event-line" style="color: var(--color-primary); margin-inline-end: 8px;"></i>
                {{ __('Subscription Billing Cycles') }}
            </h3>
            <span class="badge {{ $project->subscription_status === 'active' ? 'badge-present' : 'badge-absent' }}">
                {{ __('Subscription:') }} {{ __($project->subscription_status === 'active' ? 'Active' : 'Stopped') }}
            </span>
        </div>

        <form action="{{ route('admin.projects.cycles.store', $project->id) }}" method="POST" style="display: flex; gap: 15px; margin-bottom: 24px; align-items: flex-end; flex-wrap: wrap;">
            @csrf
            <div class="form-group" style="flex: 1; min-width: 160px; margin-bottom: 0;">
                <label class="form-label">{{ __('Billing Date') }} <span style="color: red;">*</span></label>
                <input type="date" name="billing_date" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group" style="flex: 1; min-width: 140px; margin-bottom: 0;">
                <label class="form-label">{{ __('Amount') }} <span style="color: red;">*</span></label>
                <input type="number" step="0.01" name="amount" class="form-control" required value="{{ $project->agreed_price }}">
            </div>
            <button type="submit" class="btn btn-primary" style="height: 48px;">
                <i class="ri-add-line"></i> {{ __('Add Cycle') }}
            </button>
        </form>

        @if($project->cycles->count() > 0)
            <div class="table-responsive" style="border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                <table class="data-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th style="padding: 12px 16px;">{{ __('Cycle') }}</th>
                            <th style="padding: 12px 16px;">{{ __('Amount') }}</th>
                            <th style="padding: 12px 16px;">{{ __('Paid') }}</th>
                            <th style="padding: 12px 16px;">{{ __('Due') }}</th>
                            <th style="padding: 12px 16px;">{{ __('Status') }}</th>
                            <th style="padding: 12px 16px; text-align: center; width: 130px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->cycles()->orderBy('billing_date', 'desc')->get() as $cycle)
                            @php
                                $status = $cycle->payment_status;
                            @endphp
                            <tr>
                                <td style="padding: 12px 16px; font-size: 0.95rem;">
                                    <strong>{{ $cycle->billing_date->format('M Y') }}</strong>
                                    <span style="display:block; font-size: 0.8rem; color: var(--text-secondary);">{{ $cycle->billing_date->format('Y-m-d') }}</span>
                                </td>
                                <td style="padding: 12px 16px; font-weight: 600;">
                                    {{ number_format($cycle->amount, 2) }}
                                </td>
                                <td style="padding: 12px 16px; font-weight: 600; color: #22c55e;">
                                    {{ number_format($cycle->total_paid, 2) }}
                                </td>
                                <td style="padding: 12px 16px; font-weight: 600; color: #ef4444;">
                                    {{ number_format($cycle->due_amount, 2) }}
                                </td>
                                <td style="padding: 12px 16px;">
                                    @if($status === 'paid')
                                        <span class="badge" style="background: rgba(34,197,94,0.1); color: #22c55e;">
                                            <i class="ri-check-line"></i> {{ __('Paid in Full') }}
                                        </span>
                                    @elseif($status === 'partial')
                                        <span class="badge" style="background: rgba(245,158,11,0.15); color: #f59e0b;">
                                            <i class="ri-pie-chart-line"></i> {{ __('Partially Paid') }}
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                                            <i class="ri-close-line"></i> {{ __('Unpaid') }}
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                        <!-- Quick add payment for this cycle -->
                                        @if($cycle->due_amount > 0)
                                            <button type="button" class="btn btn-icon btn-primary" title="{{ __('Add Installment for this Cycle') }}" onclick="selectCycleForPayment({{ $cycle->id }}, '{{ $cycle->billing_date->format('M Y') }}', {{ $cycle->due_amount }})">
                                                <i class="ri-add-circle-line"></i>
                                            </button>
                                        @endif

                                        <!-- Edit cycle -->
                                        <button type="button" class="btn btn-icon btn-secondary" title="{{ __('Edit Cycle') }}" onclick="openCycleEdit(@js([
                                            'action' => route('admin.projects.cycles.update', $cycle->id),
                                            'billing_date' => $cycle->billing_date->format('Y-m-d'),
                                            'amount' => (float) $cycle->amount,
                                        ]))">
                                            <i class="ri-pencil-fill"></i>
                                        </button>

                                        <!-- Toggle Paid Status Form -->
                                        <form action="{{ route('admin.projects.cycles.toggle_paid', $cycle->id) }}" method="POST" style="margin:0;">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-icon {{ $cycle->is_paid ? 'btn-secondary' : 'btn-success' }}" title="{{ $cycle->is_paid ? __('Mark as Unpaid') : __('Mark Full Cycle Paid') }}">
                                                <i class="ri-money-dollar-circle-line"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- Delete cycle -->
                                        <form id="delete-cycle-{{ $cycle->id }}" action="{{ route('admin.projects.cycles.destroy', $cycle->id) }}" method="POST" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-icon" title="{{ __('Delete Cycle') }}" onclick="showGlobalConfirmPopup('delete-cycle-{{ $cycle->id }}', '{{ __('Are you sure you want to delete this cycle?') }}')">
                                                <i class="ri-delete-bin-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 30px; color: var(--text-secondary); background: var(--bg-hover); border-radius: var(--radius-md);">
                {{ __('No billing cycles added yet.') }}
            </div>
        @endif
    </div>
    @endif

    <!-- Project Installments & Payments Section (Available for All Billing Types) -->
    <div id="payments" class="card" style="max-width: 1100px; margin: 24px auto 0;">
        <h3 style="font-family: var(--font-title); margin-top: 0; margin-bottom: 24px; font-weight: 600;">
            <i class="ri-bank-card-line" style="color: var(--color-primary); margin-inline-end: 8px;"></i>
            {{ __('Project Installments / Payments') }}
        </h3>

        <form id="payment_form" action="{{ route('admin.projects.payments.store', $project->id) }}" method="POST" style="display: flex; gap: 15px; margin-bottom: 24px; align-items: flex-end; flex-wrap: wrap;">
            @csrf
            
            @if(in_array($project->billing_type, ['monthly', 'yearly']))
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label class="form-label">{{ __('Link to Billing Cycle') }}</label>
                <select name="cycle_id" id="payment_cycle_id" class="form-control">
                    <option value="">-- {{ __('General / Not linked to a cycle') }} --</option>
                    @foreach($project->cycles()->orderBy('billing_date', 'desc')->get() as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->billing_date->format('M Y') }} ({{ __('Due:') }} {{ number_format($c->due_amount, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                <label class="form-label">{{ __('Title / Description') }} <span style="color: red;">*</span></label>
                <input type="text" id="payment_title" name="title" class="form-control" required placeholder="{{ __('e.g. Deposit, First Installment, Part 1') }}">
            </div>
            <div class="form-group" style="flex: 1; min-width: 110px; margin-bottom: 0;">
                <label class="form-label">{{ __('Amount') }} <span style="color: red;">*</span></label>
                <input type="number" step="0.01" id="payment_amount" name="amount" class="form-control" required>
            </div>
            <div class="form-group" style="flex: 1; min-width: 130px; margin-bottom: 0;">
                <label class="form-label">{{ __('Date') }} <span style="color: red;">*</span></label>
                <input type="date" name="due_date" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group" style="flex: 1; min-width: 120px; margin-bottom: 0;">
                <label class="form-label">{{ __('Status') }} <span style="color: red;">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="paid">{{ __('Paid') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="overdue">{{ __('Overdue') }}</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="height: 48px;">
                <i class="ri-add-line"></i> {{ __('Add Installment') }}
            </button>
        </form>

        @if(!in_array($project->billing_type, ['monthly', 'yearly']))
            @if($errors->has('installment_amount'))
                <div class="project-form-errors" style="margin-bottom: 12px;">{{ $errors->first('installment_amount') }}</div>
            @endif
            <form action="{{ route('admin.projects.payments.generate', $project->id) }}" method="POST" style="display: flex; gap: 15px; margin-bottom: 24px; align-items: flex-end; flex-wrap: wrap; padding: 14px; border: 1px dashed var(--border-color); border-radius: var(--radius-md);">
                @csrf
                <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label class="form-label" title="{{ __('The unscheduled balance is split into monthly pending installments of this amount.') }}">{{ __('Monthly Installment') }} <i class="ri-information-line"></i></label>
                    <input type="number" step="0.01" min="0.01" name="installment_amount" class="form-control" required>
                </div>
                <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label class="form-label">{{ __('First Due Date') }}</label>
                    <input type="date" name="first_due_date" class="form-control" required value="{{ date('Y-m-d', strtotime('+1 month')) }}">
                </div>
                <button type="submit" class="btn btn-secondary" style="height: 48px;">
                    <i class="ri-calendar-schedule-line"></i> {{ __('Generate Installments') }}
                </button>
            </form>
        @endif

        @if($project->payments->count() > 0)
            <div class="table-responsive" style="border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                <table class="data-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th style="padding: 12px 16px;">{{ __('Title / Details') }}</th>
                            @if(in_array($project->billing_type, ['monthly', 'yearly']))
                                <th style="padding: 12px 16px;">{{ __('Cycle') }}</th>
                            @endif
                            <th style="padding: 12px 16px;">{{ __('Amount') }}</th>
                            <th style="padding: 12px 16px;">{{ __('Date') }}</th>
                            <th style="padding: 12px 16px;">{{ __('Status') }}</th>
                            <th style="padding: 12px 16px; text-align: center; width: 100px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->payments()->orderBy('due_date', 'desc')->get() as $payment)
                            <tr>
                                <td style="padding: 12px 16px; font-weight: 600;">
                                    {{ $payment->title }}
                                    @if($payment->notes)
                                        <div style="font-size: 0.8rem; color: var(--text-secondary); font-weight: normal;">{{ $payment->notes }}</div>
                                    @endif
                                </td>
                                @if(in_array($project->billing_type, ['monthly', 'yearly']))
                                    <td style="padding: 12px 16px; font-size: 0.85rem;">
                                        @if($payment->cycle)
                                            <span class="badge badge-vacation">{{ $payment->cycle->billing_date->format('M Y') }}</span>
                                        @else
                                            <span style="color: var(--text-secondary);">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td style="padding: 12px 16px; font-weight: 600; color: var(--color-primary-light);">
                                    {{ number_format($payment->amount, 2) }}
                                </td>
                                <td style="padding: 12px 16px; font-size: 0.95rem; color: var(--text-secondary);">
                                    {{ $payment->due_date->format('Y-m-d') }}
                                </td>
                                <td style="padding: 12px 16px;">
                                    @if($payment->status === 'paid')
                                        <span class="badge" style="background: rgba(34,197,94,0.1); color: #22c55e;">
                                            <i class="ri-check-line"></i> {{ __('Paid on') }} {{ $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '' }}
                                        </span>
                                    @elseif($payment->status === 'overdue')
                                        <span class="badge" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                                            <i class="ri-alarm-warning-line"></i> {{ __('Overdue') }}
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                                            <i class="ri-time-line"></i> {{ __('Pending') }}
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px; text-align: center; display: flex; gap: 8px; justify-content: center;">
                                    <!-- Edit installment -->
                                    <button type="button" class="btn btn-icon btn-secondary" title="{{ __('Edit Installment') }}" onclick="openPaymentEdit(@js([
                                        'action' => route('admin.projects.payments.update', $payment->id),
                                        'cycle_id' => $payment->cycle_id,
                                        'title' => $payment->title,
                                        'amount' => (float) $payment->amount,
                                        'due_date' => $payment->due_date->format('Y-m-d'),
                                        'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '',
                                        'status' => $payment->status,
                                        'notes' => $payment->notes,
                                    ]))">
                                        <i class="ri-pencil-fill"></i>
                                    </button>

                                    <!-- Toggle Paid Status Form -->
                                    <form action="{{ route('admin.projects.payments.update', $payment->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="cycle_id" value="{{ $payment->cycle_id }}">
                                        <input type="hidden" name="title" value="{{ $payment->title }}">
                                        <input type="hidden" name="amount" value="{{ $payment->amount }}">
                                        <input type="hidden" name="due_date" value="{{ $payment->due_date->format('Y-m-d') }}">
                                        <input type="hidden" name="status" value="{{ $payment->status === 'paid' ? 'pending' : 'paid' }}">
                                        <button class="btn btn-icon {{ $payment->status === 'paid' ? 'btn-secondary' : 'btn-success' }}" title="{{ $payment->status === 'paid' ? __('Mark as Pending') : __('Mark as Paid') }}">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </button>
                                    </form>
                                    
                                    <form id="delete-payment-{{ $payment->id }}" action="{{ route('admin.projects.payments.destroy', $payment->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-icon" title="{{ __('Delete Installment') }}" onclick="showGlobalConfirmPopup('delete-payment-{{ $payment->id }}', '{{ __('Are you sure you want to delete this installment?') }}')">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 30px; color: var(--text-secondary); background: var(--bg-hover); border-radius: var(--radius-md);">
                {{ __('No installments/payments added yet.') }}
            </div>
        @endif
    </div>

    @include('admin.projects._vendors', ['project' => $project])

    @include('admin.projects._partners', ['project' => $project])

    <!-- Edit Installment Modal -->
    <div id="paymentEditModal" class="custom-modal-overlay" style="display: none;">
        <div class="custom-modal" style="max-width: 520px; text-align: start;">
            <h4 class="modal-title">{{ __('Edit Installment') }}</h4>
            <form id="paymentEditForm" method="POST">
                @csrf
                @method('PUT')
                @if(in_array($project->billing_type, ['monthly', 'yearly']))
                    <div class="form-group">
                        <label class="form-label">{{ __('Link to Billing Cycle') }}</label>
                        <select name="cycle_id" id="edit_payment_cycle_id" class="form-control">
                            <option value="">-- {{ __('General / Not linked to a cycle') }} --</option>
                            @foreach($project->cycles()->orderBy('billing_date', 'desc')->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->billing_date->format('M Y') }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="form-group">
                    <label class="form-label">{{ __('Title / Description') }} <span style="color: red;">*</span></label>
                    <input type="text" name="title" id="edit_payment_title" class="form-control" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">{{ __('Amount') }} <span style="color: red;">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="edit_payment_amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Date') }} <span style="color: red;">*</span></label>
                        <input type="date" name="due_date" id="edit_payment_due_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Status') }} <span style="color: red;">*</span></label>
                        <select name="status" id="edit_payment_status" class="form-control" required onchange="togglePaidAtField()">
                            <option value="paid">{{ __('Paid') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="overdue">{{ __('Overdue') }}</option>
                        </select>
                    </div>
                    <div class="form-group" id="edit_payment_paid_at_group">
                        <label class="form-label">{{ __('Payment Date') }}</label>
                        <input type="date" name="paid_at" id="edit_payment_paid_at" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <textarea name="notes" id="edit_payment_notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal('paymentEditModal')">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> {{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Cycle Modal -->
    <div id="cycleEditModal" class="custom-modal-overlay" style="display: none;">
        <div class="custom-modal" style="max-width: 420px; text-align: start;">
            <h4 class="modal-title">{{ __('Edit Cycle') }}</h4>
            <form id="cycleEditForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">{{ __('Billing Date') }} <span style="color: red;">*</span></label>
                    <input type="date" name="billing_date" id="edit_cycle_billing_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Amount') }} <span style="color: red;">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" id="edit_cycle_amount" class="form-control" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal('cycleEditModal')">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> {{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPaymentEdit(p) {
            document.getElementById('paymentEditForm').action = p.action;
            const cycleSelect = document.getElementById('edit_payment_cycle_id');
            if (cycleSelect) cycleSelect.value = p.cycle_id ?? '';
            document.getElementById('edit_payment_title').value = p.title;
            document.getElementById('edit_payment_amount').value = p.amount;
            document.getElementById('edit_payment_due_date').value = p.due_date;
            document.getElementById('edit_payment_status').value = p.status;
            document.getElementById('edit_payment_paid_at').value = p.paid_at;
            document.getElementById('edit_payment_notes').value = p.notes ?? '';
            togglePaidAtField();
            document.getElementById('paymentEditModal').style.display = 'flex';
        }

        function togglePaidAtField() {
            const isPaid = document.getElementById('edit_payment_status').value === 'paid';
            document.getElementById('edit_payment_paid_at_group').style.display = isPaid ? '' : 'none';
        }

        function openCycleEdit(c) {
            document.getElementById('cycleEditForm').action = c.action;
            document.getElementById('edit_cycle_billing_date').value = c.billing_date;
            document.getElementById('edit_cycle_amount').value = c.amount;
            document.getElementById('cycleEditModal').style.display = 'flex';
        }

        function closeEditModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function selectCycleForPayment(cycleId, cycleName, dueAmount) {
            const cycleSelect = document.getElementById('payment_cycle_id');
            const titleInput = document.getElementById('payment_title');
            const amountInput = document.getElementById('payment_amount');
            
            if (cycleSelect) {
                cycleSelect.value = cycleId;
            }
            if (titleInput) {
                titleInput.value = "{{ __('Installment for') }} " + cycleName;
            }
            if (amountInput) {
                amountInput.value = dueAmount;
            }
            const paymentForm = document.getElementById('payment_form');
            if (paymentForm) {
                paymentForm.scrollIntoView({ behavior: 'smooth' });
                if (amountInput) amountInput.focus();
            }
        }
    </script>
@endsection
