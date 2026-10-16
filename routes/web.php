<?php

use App\Http\Controllers\{AccountInvitationController, AttendanceController, AuditLogController, AuthController,
    ContractController, DashboardController, DepartmentController, EmployeeController, PayrollDetailController,
    PayrollPeriodController, PositionController, UserController, WorkScheduleController};
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/account/activate/{token}', [AccountInvitationController::class, 'show'])->middleware('throttle:20,1')->name('account.activate');
Route::post('/account/activate/{token}', [AccountInvitationController::class, 'activate'])->middleware('throttle:10,1')->name('account.activate.submit');

Route::prefix('admin')->middleware(['auth', 'role:HR,Director,Admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    // Define static create paths before resource show paths.
    Route::middleware('role:HR,Director,Admin')->group(function () {
        Route::resource('employees', EmployeeController::class)->except(['index', 'show']);
        Route::resource('departments', DepartmentController::class)->except(['index', 'show']);
        Route::resource('positions', PositionController::class)->except(['index', 'show']);
        Route::patch('departments/{department}/status', [DepartmentController::class, 'updateStatus'])->name('departments.status');
        Route::patch('positions/{position}/status', [PositionController::class, 'updateStatus'])->name('positions.status');
        Route::post('employees/{employee}/account', [UserController::class, 'store'])->middleware('throttle:5,1')->name('employees.account.store');
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::resource('contracts', ContractController::class)->only(['create', 'store', 'edit', 'update']);
        Route::post('contracts/{contract}/submit', [ContractController::class, 'submit'])->name('contracts.submit');
        Route::resource('attendances', AttendanceController::class)->only(['create', 'store', 'edit', 'update']);
        Route::resource('payrolls', PayrollPeriodController::class)->only(['create', 'store']);
        Route::post('payrolls/{payroll}/calculate', [PayrollPeriodController::class, 'calculate'])->name('payrolls.calculate');
        Route::post('payrolls/{payroll}/submit', [PayrollPeriodController::class, 'submit'])->name('payrolls.submit');
        Route::resource('schedules', WorkScheduleController::class)->only(['create', 'store', 'edit', 'update']);
    });
    Route::middleware('role:Director,Admin')->group(function () {
        Route::post('contracts/{contract}/approve', [ContractController::class, 'approve'])->name('contracts.approve');
        Route::post('contracts/{contract}/reject', [ContractController::class, 'reject'])->name('contracts.reject');
        Route::post('payrolls/{payroll}/approve', [PayrollPeriodController::class, 'approve'])->name('payrolls.approve');
        Route::post('payrolls/{payroll}/reject', [PayrollPeriodController::class, 'reject'])->name('payrolls.reject');
    });
    Route::resource('employees', EmployeeController::class)->only(['index', 'show']);
    Route::resource('departments', DepartmentController::class)->only(['index', 'show']);
    Route::resource('positions', PositionController::class)->only(['index', 'show']);
    Route::resource('contracts', ContractController::class)->only(['index', 'show']);
    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::resource('payrolls', PayrollPeriodController::class)->only(['index', 'show']);
    Route::get('schedules', [WorkScheduleController::class, 'index'])->name('schedules.index');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->middleware('role:Admin')->name('users.role');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
});

Route::prefix('user')->middleware(['auth', 'role:HR,Director,Employee,Admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('user.attendances'));
    Route::get('/dashboard', [DashboardController::class, 'profile'])->name('user.dashboard');
    Route::get('/contracts', [DashboardController::class, 'contracts'])->name('user.contracts');
    Route::get('/attendances', [AttendanceController::class, 'mine'])->name('user.attendances');
    Route::get('/attendances/history', [AttendanceController::class, 'history'])->name('user.attendances.history');
    Route::post('/attendances/check-in', [AttendanceController::class, 'checkIn'])->name('user.attendances.check-in');
    Route::post('/attendances/check-out', [AttendanceController::class, 'checkOut'])->name('user.attendances.check-out');
    Route::get('/payrolls', [PayrollDetailController::class, 'index'])->name('user.payrolls');
});
