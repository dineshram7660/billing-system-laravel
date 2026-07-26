<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmailSendController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\EstimateMailController;
use App\Http\Controllers\GstReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SalaryDetailController;
use App\Http\Controllers\SalarySlipController;
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
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::get('employees/{employee}/salary-details', [SalaryDetailController::class, 'index'])->name('employees.salary-details.index');
    Route::post('employees/{employee}/salary-details', [SalaryDetailController::class, 'store'])->name('employees.salary-details.store');
    Route::delete('employees/{employee}/salary-details/{salary_detail}', [SalaryDetailController::class, 'destroy'])->name('employees.salary-details.destroy');

    Route::get('bills/{bill}/print', [BillController::class, 'print'])->name('bills.print');
    Route::get('bills/{bill}/pdf', [BillController::class, 'pdf'])->name('bills.pdf');
    Route::resource('bills', BillController::class)->except(['show']);

    Route::get('estimates/{estimate}/print', [EstimateController::class, 'print'])->name('estimates.print');
    Route::get('estimates/{estimate}/pdf', [EstimateController::class, 'pdf'])->name('estimates.pdf');
    Route::get('estimates/{estimate}/excel', [EstimateController::class, 'excel'])->name('estimates.excel');
    Route::get('estimates/{estimate}/mail', [EstimateMailController::class, 'create'])->name('estimates.mail.create');
    Route::post('estimates/{estimate}/mail', [EstimateMailController::class, 'store'])->name('estimates.mail.store');

    Route::get('email-sends', [EmailSendController::class, 'index'])->name('email-sends.index');
    Route::resource('estimates', EstimateController::class)->except(['show']);

    Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::resource('quotations', QuotationController::class)->except(['show']);

    Route::get('gst-report', [GstReportController::class, 'index'])->name('gst-report.index');
    Route::get('gst-report/view', [GstReportController::class, 'show'])->name('gst-report.show');
    Route::get('gst-report/pdf', [GstReportController::class, 'pdf'])->name('gst-report.pdf');

    Route::get('salary-slips/data', [SalarySlipController::class, 'data'])->name('salary-slips.data');
    Route::get('salary-slips/{salary_slip}/print', [SalarySlipController::class, 'print'])->name('salary-slips.print');
    Route::get('salary-slips/{salary_slip}/pdf', [SalarySlipController::class, 'pdf'])->name('salary-slips.pdf');
    Route::resource('salary-slips', SalarySlipController::class)->except(['show']);

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
