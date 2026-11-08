<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;

class DashboardController extends Controller
{
    public function index()
    {
        $lunasAktif = fn () => Pesanan::where('pesanan_status_pembayaran', 'lunas')
            ->where('pesanan_status', '!=', 'dibatalkan');

        return [
            'total_produk' => Produk::count(),
            'total_pelanggan' => Pelanggan::count(),
            'total_pesanan' => Pesanan::count(),
            'pesanan_menunggu' => Pesanan::where('pesanan_status', 'menunggu')->count(),
            'pembayaran_menunggu_verifikasi' => Pesanan::where('pesanan_status_pembayaran', 'menunggu_verifikasi')->count(),
            'pendapatan_bulan_ini' => (string) $lunasAktif()
                ->whereYear('pesanan_tanggal', now()->year)
                ->whereMonth('pesanan_tanggal', now()->month)
                ->sum('pesanan_total'),
            'stok_menipis' => Produk::where('produk_stok', '<=', 5)->orderBy('produk_stok')->limit(10)
                ->get(['produk_id', 'produk_nama', 'produk_stok']),
            'pesanan_terbaru' => Pesanan::with('pelanggan:pelanggan_id,nama')
                ->orderByDesc('pesanan_id')->limit(5)->get(),
        ];
    }
}
