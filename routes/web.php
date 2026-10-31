<?php

use App\Http\Controllers\{AccountInvitationController, AuditLogController, AuthController,
    ContractController, DashboardController, DepartmentController, EmployeeController,
    LeaveRequestController, PositionController, UserController};
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
        Route::post('contracts/{contract}/signed-document', [ContractController::class, 'uploadSignedDocument'])->middleware('role:HR')->name('contracts.signed-document.store');
    });
    Route::middleware('role:Director,Admin')->group(function () {
        Route::post('contracts/{contract}/approve', [ContractController::class, 'approve'])->name('contracts.approve');
        Route::post('contracts/{contract}/reject', [ContractController::class, 'reject'])->name('contracts.reject');
    });
    Route::resource('employees', EmployeeController::class)->only(['index', 'show']);
    Route::resource('departments', DepartmentController::class)->only(['index', 'show']);
    Route::resource('positions', PositionController::class)->only(['index', 'show']);
    Route::resource('contracts', ContractController::class)->only(['index', 'show']);
    Route::middleware('role:HR')->group(function () {
        Route::get('leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
        Route::get('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
        Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
        Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    });
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->middleware('role:Admin')->name('users.role');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
});

Route::prefix('user')->middleware(['auth', 'role:HR,Director,Employee,Admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('user.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'profile'])->name('user.dashboard');
    Route::get('/contracts', [DashboardController::class, 'contracts'])->name('user.contracts');
    Route::get('/leave-requests', [LeaveRequestController::class, 'mine'])->name('user.leave-requests.index');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('user.leave-requests.store');
    Route::patch('/leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('user.leave-requests.cancel');
});

Route::middleware(['auth', 'role:HR,Director,Employee,Admin'])->group(function () {
    Route::get('/employee-photos/{employee}', [EmployeeController::class, 'photo'])->name('employees.photo');
    Route::get('/contract-documents/{contract}', [ContractController::class, 'signedDocument'])->name('contracts.signed-document.show');
});
