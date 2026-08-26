<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;

// Halaman Analytics & Grafik
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/analytics', [DashboardController::class, 'index'])->name('analytics');

// Halaman Manajemen Data & Tabel
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders/import', [OrderController::class, 'import'])->name('import');
Route::put('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');
Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
Route::delete('/orders-reset-all', [OrderController::class, 'destroyAll'])->name('orders.destroyAll');