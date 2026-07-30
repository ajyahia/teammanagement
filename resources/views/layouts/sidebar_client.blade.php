<li class="sidebar-item {{ request()->is('client/dashboard*') ? 'active' : '' }}">
    <a href="/client/dashboard">
        <i class="ri-dashboard-line"></i>
        <span>{{ __('Dashboard') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('client/projects*') ? 'active' : '' }}">
    <a href="/client/projects">
        <i class="ri-briefcase-4-line"></i>
        <span>{{ __('My Projects') }}</span>
    </a>
</li>
<!-- In the future, we can add a specific Payments history page -->
