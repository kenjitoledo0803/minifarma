<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\InstallationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- 1. WEB INSTALLER (For Hostinger "One Click" DB Setup) ---
// In production, you might want to protect this or delete it after use.
Route::get('/install-system', [InstallationController::class, 'setupDatabase']);


// --- 2. AUTHENTICATION ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'processLogin']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');


// --- 3. PROTECTED ROUTES ---
Route::middleware(['auth'])->group(function () {
    
    // Redirect root to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS & Sales
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/sale', [POSController::class, 'store'])->name('pos.store');
    Route::get('/pos/print/{sale}', [POSController::class, 'printTicket'])->name('pos.print');

    // Inventory & Import
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/import', [InventoryController::class, 'importExcel'])->name('inventory.import');
    Route::post('/inventory/manual', [InventoryController::class, 'storeManual'])->name('inventory.store');

    // Admin Only
    Route::middleware('can:admin')->group(function () {
        Route::get('/users', [AuthController::class, 'userList'])->name('users.index');
        Route::post('/users/{user}/toggle', [AuthController::class, 'toggleActive'])->name('users.toggle');
    });

});
