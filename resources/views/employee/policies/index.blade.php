@extends('layouts.app')

@section('title', __('Company Policies'))
@section('page_header', __('Company Policies'))

@section('sidebar_menu')
    @include('layouts.sidebar_employee')
@endsection

@section('content')
    <!-- Intro Card -->
    <div class="card" style="background: linear-gradient(135deg, rgba(99, 123, 255, 0.08) 0%, rgba(33, 200, 246, 0.08) 100%); border-color: rgba(99, 123, 255, 0.2); margin-bottom: 24px; padding: 28px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="background: rgba(99, 123, 255, 0.15); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--indigo); font-size: 24px; box-shadow: 0 4px 12px rgba(99, 123, 255, 0.15);">
                <i class="ri-book-open-line"></i>
            </div>
            <div>
                <h2 style="font-family: var(--font-title); font-size: 1.4rem; margin: 0 0 4px 0; font-weight: 700;">
                    {{ __('Company Policy & Guidelines') }}
                </h2>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem; line-height: 1.5;">
                    {{ __('Please review the company policies below. These rules and guidelines govern our daily operations and professional standards.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Policies List -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        @php
            // Assign gradient accent colors based on iteration index to make it look premium
            $accents = [
                ['var(--color-primary)', 'var(--color-primary-light)', 'rgba(50, 138, 241, 0.1)'],
                ['var(--green)', 'var(--teal)', 'rgba(26, 171, 139, 0.1)'],
                ['var(--yellow)', '#FFE5A3', 'rgba(255, 199, 60, 0.1)'],
                ['var(--purple)', 'var(--violet)', 'rgba(139, 96, 237, 0.1)'],
                ['var(--red)', '#FF8B94', 'rgba(236, 69, 79, 0.1)'],
                ['var(--cyan)', '#9CE5FF', 'rgba(33, 200, 246, 0.1)']
            ];
        @endphp

        @forelse($policies as $idx => $policy)
            @php
                $accent = $accents[$idx % count($accents)];
                $isAr = app()->getLocale() === 'ar';
                $title = $isAr ? $policy->title_ar : $policy->title;
                $content = $isAr ? $policy->content_ar : $policy->content;
            @endphp
            <div class="card policy-card" style="margin-bottom: 0; padding: 24px; border-inline-start: 6px solid {{ $accent[0] }}; background: linear-gradient(135deg, var(--bg-card) 0%, {{ $accent[2] }} 100%); transition: var(--transition);" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='{{ $accent[1] }}'; this.style.boxShadow='0 8px 30px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='none'; this.style.borderColor='{{ $accent[0] }}'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.15)';">
                <div style="display: flex; align-items: flex-start; gap: 18px;">
                    <!-- Policy Icon with Glowing Background -->
                    <div style="background-color: {{ $accent[2] }}; color: {{ $accent[0] }}; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 4px 10px {{ $accent[2] }}; border: 1px solid rgba(255, 255, 255, 0.05);">
                        <i class="{{ $policy->icon }}"></i>
                    </div>

                    <!-- Policy Content -->
                    <div style="flex-grow: 1;">
                        <h3 style="font-family: var(--font-title); font-size: 1.15rem; font-weight: 700; margin: 0 0 10px 0; color: var(--text-primary);">
                            {{ $title }}
                        </h3>
                        <div style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; white-space: pre-line;">
                            {{ $content }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card" style="text-align: center; padding: 50px 20px; color: var(--text-secondary);">
                <i class="ri-article-line" style="font-size: 3rem; opacity: 0.4; display: block; margin-bottom: 12px;"></i>
                <span>{{ __('No policies have been published yet. Please check back later.') }}</span>
            </div>
        @endforelse
    </div>
@endsection
