<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\RoleController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::middleware(['check-access'])->group(function () {
    Route::view('/', 'layouts.landing')->name('landing');
});

Route::middleware('sso')->group(function () {
    Route::get('/dashboard', [HomeController::class, '__invoke'])->name('dashboard');
    Route::get('/dashboard/read', [HomeController::class, 'read'])->name('dashboard.read');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    //MENU ADMINISTRATOR
    
    Route::get('/permission', [PermissionController::class, '__invoke'])->name('permission');
    Route::get('/permission/read', [PermissionController::class, 'read'])->name('permission.read');
    Route::post('/permission/write', [PermissionController::class, 'write'])->name('permission.write');

    Route::get('/unit', [UnitController::class, '__invoke'])->name('unit');
    Route::get('/unit/read', [UnitController::class, 'read'])->name('unit.read');
    Route::post('/unit/write', [UnitController::class, 'write'])->name('unit.write');

    Route::get('/role', [RoleController::class, '__invoke'])->name('role');
    Route::get('/role/read', [RoleController::class, 'read'])->name('role.read');
    Route::post('/role/write', [RoleController::class, 'write'])->name('role.write');

    Route::get('/user', [UserController::class, '__invoke'])->name('user');
    Route::get('/user/read', [UserController::class, 'read'])->name('user.read');
    Route::post('/user/write', [UserController::class, 'write'])->name('user.write');


    Route::fallback(function () {
        $vue = "<not-found/>";
        return response()->view('layouts.antd', compact('vue'), 404);
    });
});