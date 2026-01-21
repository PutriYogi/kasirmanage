<?php

// use App\Http\Controllers\AdminAuthController;
// use App\Http\Controllers\AdminUserController;

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminKategoriController;
use App\Http\Controllers\AdminProdukController;
use App\Http\Controllers\AdminTransaksiController;
use App\Http\Controllers\AdminTransaksiDetailController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminReportingController;
use App\Http\Controllers\AdminPengeluaranController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/login', [AdminAuthController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login/do', [AdminAuthController::class, 'doLogin'])->middleware('guest');
Route::get('logout', [AdminAuthController::class, 'logout'])->middleware('auth');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin/dashboard');
    }
    return redirect('/login');
});


Route::prefix('/admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    Route::get('/transaksi/export', [AdminTransaksiController::class, 'export']); // Route untuk export data
    Route::get('/transaksi/{id}/print', [AdminTransaksiController::class, 'print'])->name('transaksi.print'); // Route untuk print struk
    Route::get('/transaksi/detail/selesai/{id}', [AdminTransaksiDetailController::class, 'done']);
    Route::get('/transaksi/detail/delete', [AdminTransaksiDetailController::class, 'delete']);
    Route::get('/transaksi/detail/delete-by-produk', [AdminTransaksiDetailController::class, 'deleteByProduk']);
    Route::get('/transaksi/get-totals/{id}', [AdminTransaksiDetailController::class, 'getTotals']);
    Route::get('/transaksi/{id}/get-details', [AdminTransaksiDetailController::class, 'getDetails']);
    // Route::post('/admin/transaksi/detail/selesai/{id}', [AdminTransaksiController::class, 'selesai']);
    Route::delete('/admin/transaksi/{id}', [AdminTransaksiController::class, 'destroy']);
    Route::post('/transaksi/detail/selesai/{id}', [AdminTransaksiController::class, 'selesai'])->name('transaksi.selesai');
    Route::post('/transaksi/detail/create', [AdminTransaksiDetailController::class, 'create']);
    Route::resource('/transaksi', AdminTransaksiController::class);
    Route::resource('/produk', AdminProdukController::class);
    Route::resource('/kategori', AdminKategoriController::class);

    // Routes untuk Reporting
    Route::get('/reporting', [AdminReportingController::class, 'index']);
    Route::get('/reporting/export', [AdminReportingController::class, 'export']);
    
    // Routes untuk Pengeluaran
    Route::get('/pengeluaran/export', [AdminPengeluaranController::class, 'export'])->name('admin.pengeluaran.export');
    Route::resource('/pengeluaran', AdminPengeluaranController::class, [
        'as' => 'admin'
    ]);
    
    Route::resource('/user', AdminUserController::class);
});
