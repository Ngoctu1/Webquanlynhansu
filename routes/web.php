<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AccountInvitationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
});

Route::get('/account/activate/{token}', [AccountInvitationController::class, 'show'])
    ->middleware('throttle:30,1')->name('account.activate');
Route::post('/account/activate/{token}', [AccountInvitationController::class, 'activate'])
    ->middleware('throttle:10,1')->name('account.activate.submit');

/*
|--------------------------------------------------------------------------
| Admin Area
| - Role 1 (Admin) và Role 2 (HR) được phép truy cập
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:1,2'])->prefix('admin')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    
    Route::get('/dashboard', function () {
        return view('admin.welcome'); // Sửa lại trỏ đúng về view dashboard admin hiện có
    })->name('admin.dashboard');

    // Quản lý Nhân viên (CRUD)
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{employee}/account', [UserController::class, 'store'])
        ->middleware('throttle:10,1')->name('employees.account.store');

});

/*
|--------------------------------------------------------------------------
| User Area (Khu vực dành cho Employee)
| - Chỉ Role 3 (Employee) được phép truy cập
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:3'])->prefix('user')->group(function () {
    Route::get('/', fn () => redirect()->route('user.dashboard'));
    
    Route::get('/dashboard', function () {
        // Giả sử view user.dashboard đã tồn tại theo yêu cầu
        return view('user.profile.index'); // Sửa lại trỏ đúng về view dashboard user hiện có
    })->name('user.dashboard');

});
