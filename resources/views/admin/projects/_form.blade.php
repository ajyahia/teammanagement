{{-- Shared project form fields for create & edit. $project is null on create. --}}
@php
    $project = $project ?? null;
    $selectedEmployees = old('employee_ids', $project ? $project->employees->pluck('id')->toArray() : []);
@endphp

@if($errors->any())
    <div class="project-form-errors">
        <i class="ri-error-warning-line"></i>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="project-form-grid">
    {{-- Row 1: who & what --}}
    <div class="form-group">
        <label class="form-label">{{ __('Select Client') }} <span class="required-star">*</span></label>
        <select name="client_id" class="form-control" required>
            <option value="">-- {{ __('Choose Client') }} --</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id', $project?->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">{{ __('Select Service') }} <span class="required-star">*</span></label>
        <select name="service_id" class="form-control" required>
            <option value="">-- {{ __('Choose Service') }} --</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" {{ old('service_id', $project?->service_id) == $service->id ? 'selected' : '' }}>{{ $service->name }} ({{ number_format($service->default_price, 2) }})</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">{{ __('Status') }} <span class="required-star">*</span></label>
        <select name="status" class="form-control" required>
            @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                <option value="{{ $value }}" {{ old('status', $project?->status) == $value ? 'selected' : '' }}>{{ __($label) }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">{{ __('Billing Type') }} <span class="required-star">*</span></label>
        <select name="billing_type" class="form-control" required id="billing_type_select">
            @foreach(['one_time' => 'One Time Payment (Fixed)', 'monthly' => 'Monthly Subscription', 'yearly' => 'Annual Subscription', 'per_page' => 'Per Page (Books)'] as $value => $label)
                <option value="{{ $value }}" {{ old('billing_type', $project?->billing_type) == $value ? 'selected' : '' }}>{{ __($label) }}</option>
            @endforeach
        </select>
    </div>

    {{-- Row 2: team & pricing --}}
    <div class="form-group span-2">
        <label class="form-label">{{ __('Assign To Employees') }}</label>
        <select name="employee_ids[]" id="employee_ids" class="form-control select2-multiple" multiple>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" data-services="{{ json_encode($emp->services->pluck('id')) }}" {{ in_array($emp->id, (array) $selectedEmployees) ? 'selected' : '' }}>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group" id="subscription_status_group" style="display: none;">
        <label class="form-label">{{ __('Subscription Status') }}</label>
        <select name="subscription_status" class="form-control">
            <option value="active" {{ old('subscription_status', $project?->subscription_status) == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
            <option value="stopped" {{ old('subscription_status', $project?->subscription_status) == 'stopped' ? 'selected' : '' }}>{{ __('Stopped') }}</option>
        </select>
    </div>

    <div class="form-group per-page-field" style="display: none;">
        <label class="form-label">{{ __('Number of Pages') }} <span class="required-star">*</span></label>
        <input type="number" id="page_count" name="page_count" class="form-control" value="{{ old('page_count', $project?->page_count) }}" min="1">
    </div>

    <div class="form-group per-page-field" style="display: none;">
        <label class="form-label">{{ __('Price per Page') }} <span class="required-star">*</span></label>
        <input type="number" step="0.01" id="price_per_page" name="price_per_page" class="form-control" value="{{ old('price_per_page', $project?->price_per_page) }}" min="0">
    </div>

    <div class="form-group">
        <label class="form-label">{{ __('Agreed Price (Client Pays)') }} <span class="required-star">*</span></label>
        <input type="number" step="0.01" min="0" name="agreed_price" class="form-control" required value="{{ old('agreed_price', $project?->agreed_price ?? '0.00') }}">
    </div>

    {{-- Row 3: payment & dates --}}
    @if(!$project)
        {{-- On create, money already received becomes a paid installment (editable later) --}}
        <div class="form-group">
            <label class="form-label" title="{{ __('Recorded as a paid installment that you can edit later from the project page.') }}">
                {{ __('Amount Paid (By Client)') }} <i class="ri-information-line"></i>
            </label>
            <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="{{ old('paid_amount', '0.00') }}">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('Payment Date') }}</label>
            <input type="date" name="paid_at" class="form-control" value="{{ old('paid_at', date('Y-m-d')) }}">
        </div>
    @elseif((float) $project->paid_amount > 0)
        <div class="form-group">
            <label class="form-label" title="{{ __('Legacy payments. New payments should be added as installments below.') }}">
                {{ __('Amount Paid (Legacy)') }} <i class="ri-information-line"></i>
            </label>
            <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" required value="{{ old('paid_amount', $project->paid_amount) }}">
        </div>
    @else
        <input type="hidden" name="paid_amount" value="0">
    @endif

    <div class="form-group">
        <label class="form-label" id="start_date_label">{{ __('Start Date') }}</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $project?->start_date?->format('Y-m-d')) }}">
    </div>

    <div class="form-group" id="deadline_group">
        <label class="form-label">{{ __('Deadline') }}</label>
        <input type="date" name="deadline" class="form-control" value="{{ old('deadline', $project?->deadline?->format('Y-m-d')) }}">
    </div>

    {{-- Row 4: notes --}}
    <div class="form-group span-full">
        <label class="form-label">{{ __('Project Notes / Requirements') }}</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $project?->notes) }}</textarea>
    </div>
