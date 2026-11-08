<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

/** Data Pelanggan: Read + Search saja (sesuai sitemap). */
class PelangganController extends Controller
{
    public function index(Request $r)
    {
        return Pelanggan::withCount('pesanan')
            ->when($r->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            }))
            ->orderByDesc('pelanggan_id')->paginate(min(max((int) $r->input('per_page', 20), 1), 100));
    }

    public function show(Pelanggan $pelanggan)
    {
        return $pelanggan->loadCount('pesanan')->load(['pesanan' => fn ($q) => $q->orderByDesc('pesanan_id')->limit(10)]);
    }
}
