<?php

use App\Http\Controllers\EmployeeController;
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
| Admin Area
| - Role 1 (Admin) và Role 2 (HR) được phép truy cập
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:1,2'])->prefix('admin')->group(function () {
    
    Route::get('/dashboard', function () {
        return view('admin.welcome'); // Sửa lại trỏ đúng về view dashboard admin hiện có
    })->name('admin.dashboard');

    // Quản lý Nhân viên (CRUD)
    Route::resource('employees', EmployeeController::class);

});

/*
|--------------------------------------------------------------------------
| User Area (Khu vực dành cho Employee)
| - Chỉ Role 3 (Employee) được phép truy cập
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:3'])->prefix('user')->group(function () {
    
    Route::get('/dashboard', function () {
        // Giả sử view user.dashboard đã tồn tại theo yêu cầu
        return view('user.dashboard');
    })->name('user.dashboard');

});

