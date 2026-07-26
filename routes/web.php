<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('designations', DesignationController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('employees', EmployeeController::class)->except(['show']);

    // Not Route::resource: "sub-admins" would imply implicit binding to a
    // SubAdmin model, but this manages App\Models\User (the admin table).
    Route::get('sub-admins', [SubAdminController::class, 'index'])->name('sub-admins.index');
    Route::get('sub-admins/create', [SubAdminController::class, 'create'])->name('sub-admins.create');
    Route::post('sub-admins', [SubAdminController::class, 'store'])->name('sub-admins.store');
    Route::get('sub-admins/{user}/edit', [SubAdminController::class, 'edit'])->name('sub-admins.edit');
    Route::put('sub-admins/{user}', [SubAdminController::class, 'update'])->name('sub-admins.update');
    Route::put('sub-admins/{user}/password', [SubAdminController::class, 'updatePassword'])->name('sub-admins.password');
    Route::put('sub-admins/{user}/access', [SubAdminController::class, 'updateAccess'])->name('sub-admins.access');
    Route::delete('sub-admins/{user}', [SubAdminController::class, 'destroy'])->name('sub-admins.destroy');
});

require __DIR__.'/auth.php';
