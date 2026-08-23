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

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="margin: 0; font-family: var(--font-title); font-weight: 700;">{{ __('Projects') }}</h3>
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
                        | <span style="color: var(--green);">{{ __('Paid:') }} <strong>{{ number_format($project->paid_amount, 2) }}</strong></span>
                        | <span style="color: var(--orange);">{{ __('Due:') }} <strong>{{ number_format($project->due_amount, 2) }}</strong></span>
                        <br><br>
                        {{ __('Assigned Employees:') }} 
                        @if($project->employees->count() > 0)
                            @foreach($project->employees as $emp)
                                <span class="badge badge-present" style="font-size: 0.75rem; padding: 2px 6px;">{{ $emp->name }}</span>
                            @endforeach
                        @else
                            <span class="badge badge-absent" style="font-size: 0.75rem; padding: 2px 6px;">{{ __('Unassigned') }}</span>
                        @endif
                    </span>
                </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card" style="text-align: center; padding: 40px;">
            <i class="ri-briefcase-4-line" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
            <p style="color: var(--text-secondary); margin: 0;">{{ __('This client has no projects yet.') }}</p>
        </div>
    @endforelse



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


@endsection
