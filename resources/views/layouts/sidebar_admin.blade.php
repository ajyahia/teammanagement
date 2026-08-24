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

<!-- CRM & Projects -->
<li class="sidebar-item {{ request()->is('admin/clients*') ? 'active' : '' }}">
    <a href="/admin/clients">
        <i class="ri-contacts-book-2-fill"></i>
        <span>{{ __('Clients') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('admin/services*') ? 'active' : '' }}">
    <a href="/admin/services">
        <i class="ri-customer-service-2-fill"></i>
        <span>{{ __('Services') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('admin/projects*') ? 'active' : '' }}">
    <a href="/admin/projects">
        <i class="ri-briefcase-4-fill"></i>
        <span>{{ __('Projects') }}</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('admin/expenses*') ? 'active' : '' }}">
    <a href="/admin/expenses">
        <i class="ri-money-dollar-circle-line"></i>
        <span>المصروفات</span>
    </a>
</li>
<li class="sidebar-item {{ request()->is('admin/reports*') ? 'active' : '' }}">
    <a href="/admin/reports">
        <i class="ri-bar-chart-box-fill"></i>
        <span>{{ __('Reports & Analytics') }}</span>
    </a>
</li>