</div>

<div class="project-form-actions">
    <button type="submit" class="btn btn-primary">
        <i class="ri-save-line"></i> {{ $project ? __('Update Project') : __('Create Project') }}
    </button>
    <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
        {{ __('Cancel') }}
    </a>
</div>

<style>
    /* Compact 4-column layout so the whole form fits on one screen */
    .project-form-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px 16px;
        margin-bottom: 18px;
    }
    .project-form-grid .form-group {
        gap: 6px;
        min-width: 0;
    }
    .project-form-grid .span-2 {
        grid-column: span 2;
    }
    .project-form-grid .span-full {
        grid-column: 1 / -1;
    }
    .project-form-grid .form-label {
        font-size: 0.8rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .project-form-grid .form-label i {
        color: var(--text-secondary);
        cursor: help;
    }
    .project-form-grid .form-control {
        width: 100%;
        box-sizing: border-box;
        padding: 9px 12px;
        font-size: 0.9rem;
    }
    .project-form-grid textarea.form-control {
        resize: vertical;
        min-height: 60px;
    }
    @media (max-width: 991px) {
        .project-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 576px) {
        .project-form-grid {
            grid-template-columns: minmax(0, 1fr);
        }
        .project-form-grid .span-2 {
            grid-column: 1 / -1;
        }
    }
    .required-star {
        color: var(--red, #ec454f);
    }
    .project-form-errors {
        display: flex;
        gap: 10px;
        padding: 10px 14px;
        margin-bottom: 16px;
        border-radius: var(--radius-md);
        background: rgba(236, 69, 79, 0.1);
        border: 1px solid rgba(236, 69, 79, 0.3);
        color: var(--red, #ec454f);
    }
    .project-form-errors ul {
        margin: 0;
        padding-inline-start: 18px;
    }
    .project-form-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const billingSelect = document.getElementById('billing_type_select');
        const subGroup = document.getElementById('subscription_status_group');
        const perPageFields = document.querySelectorAll('.per-page-field');
        const agreedPriceInput = document.querySelector('input[name="agreed_price"]');
        const pageCountInput = document.getElementById('page_count');
        const pricePerPageInput = document.getElementById('price_per_page');
        const startDateLabel = document.getElementById('start_date_label');
        const deadlineGroup = document.getElementById('deadline_group');

        function toggleBillingFields() {
            // Subscription
            if (billingSelect.value === 'monthly' || billingSelect.value === 'yearly') {
                subGroup.style.display = '';
                startDateLabel.innerHTML = '{{ __('Service Payment Date') }}';
                deadlineGroup.style.display = 'none';
            } else {
                subGroup.style.display = 'none';
                startDateLabel.innerHTML = '{{ __('Start Date') }}';
                deadlineGroup.style.display = '';
            }

            // Per Page
            const isPerPage = billingSelect.value === 'per_page';
            perPageFields.forEach(el => el.style.display = isPerPage ? '' : 'none');
            agreedPriceInput.readOnly = isPerPage;
            agreedPriceInput.style.background = isPerPage ? 'var(--bg-hover)' : '';
            if (isPerPage) calculateAgreedPrice();
        }

        function calculateAgreedPrice() {
            if (billingSelect.value === 'per_page') {
                const pages = parseFloat(pageCountInput.value) || 0;
                const price = parseFloat(pricePerPageInput.value) || 0;
                agreedPriceInput.value = (pages * price).toFixed(2);
            }
        }

        billingSelect.addEventListener('change', toggleBillingFields);
        pageCountInput.addEventListener('input', calculateAgreedPrice);
        pricePerPageInput.addEventListener('input', calculateAgreedPrice);

        toggleBillingFields();

        // Employee Service Filtering
        const serviceSelect = document.querySelector('select[name="service_id"]');
        const employeeSelect = document.getElementById('employee_ids');

        function filterEmployees() {
            const serviceId = parseInt(serviceSelect.value);

            Array.from(employeeSelect.options).forEach(option => {
                if (!serviceId) {
                    option.disabled = false;
                    return;
                }

                const services = JSON.parse(option.getAttribute('data-services') || '[]');
                option.disabled = !services.includes(serviceId);
            });
            // Re-initialize Select2 to apply the disabled states
            $(employeeSelect).select2({
                placeholder: "{{ __('Select options...') }}",
                allowClear: true,
                width: '100%'
            });
        }

        if (serviceSelect) {
            serviceSelect.addEventListener('change', function() {
                filterEmployees();
                // Drop employees that don't offer the newly selected service
                Array.from(employeeSelect.options).forEach(opt => {
                    if (opt.disabled) opt.selected = false;
                });
                $(employeeSelect).trigger('change');
            });
            filterEmployees();
        }
    });
</script>
