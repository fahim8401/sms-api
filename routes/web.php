<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResellerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    
    // Redirect to appropriate dashboard based on role
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'reseller') {
        return redirect()->route('reseller.dashboard');
    }
    
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::post('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users/{id}/credit', [AdminController::class, 'addCredit'])->name('users.credit');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/gateways/{id}', [AdminController::class, 'updateGateway'])->name('gateways.update');
    Route::post('/gateways', [AdminController::class, 'createGateway'])->name('gateways.create');
});

// Reseller Routes
Route::middleware(['auth'])->prefix('reseller')->name('reseller.')->group(function () {
    Route::get('/dashboard', [ResellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/sub-users', [ResellerController::class, 'subUsers'])->name('sub-users');
    Route::post('/sub-users', [ResellerController::class, 'createSubUser'])->name('sub-users.create');
    Route::post('/sub-users/{id}/transfer', [ResellerController::class, 'transferCredit'])->name('sub-users.transfer');
    Route::get('/invoices', [ResellerController::class, 'invoices'])->name('invoices');
    Route::post('/invoices/generate', [ResellerController::class, 'generateInvoice'])->name('invoices.generate');
});

require __DIR__.'/auth.php';
