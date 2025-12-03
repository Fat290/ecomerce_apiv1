<?php

use App\Http\Controllers\Admin\AdminViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/demo/shop-onboarding', 'demo.shop-onboarding')->name('demo.shop-onboarding');

// Admin Panel Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminViewController::class, 'login'])->name('admin.login');
    Route::get('/dashboard', [AdminViewController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminViewController::class, 'users'])->name('admin.users');
    Route::get('/banners', [AdminViewController::class, 'banners'])->name('admin.banners');
    Route::get('/vouchers', [AdminViewController::class, 'vouchers'])->name('admin.vouchers');
    Route::get('/categories', [AdminViewController::class, 'categories'])->name('admin.categories');
    Route::get('/pending-shops', [AdminViewController::class, 'pendingShops'])->name('admin.pending-shops');
});
