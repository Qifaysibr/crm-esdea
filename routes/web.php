<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\EarningsController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\MarketingAssetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Lead Management
    Route::resource('leads', LeadController::class);
    Route::get('/leads/template/download', [LeadController::class, 'downloadTemplate'])->name('leads.template.download');
    Route::post('/leads/import', [LeadController::class, 'import'])->name('leads.import');

    // Quotations
    Route::resource('quotations', QuotationController::class);
    Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'generatePDF'])->name('quotations.pdf');
    Route::get('/quotations/{quotation}/download', [QuotationController::class, 'downloadPDF'])->name('quotations.download');
    Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert');

    // Invoices
    Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);
    Route::patch('/invoices/{invoice}/payment', [InvoiceController::class, 'updatePayment'])->name('invoices.payment');

    // Earnings
    Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings.index');
    Route::get('/earnings/export', [EarningsController::class, 'export'])->name('earnings.export');

    // Team Monitor & User Management (Admin only)
    Route::middleware(['role:manager,admin'])->group(function () {
        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::get('/team/export', [TeamController::class, 'export'])->name('team.export');
        Route::get('/team/{user}', [TeamController::class, 'show'])->name('team.show');
        
        // Users (Admin only usually, but manager might need access depending on policy)
        Route::resource('users', UserController::class);
    });

    // Marketing Assets
    Route::get('/marketing-assets', [MarketingAssetController::class, 'index'])->name('marketing-assets.index');
    Route::post('/marketing-assets', [MarketingAssetController::class, 'store'])->name('marketing-assets.store');
    Route::get('/marketing-assets/{asset}/download', [MarketingAssetController::class, 'download'])->name('marketing-assets.download');
    Route::delete('/marketing-assets/{asset}', [MarketingAssetController::class, 'destroy'])->name('marketing-assets.destroy');
});

require __DIR__.'/auth.php';
