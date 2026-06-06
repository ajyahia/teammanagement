<li class="sidebar-item {{ request()->is('admin/dashboard*') ? 'active' : '' }}">
    <a href="/admin/dashboard">
        <i class="ri-dashboard-fill"></i>
        <span>{{ __('Dashboard') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('admin/employees*') ? 'active' : '' }}">
    <a href="/admin/employees">
        <i class="ri-user-settings-fill"></i>
        <span>{{ __('Employees') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('admin/attendance') ? 'active' : '' }}">
    <a href="/admin/attendance">
        <i class="ri-calendar-check-fill"></i>
        <span>{{ __('Daily Attendance') }}</span>
    </a>
</li>
<li class="sidebar-item {{ (request()->is('admin/attendance/months*') || request()->is('admin/attendance/details*')) ? 'active' : '' }}">
    <a href="/admin/attendance/months">
        <i class="ri-calendar-todo-fill"></i>
        <span>{{ __('Monthly Summary') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('admin/salaries*') ? 'active' : '' }}">
    <a href="/admin/salaries">
        <i class="ri-money-dollar-circle-fill"></i>
        <span>{{ __('Salaries') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('admin/policies*') ? 'active' : '' }}">
    <a href="/admin/policies">
        <i class="ri-file-text-fill"></i>
        <span>{{ __('Company Policy') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
    <a href="/admin/settings">
        <i class="ri-settings-4-fill"></i>
        <span>{{ __('Settings') }}</span>
    </a>
</li>
