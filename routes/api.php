<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\KuisController;
use App\Http\Controllers\PesananController;
use Illuminate\Support\Facades\Route;

// ---- Publik ----
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('admin/login', [AuthController::class, 'adminLogin']);

Route::get('produk', [CatalogController::class, 'produkIndex']);
Route::get('produk/{produk}', [CatalogController::class, 'produkShow']);
Route::get('topeng', [CatalogController::class, 'topengIndex']);
Route::get('topeng/{topeng}', [CatalogController::class, 'topengShow']);
Route::get('kuis', [CatalogController::class, 'kuisIndex']);
Route::get('video', [CatalogController::class, 'videoIndex']);

// ---- Pelanggan ----
Route::middleware(['auth:sanctum', 'role:pelanggan'])->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('kuis/hasil', [KuisController::class, 'hasil']);

    Route::get('keranjang', [KeranjangController::class, 'show']);
    Route::post('keranjang/item', [KeranjangController::class, 'tambah']);
    Route::put('keranjang/item/{item}', [KeranjangController::class, 'ubah']);
    Route::delete('keranjang/item/{item}', [KeranjangController::class, 'hapus']);

    Route::get('pesanan', [PesananController::class, 'index']);
    Route::post('pesanan', [PesananController::class, 'store']);
    Route::get('pesanan/{pesanan}', [PesananController::class, 'show']);
    Route::post('pesanan/{pesanan}/bukti', [PesananController::class, 'uploadBukti']);
    Route::post('pesanan/{pesanan}/batal', [PesananController::class, 'batal']);
});

// ---- Admin ----
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('dashboard', [Admin\DashboardController::class, 'index']);

    Route::apiResource('produk', Admin\ProdukController::class);
    Route::apiResource('topeng', Admin\TopengController::class);
    Route::apiResource('kuis', Admin\KuisController::class)->parameters(['kuis' => 'kuis']);
    Route::apiResource('video', Admin\VideoAiController::class)->parameters(['video' => 'video']);

    Route::get('pesanan', [Admin\PesananController::class, 'index']);
    Route::post('pesanan', [Admin\PesananController::class, 'store']);
    Route::get('pesanan/{pesanan}', [Admin\PesananController::class, 'show']);
    Route::put('pesanan/{pesanan}', [Admin\PesananController::class, 'update']);
    Route::delete('pesanan/{pesanan}', [Admin\PesananController::class, 'destroy']);

    Route::get('pelanggan', [Admin\PelangganController::class, 'index']);
    Route::get('pelanggan/{pelanggan}', [Admin\PelangganController::class, 'show']);

    Route::get('laporan/penjualan', [Admin\LaporanController::class, 'penjualan']);
});
