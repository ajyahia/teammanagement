<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Team Management System</title>
    
    <!-- Remix Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="/css/style.css">
    
    @yield('styles')
</head>
<body dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="app-container">
        
        <!-- Sidebar -->
        <aside class="sidebar" id="app-sidebar">
            <div class="sidebar-brand">
                <i class="ri-team-fill"></i>
                <span>{{ __('Team Manage') }}</span>
            </div>
            
            <ul class="sidebar-menu">
                @yield('sidebar_menu')
            </ul>
            
            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST" class="logout-form">
                    @csrf
                    <button type="submit">
                        <i class="ri-logout-box-r-line"></i>
                        <span>{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Main Wrapper -->
        <div class="main-wrapper">
            
            <!-- Topbar / Header -->
            <header class="topbar">
                <div class="topbar-left">
                    <!-- Menu Toggle for Mobile -->
                    <button id="sidebar-toggle" class="sidebar-toggle">
                        <i class="ri-menu-line"></i>
                    </button>
                    <div class="page-title">
                        <h1>@yield('page_header', 'Dashboard')</h1>
                    </div>
                </div>
                
                <div class="topbar-right">
                    <!-- Language Switcher Toggle -->
                    <div class="language-switcher">
                        @if(app()->getLocale() === 'en')
                            <a href="{{ request()->url() }}/ar{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="lang-btn">
                                <i class="ri-global-line"></i>
                                <span>العربية</span>
                            </a>
                        @else
                            <a href="{{ request()->url() }}/en{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="lang-btn">
                                <i class="ri-global-line"></i>
                                <span>English</span>
                            </a>
                        @endif
                    </div>

                    <a href="{{ auth()->user()->role === 'admin' ? '/admin/employees/' . auth()->user()->id . '/edit' : '/employee/profile' }}" class="user-profile" style="text-decoration: none;">
                        <div class="user-info">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-role">{{ __(ucfirst(auth()->user()->role)) }}</span>
                        </div>
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </a>
                </div>
            </header>
            
            <!-- Content Body -->
            <main class="content-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="ri-checkbox-circle-fill"></i>
                        <span>{{ __(session('success')) }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="ri-error-warning-fill"></i>
                        <span>{{ __(session('error')) }}</span>
                    </div>
                @endif
                
                @yield('content')
            </main>
            
        </div>
    </div>

    <!-- Core Javascript -->
    <script>
        document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
            document.getElementById('app-sidebar').classList.toggle('open');
        });
        
        // Hide sidebar on clicking outside in mobile view
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('app-sidebar');
            const toggleBtn = document.getElementById('sidebar-toggle');
            if (window.innerWidth <= 991 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // Global Custom Dropdowns handler
        document.addEventListener('DOMContentLoaded', function () {
            const dropdowns = document.querySelectorAll('.custom-dropdown');
            
            dropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('.dropdown-trigger');
                const menu = dropdown.querySelector('.dropdown-menu');
                const hiddenInput = dropdown.querySelector('.dropdown-value-input');
                const selectedText = dropdown.querySelector('.selected-text');
                const items = dropdown.querySelectorAll('.dropdown-item');
                
                // Open/Close dropdown on trigger click
                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    
                    // Close all other dropdowns first
                    dropdowns.forEach(otherDropdown => {
                        if (otherDropdown !== dropdown) {
                            otherDropdown.classList.remove('open');
                            otherDropdown.closest('.attendance-row-card')?.classList.remove('open-dropdown');
                        }
                    });
                    
                    const isOpen = dropdown.classList.toggle('open');
                    const card = dropdown.closest('.attendance-row-card');
                    if (card) {
                        if (isOpen) {
                            card.classList.add('open-dropdown');
                        } else {
                            card.classList.remove('open-dropdown');
                        }
                    }
                });
                
                // Handle item selection
                items.forEach(item => {
                    item.addEventListener('click', function (e) {
                        e.stopPropagation();
                        
                        const val = item.getAttribute('data-value');
                        const text = item.textContent.trim();
                        
                        // Update text and hidden input value
                        selectedText.textContent = text;
                        if (hiddenInput) {
                            hiddenInput.value = val;
                        }
                        
                        // Update selected styling
                        items.forEach(i => i.classList.remove('selected'));
                        item.classList.add('selected');
                        
                        // Close dropdown
                        dropdown.classList.remove('open');
                        dropdown.closest('.attendance-row-card')?.classList.remove('open-dropdown');
                    });
                });
            });
            
            // Close dropdowns on clicking outside
            document.addEventListener('click', function () {
                dropdowns.forEach(dropdown => {
                    dropdown.classList.remove('open');
                    dropdown.closest('.attendance-row-card')?.classList.remove('open-dropdown');
                });
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
