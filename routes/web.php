<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Customer as Cust;
use App\Http\Controllers\Merchant\{DashboardController, CategoryController, ProductController,
    OrderController, ReportController, SettingController, AccountController};
use Illuminate\Support\Facades\Route;

/* ===================== SISI PELANGGAN (tanpa login) ===================== */
Route::name('customer.')->group(function () {

    // Halaman publik
    Route::get('/', [Cust\HomeController::class, 'index'])->name('home');
    Route::get('/menu', [Cust\MenuController::class, 'index'])->name('menu');
    Route::get('/menu/{product}', [Cust\MenuController::class, 'show'])->name('menu.show');
    Route::get('/lokasi', [Cust\LocationController::class, 'index'])->name('location');

    // Keranjang
    Route::get('/keranjang', [Cust\CartController::class, 'index'])->name('cart');
    Route::post('/keranjang', [Cust\CartController::class, 'store'])->name('cart.store');
    Route::patch('/keranjang/{key}', [Cust\CartController::class, 'update'])
        ->where('key', '[a-f0-9]{32}')->name('cart.update');
    Route::delete('/keranjang/{key}', [Cust\CartController::class, 'destroy'])
        ->where('key', '[a-f0-9]{32}')->name('cart.destroy');

    // Checkout dan pelacakan pesanan lewat tautan (token)
    Route::get('/checkout', [Cust\CheckoutController::class, 'create'])->name('checkout');
    Route::post('/checkout', [Cust\CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pesanan/{token}', [Cust\OrderController::class, 'show'])
        ->where('token', '[A-Za-z0-9]{40}')->name('order');
    Route::get('/riwayat', [Cust\OrderController::class, 'history'])->name('history');
    Route::get('/riwayat/status', [Cust\OrderController::class, 'historyStatus'])->name('history.status');
    Route::delete('/riwayat', [Cust\OrderController::class, 'clearHistory'])->name('history.clear');
    Route::delete('/riwayat/{token}', [Cust\OrderController::class, 'forgetHistory'])
        ->where('token', '[A-Za-z0-9]{40}')->name('history.forget');    
    Route::post('/pesanan/{token}/batal', [Cust\OrderController::class, 'cancel'])
        ->where('token', '[A-Za-z0-9]{40}')->name('order.cancel');
    Route::get('/pesanan/{token}/status', [Cust\OrderController::class, 'status'])
        ->where('token', '[A-Za-z0-9]{40}')->name('order.status');
    Route::post('/pesanan/{token}/ulasan', [Cust\ReviewController::class, 'store'])
        ->where('token', '[A-Za-z0-9]{40}')->name('reviews.store');
});

/* ===================== SISI MERCHANT ===================== */
Route::middleware('guest')->group(function () {
    Route::get('/merchant/login', [LoginController::class, 'show'])->name('login');
    Route::post('/merchant/login', [LoginController::class, 'store']);
});
Route::post('/merchant/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')->name('logout');

Route::prefix('merchant')->name('merchant.')
    ->middleware(['auth', 'pengelola'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('produk', ProductController::class)
        ->parameters(['produk' => 'product'])->except('show')->names('products');
    Route::resource('kategori', CategoryController::class)
        ->parameters(['kategori' => 'category'])
        ->only(['index', 'store', 'update', 'destroy'])->names('categories');

    Route::post('/pesanan/aksi', [OrderController::class, 'bulk'])->name('orders.bulk');
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/feed', [OrderController::class, 'feed'])->name('orders.feed');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/terima', [OrderController::class, 'accept'])->name('orders.accept');
    Route::post('/pesanan/{order}/tolak', [OrderController::class, 'reject'])->name('orders.reject');
    Route::post('/pesanan/{order}/selesai', [OrderController::class, 'complete'])->name('orders.complete');

    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/ekspor', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/laporan/{date}', [ReportController::class, 'show'])
        ->where('date', '\d{4}-\d{2}-\d{2}')->name('reports.show');

    Route::get('/pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/pengaturan', [SettingController::class, 'update'])->name('settings.update');

    Route::post('/pengaturan/akun', [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/pengaturan/akun/{user}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/pengaturan/akun/{user}', [AccountController::class, 'destroy'])->name('accounts.destroy');
});