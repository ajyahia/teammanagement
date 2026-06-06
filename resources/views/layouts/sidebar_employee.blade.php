<li class="sidebar-item {{ request()->is('employee/dashboard*') ? 'active' : '' }}">
    <a href="/employee/dashboard">
        <i class="ri-dashboard-line"></i>
        <span>{{ __('Dashboard') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('employee/attendance*') ? 'active' : '' }}">
    <a href="/employee/attendance">
        <i class="ri-calendar-todo-line"></i>
        <span>{{ __('My Attendance') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('employee/profile*') ? 'active' : '' }}">
    <a href="/employee/profile">
        <i class="ri-user-line"></i>
        <span>{{ __('My Profile') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('employee/salary*') ? 'active' : '' }}">
    <a href="/employee/salary">
        <i class="ri-money-dollar-circle-line"></i>
        <span>{{ __('My Salary') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('employee/policies*') ? 'active' : '' }}">
    <a href="/employee/policies">
        <i class="ri-file-text-line"></i>
        <span>{{ __('Company Policy') }}</span>
    </a>
</li>
