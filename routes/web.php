<?php

use App\Http\Controllers\{
    AccountInvitationController,
    AuditLogController,
    AuthController,
    ChangeRequestController,
    ContractController,
    DashboardController,
    DepartmentController,
    EmployeeController,
    EmployeeDocumentController,
    EmployeePortalController,
    LeaveRequestController,
    PositionController,
    UserController
};
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Hỗ trợ cả /account/activate?email=...&token=... và /account/activate/{token}
Route::get('/account/activate/{token?}', [AccountInvitationController::class, 'show'])->middleware('throttle:20,1')->name('account.activate');
Route::post('/account/activate/{token?}', [AccountInvitationController::class, 'activate'])->middleware('throttle:10,1')->name('account.activate.submit');

Route::prefix('admin')->middleware(['auth', 'active_user', 'role:HR,Director,Admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    // Define static create paths before resource show paths.
    Route::middleware('role:HR,Director,Admin')->group(function () {
        Route::resource('employees', EmployeeController::class)->except(['index', 'show']);
        Route::post('employees/{employee}/resign', [EmployeeController::class, 'resign'])->name('employees.resign');
        Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('employees.documents.store');
        Route::delete('documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('documents.destroy');
        Route::resource('departments', DepartmentController::class)->except(['index', 'show']);
        Route::resource('positions', PositionController::class)->except(['index', 'show']);
        Route::patch('departments/{department}/status', [DepartmentController::class, 'updateStatus'])->name('departments.status');
        Route::patch('positions/{position}/status', [PositionController::class, 'updateStatus'])->name('positions.status');
        Route::post('employees/{employee}/account', [UserController::class, 'store'])->middleware('throttle:5,1')->name('employees.account.store');
        Route::post('users/{user}/send-activation', [UserController::class, 'sendActivationMail'])->middleware('throttle:5,1')->name('users.send-activation');
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
    });

    // Admin KHÔNG được thao tác (tạo/sửa/duyệt/từ chối) hợp đồng & nghỉ bù
    Route::middleware('role:HR,Director')->group(function () {
        Route::resource('contracts', ContractController::class)->only(['create', 'store', 'edit', 'update']);
        Route::post('contracts/{contract}/signed-document', [ContractController::class, 'uploadSignedDocument'])->name('contracts.signed-document.store');
        Route::post('leave-requests/retroactive', [LeaveRequestController::class, 'storeRetroactive'])->name('leave-requests.retroactive.store');
    });

    Route::middleware('role:Director')->group(function () {
        Route::post('employees/{employee}/approve', [EmployeeController::class, 'approve'])->name('employees.approve');
        Route::post('employees/{employee}/reject', [EmployeeController::class, 'reject'])->name('employees.reject');
        Route::post('departments/{department}/approve', [DepartmentController::class, 'approve'])->name('departments.approve');
        Route::post('departments/{department}/reject', [DepartmentController::class, 'reject'])->name('departments.reject');
        Route::post('positions/{position}/approve', [PositionController::class, 'approve'])->name('positions.approve');
        Route::post('positions/{position}/reject', [PositionController::class, 'reject'])->name('positions.reject');
        Route::post('contracts/{contract}/approve', [ContractController::class, 'approve'])->name('contracts.approve');
        Route::post('contracts/{contract}/reject', [ContractController::class, 'reject'])->name('contracts.reject');

        Route::get('change-requests', [ChangeRequestController::class, 'index'])->name('change-requests.index');
        Route::post('change-requests/{changeRequest}/approve', [ChangeRequestController::class, 'approve'])->name('change-requests.approve');
        Route::post('change-requests/{changeRequest}/reject', [ChangeRequestController::class, 'reject'])->name('change-requests.reject');
    });

    Route::resource('employees', EmployeeController::class)->only(['index', 'show']);
    Route::resource('departments', DepartmentController::class)->only(['index', 'show']);
    Route::resource('positions', PositionController::class)->only(['index', 'show']);
    Route::resource('contracts', ContractController::class)->only(['index', 'show']);

    Route::middleware('role:HR,Director,Admin')->group(function () {
        Route::get('leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
        Route::get('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
        Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
        Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    });

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->middleware('role:Admin')->name('users.role');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('role:Director,Admin')->name('audit.index');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->middleware('role:Director,Admin')->name('audit.show');
});

Route::prefix('user')->middleware(['auth', 'active_user', 'role:HR,Director,Employee,Admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('user.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'profile'])->name('user.dashboard');
    Route::put('/password', [AuthController::class, 'changePassword'])->name('user.password.update');
    Route::get('/contracts', [DashboardController::class, 'contracts'])->name('user.contracts');
    Route::get('/leave-requests', [LeaveRequestController::class, 'mine'])->name('user.leave-requests.index');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('user.leave-requests.store');
    Route::patch('/leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('user.leave-requests.cancel');
});

Route::middleware(['auth', 'active_user', 'role:HR,Director,Employee,Admin'])->group(function () {
    Route::get('/my-profile', [EmployeePortalController::class, 'profile'])->name('my-profile');
    Route::get('/employee/profile', [EmployeePortalController::class, 'profile'])->name('employee.profile');
    Route::get('/documents/{document}/download', [EmployeeDocumentController::class, 'download'])->name('documents.download');
    Route::get('/contracts/{contract}/download-pdf', [ContractController::class, 'downloadPdf'])->name('contracts.download-pdf');
    Route::get('/leave-requests/{leaveRequest}/attachment', [LeaveRequestController::class, 'downloadAttachment'])->name('leave-requests.attachment');
    Route::get('/employee-photos/{employee}', [EmployeeController::class, 'photo'])->name('employees.photo');
    Route::get('/contract-documents/{contract}', [ContractController::class, 'signedDocument'])->name('contracts.signed-document.show');
});
