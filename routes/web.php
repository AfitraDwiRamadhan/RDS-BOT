<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\BotController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\SystemController;
use App\Http\Controllers\Web\GroupController;
use App\Http\Controllers\Web\ItemController;
use App\Http\Controllers\Web\PromoController;

Route::get('/', function () { return redirect()->route('dashboard'); });

Route::get('/dashboard', [BotController::class, 'dashboard'])->name('dashboard');

Route::prefix('bots')->name('bots.')->group(function () {
    Route::get('/', [BotController::class, 'index'])->name('index');
    Route::post('/store', [BotController::class, 'store'])->name('store');
    Route::post('/{bot}/update-template', [BotController::class, 'updateTemplate'])->name('update-template');
    Route::delete('/{bot}', [BotController::class, 'destroy'])->name('destroy');
});

Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
});

Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
});

Route::prefix('groups')->name('groups.')->group(function () {
    Route::get('/', [GroupController::class, 'index'])->name('index');
    Route::post('/store', [GroupController::class, 'store'])->name('store');
    Route::post('/update-type', [GroupController::class, 'updateType'])->name('update-type');
    Route::delete('/{group}', [GroupController::class, 'destroy'])->name('destroy');
});

Route::prefix('items')->name('items.')->group(function () {
    Route::get('/', [ItemController::class, 'index'])->name('index');
    Route::post('/store', [ItemController::class, 'store'])->name('store');
    Route::post('/update-status', [ItemController::class, 'updateStatus'])->name('update-status');
    Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');
});

Route::prefix('promos')->name('promos.')->group(function () {
    Route::get('/', [PromoController::class, 'index'])->name('index');
    Route::post('/store', [PromoController::class, 'store'])->name('store');
    Route::post('/update-status', [PromoController::class, 'updateStatus'])->name('update-status');
    Route::delete('/{promo}', [PromoController::class, 'destroy'])->name('destroy');
});

// RUTE BARU: System & Monitoring
Route::prefix('system')->name('system.')->group(function () {
    Route::get('/monitoring', [SystemController::class, 'monitoring'])->name('monitoring');
    Route::get('/backup-sessions', [SystemController::class, 'backupSessions'])->name('backup');
});