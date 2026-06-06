<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminSalaryController;
use App\Http\Controllers\Admin\AdminPolicyController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeProfileController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Http\Controllers\Employee\EmployeeSalaryController;
use App\Http\Controllers\Employee\EmployeePolicyController;

// Redirect root to dashboard (which handles role-based redirects) or login
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin' 
            ? redirect('/admin/dashboard') 
            : redirect('/employee/dashboard');
    }
    return redirect('/login');
});

// Guest Routes (Authentication)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin Group
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Employee Management (CRUD)
        Route::resource('employees', AdminUserController::class)->except(['show']);
        
        // Attendance Management
        Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AdminAttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/mark-all-present', [AdminAttendanceController::class, 'markAllPresent'])->name('attendance.mark_all_present');
        Route::delete('/attendance/{attendance}', [AdminAttendanceController::class, 'destroy'])->name('attendance.destroy');
        Route::get('/attendance/months', [AdminAttendanceController::class, 'months'])->name('attendance.months');
        Route::get('/attendance/details/{employee}/{month}/{year}', [AdminAttendanceController::class, 'details'])->name('attendance.details');

        // System Settings
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/weekly', [AdminSettingsController::class, 'saveWeekly'])->name('settings.weekly');
        Route::post('/settings/specific', [AdminSettingsController::class, 'addSpecific'])->name('settings.specific');
        Route::delete('/settings/specific/{holiday}', [AdminSettingsController::class, 'deleteSpecific'])->name('settings.specific.delete');

        // Salaries & Adjustments
        Route::get('/salaries', [AdminSalaryController::class, 'index'])->name('salaries.index');
        Route::post('/salaries', [AdminSalaryController::class, 'store'])->name('salaries.store');
        Route::get('/salaries/{user}/details', [AdminSalaryController::class, 'details'])->name('salaries.details');
        Route::delete('/salaries/adjustments/{id}', [AdminSalaryController::class, 'destroy'])->name('salaries.adjustments.destroy');
        Route::post('/salaries/{user}/pay', [AdminSalaryController::class, 'pay'])->name('salaries.pay');
        Route::delete('/salaries/{user}/unpay', [AdminSalaryController::class, 'unpay'])->name('salaries.unpay');

        // Company Policies CRUD
        Route::resource('policies', AdminPolicyController::class)->except(['show']);
    });

    // Employee Group
    Route::middleware('employee')->prefix('employee')->name('employee.')->group(function () {
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        
        // View Own Attendance
        Route::get('/attendance', [EmployeeAttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/details/{month}/{year}', [EmployeeAttendanceController::class, 'details'])->name('attendance.details');
        
        // Manage Profile
        Route::get('/profile', [EmployeeProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [EmployeeProfileController::class, 'update'])->name('profile.update');

        // View Own Salary Details
        Route::get('/salary', [EmployeeSalaryController::class, 'index'])->name('salary.index');

        // View Company Policies
        Route::get('/policies', [EmployeePolicyController::class, 'index'])->name('policies.index');
    });
});

// Fallback Route for locale switching
Route::fallback(function (\Illuminate\Http\Request $request) {
    $path = $request->getPathInfo();
    
    $locale = null;
    if (str_ends_with($path, '/ar') || $path === '/ar') {
        $locale = 'ar';
        $cleanPath = $path === '/ar' ? '/' : substr($path, 0, -3);
    } elseif (str_ends_with($path, '/en') || $path === '/en') {
        $locale = 'en';
        $cleanPath = $path === '/en' ? '/' : substr($path, 0, -3);
    }

    if ($locale !== null) {
        session(['locale' => $locale]);
        
        $queryString = $request->getQueryString();
        $redirectUrl = $cleanPath . ($queryString ? '?' . $queryString : '');
        return redirect($redirectUrl);
    }

    abort(404);
});
