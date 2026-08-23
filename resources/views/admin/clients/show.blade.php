@extends('layouts.app')

@section('title', __('Client Profile') . ' - ' . $client->name)
@section('page_header', __('Client Profile'))

@section('sidebar_menu')
    @include('layouts.sidebar_admin')
@endsection

@section('content')
    <!-- Client Details & Stats -->
    <div style="display: grid; grid-template-columns: 1fr 3fr; gap: 20px; margin-bottom: 24px;">
        <!-- Profile Info -->
        <div class="card" style="padding: 24px; text-align: center;">
            <div style="width: 80px; height: 80px; background: rgba(0, 158, 253, 0.1); color: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 15px;">
                {{ strtoupper(substr($client->name, 0, 1)) }}
            </div>
            <h3 style="margin: 0 0 5px; font-family: var(--font-title); font-weight: 700;">{{ $client->name }}</h3>
            <p style="color: var(--text-secondary); margin: 0 0 15px; font-size: 0.9rem;">{{ $client->company ?: __('Individual') }}</p>
            
            <div style="text-align: left; font-size: 0.9rem; margin-top: 20px;">
                <div style="margin-bottom: 10px;">
                    <i class="ri-phone-fill" style="color: var(--text-secondary); margin-right: 5px; width: 20px;"></i>
                    {{ $client->phone ?: '-' }}
                </div>
                <div style="margin-bottom: 10px;">
                    <i class="ri-mail-fill" style="color: var(--text-secondary); margin-right: 5px; width: 20px;"></i>
                    {{ $client->email ?: '-' }}
                </div>
            </div>
            
            <div style="margin-top: 20px; text-align: left;">
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;"><strong>{{ __('Client Portal Status:') }}</strong></p>
                @if($client->password)
                    <span class="badge badge-present"><i class="ri-check-line"></i> {{ __('Active') }}</span>
                @else
                    <span class="badge badge-absent"><i class="ri-close-line"></i> {{ __('Inactive (No Password)') }}</span>
                @endif
                <div style="margin-top: 10px;">
                    <button class="btn btn-secondary" style="width: 100%; font-size: 0.85rem;" onclick="document.getElementById('passwordModal').style.display = 'flex'">
                        <i class="ri-key-fill"></i> {{ __('Set Portal Password') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Financial Stats -->
        <div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div class="card" style="padding: 20px; border-left: 4px solid var(--color-primary);">
                    <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.9rem;">{{ __('Total Projects') }}</p>
                    <h3 style="margin: 0; color: var(--text-primary); font-family: var(--font-title);">{{ $totalProjects }}</h3>
                </div>
                <div class="card" style="padding: 20px; border-left: 4px solid var(--green);">
                    <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.9rem;">{{ __('Total Paid') }}</p>
                    <h3 style="margin: 0; color: var(--green); font-family: var(--font-title);">{{ number_format($totalPaid, 2) }}</h3>
                </div>
                <div class="card" style="padding: 20px; border-left: 4px solid var(--red);">
                    <p style="color: var(--text-secondary); margin: 0 0 5px; font-size: 0.9rem;">{{ __('Total Due') }}</p>
                    <h3 style="margin: 0; color: var(--red); font-family: var(--font-title);">{{ number_format($totalDue, 2) }}</h3>
                </div>
            </div>

            <!-- Notes -->
            @if($client->notes)
                <div class="card" style="padding: 20px;">
                    <h4 style="margin: 0 0 10px; font-family: var(--font-title);">{{ __('Client Notes') }}</h4>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0; line-height: 1.6;">{{ $client->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Client Projects & Payments -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Projects & Payments') }}</h3>
        <button class="btn btn-primary" onclick="openPaymentModal()" style="font-size: 0.9rem; padding: 8px 16px;">
            <i class="ri-add-circle-line"></i> {{ __('Add Payment') }}
        </button>
    </div>
    
    @forelse($client->projects as $project)
        <div class="card" style="margin-bottom: 20px; padding: 0;">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h4 style="margin: 0 0 5px; font-family: var(--font-title); color: var(--color-primary-light);">
                        <i class="ri-briefcase-4-fill" style="margin-right: 5px;"></i> {{ $project->service->name }}
                        @if($project->billing_type === 'monthly')
                            <span class="badge badge-vacation" style="margin-left: 10px; font-size: 0.75rem;">{{ __('Monthly Sub') }}</span>
                            @if($project->subscription_status === 'active')
                                <span class="badge badge-present" style="font-size: 0.75rem;">{{ __('Active') }}</span>
                            @else
                                <span class="badge badge-absent" style="font-size: 0.75rem;">{{ __('Stopped') }}</span>
                            @endif
                        @else
                            <span class="badge badge-secondary" style="margin-left: 10px; font-size: 0.75rem;">{{ __('One Time') }}</span>
                        @endif
                    </h4>
                    <span style="font-size: 0.85rem; color: var(--text-secondary);">
                        {{ __('Status:') }} 
                        @php
                            $statusColor = match($project->status) {
                                'completed' => 'var(--green)',
                                'in_progress' => 'var(--orange)',
                                'cancelled' => 'var(--red)',
                                default => 'var(--text-secondary)'
                            };
                        @endphp
                        <strong style="color: {{ $statusColor }}">{{ __(ucfirst(str_replace('_', ' ', $project->status))) }}</strong>
                        | {{ __('Agreed Price:') }} <strong>{{ number_format($project->agreed_price, 2) }}</strong>
                    </span>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="openPaymentModal({{ $project->id }})" style="font-size: 0.85rem; padding: 6px 12px; background: rgba(0, 158, 253, 0.1); color: var(--color-primary); border: 1px solid var(--color-primary);">
                        <i class="ri-add-line"></i> {{ __('Payment') }}
                    </button>
                </div>
            </div>

            <!-- Payments List -->
            <div style="padding: 20px;">
                @if($project->payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>{{ __('Title / Description') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Paid At') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->title }}</td>
                                        <td style="font-weight: bold;">{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->due_date->format('Y-m-d') }}</td>
                                        <td>
                                            @if($payment->status === 'paid')
                                                <span class="badge badge-present">{{ __('Paid') }}</span>
                                            @elseif($payment->status === 'overdue')
                                                <span class="badge badge-absent">{{ __('Overdue') }}</span>
                                            @else
                                                <span class="badge badge-excused">{{ __('Pending') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '-' }}</td>
                                        <td>
                                            <!-- Edit Payment Status Form -->
                                            <form action="{{ route('admin.projects.payments.update', $payment->id) }}" method="POST" style="display:inline-flex; gap: 5px;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="title" value="{{ $payment->title }}">
                                                <input type="hidden" name="amount" value="{{ $payment->amount }}">
                                                <input type="hidden" name="due_date" value="{{ $payment->due_date->format('Y-m-d') }}">
                                                
                                                <select name="status" class="form-control" style="padding: 4px; font-size: 0.8rem; width: auto;" onchange="this.form.submit()">
                                                    <option value="pending" {{ $payment->status == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                                    <option value="paid" {{ $payment->status == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                                                    <option value="overdue" {{ $payment->status == 'overdue' ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                                                </select>
                                            </form>
                                            
                                            <form id="delete-payment-form-{{ $payment->id }}" action="{{ route('admin.projects.payments.destroy', $payment->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn-icon btn-danger" style="padding: 4px;" title="{{ __('Delete') }}" onclick="showGlobalConfirmPopup('delete-payment-form-{{ $payment->id }}', '{{ __('Are you sure you want to delete this payment?') }}')">
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
                    <div style="text-align: center; color: var(--text-secondary); padding: 10px;">
                        <i class="ri-money-dollar-circle-line" style="font-size: 2rem; display: block; margin-bottom: 5px;"></i>
                        {{ __('No payments or installments added yet.') }}
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card" style="text-align: center; padding: 40px;">
            <i class="ri-briefcase-4-line" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
            <p style="color: var(--text-secondary); margin: 0;">{{ __('This client has no projects yet.') }}</p>
        </div>
    @endforelse

    <!-- Add Payment Modal -->
    <div id="paymentModal" class="custom-modal-overlay" style="display: none;">
        <div class="custom-modal" style="text-align: left; max-width: 500px;">
            <h4 class="modal-title" style="margin-bottom: 20px;">{{ __('Add Payment / Installment') }}</h4>
            <form id="addPaymentForm" method="POST" action="">
                @csrf
                <div class="form-group">
                    <label class="form-label">{{ __('Service / Project') }} <span style="color: red;">*</span></label>
                    <select id="modalProjectSelect" class="form-control" onchange="updatePaymentFormAction(this.value)">
                        @foreach($client->projects as $p)
                            <option value="{{ $p->id }}">{{ $p->service->name }} ({{ $p->billing_type === 'monthly' ? __('Monthly') : __('One Time') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Title / Description') }} <span style="color: red;">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="{{ __('e.g. First Installment, Monthly Sub') }}" autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Amount') }} <span style="color: red;">*</span></label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Due Date') }} <span style="color: red;">*</span></label>
                    <input type="date" name="due_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-control">
                        <option value="pending">{{ __('Pending') }}</option>
                        <option value="paid">{{ __('Paid (Now)') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('paymentModal').style.display = 'none'">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Payment') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Set Password Modal -->
    <div id="passwordModal" class="custom-modal-overlay" style="display: none;">
        <div class="custom-modal" style="text-align: left; max-width: 400px;">
            <h4 class="modal-title" style="margin-bottom: 20px;">{{ __('Set Portal Password') }}</h4>
            <form method="POST" action="{{ route('admin.clients.password', $client->id) }}">
                @csrf
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 15px;">
                    {{ __('Set a password for the client to log into their portal.') }}
                </p>
                <div class="form-group">
                    <label class="form-label">{{ __('New Password') }} <span style="color: red;">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('passwordModal').style.display = 'none'">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Password') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updatePaymentFormAction(projectId) {
            const form = document.getElementById('addPaymentForm');
            form.action = '/admin/projects/' + projectId + '/payments';
        }

        function openPaymentModal(projectId = null) {
            const select = document.getElementById('modalProjectSelect');
            if (projectId) {
                select.value = projectId;
            } else if (select.options.length > 0) {
                select.selectedIndex = 0;
            }
            
            if(select.value) {
                updatePaymentFormAction(select.value);
                document.getElementById('paymentModal').style.display = 'flex';
            } else {
                alert('{{ __("This client has no projects yet.") }}');
            }
        }
    </script>
@endsection
