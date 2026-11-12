<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * GET /api/admin/laporan/penjualan
     *   tanpa parameter                                       -> seluruh periode (rincian per bulan)
     *   ?tahun=2026                                           -> setahun (rincian per bulan)
     *   ?tahun=2026&bulan=11                                  -> laporan bulanan (rincian per hari)
     *   ?tanggal_mulai=2026-11-01&tanggal_selesai=2026-11-07  -> mingguan/rentang tanggal (rincian per hari)
     * Hanya pesanan LUNAS dan tidak dibatalkan yang dihitung sebagai penjualan.
     * (Memakai fungsi tanggal MySQL: DATE, DATE_FORMAT.)
     */
    public function penjualan(Request $r)
    {
        $d = $r->validate([
            'tahun' => 'required_with:bulan|nullable|integer|min:2000|max:2100',
            'bulan' => 'nullable|integer|between:1,12',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $base = fn () => Pesanan::query()
            ->where('pesanan_status_pembayaran', 'lunas')
            ->where('pesanan_status', '!=', 'dibatalkan')
            ->when($d['tahun'] ?? null, fn ($q, $y) => $q->whereYear('pesanan_tanggal', $y))
            ->when($d['bulan'] ?? null, fn ($q, $m) => $q->whereMonth('pesanan_tanggal', $m))
            ->when($d['tanggal_mulai'] ?? null, fn ($q, $start) => $q->whereDate('pesanan_tanggal', '>=', $start))
            ->when($d['tanggal_selesai'] ?? null, fn ($q, $end) => $q->whereDate('pesanan_tanggal', '<=', $end));

        $ringkasan = $base()->selectRaw('COUNT(*) as jumlah_pesanan, COALESCE(SUM(pesanan_total),0) as total_pendapatan')->first();

        $perHari = isset($d['bulan']) || isset($d['tanggal_mulai']) || isset($d['tanggal_selesai']);

        $rincian = $perHari
            ? $base()->selectRaw('DATE(pesanan_tanggal) as periode, COUNT(*) as jumlah_pesanan, SUM(pesanan_total) as pendapatan')
                ->groupBy('periode')->orderBy('periode')->get()
            : $base()->selectRaw("DATE_FORMAT(pesanan_tanggal, '%Y-%m') as periode, COUNT(*) as jumlah_pesanan, SUM(pesanan_total) as pendapatan")
                ->groupBy('periode')->orderBy('periode')->get();

        $terlaris = DetailPesanan::join('produk', 'produk.produk_id', '=', 'detail_pesanan.produk_id')
            ->whereIn('detail_pesanan.pesanan_id', $base()->select('pesanan_id'))
            ->selectRaw('produk.produk_id, produk.produk_nama, SUM(detail_pesanan.detail_jumlah) as terjual, SUM(detail_pesanan.detail_subtotal) as omzet')
            ->groupBy('produk.produk_id', 'produk.produk_nama')
            ->orderByDesc('terjual')->limit(5)->get();

        return [
            'filter' => [
                'tahun' => $d['tahun'] ?? null,
                'bulan' => $d['bulan'] ?? null,
                'tanggal_mulai' => $d['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $d['tanggal_selesai'] ?? null,
            ],
            'ringkasan' => [
                'jumlah_pesanan' => (int) $ringkasan->jumlah_pesanan,
                'total_pendapatan' => (string) $ringkasan->total_pendapatan,
                'total_item_terjual' => (int) DetailPesanan::whereIn('pesanan_id', $base()->select('pesanan_id'))->sum('detail_jumlah'),
            ],
            'rincian' => $rincian,
            'produk_terlaris' => $terlaris,
            'pesanan' => $base()->with('pelanggan:pelanggan_id,nama')->orderByDesc('pesanan_tanggal')->paginate(20),
        ];
    }
}