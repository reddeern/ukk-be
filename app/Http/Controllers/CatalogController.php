<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\Produk;
use App\Models\Topeng;
use App\Models\VideoAi;
use Illuminate\Http\Request;

/** Endpoint publik (tanpa login): produk, topeng, kuis, video. */
class CatalogController extends Controller
{
    public function produkIndex(Request $r)
    {
        return Produk::query()
            ->when($r->q, fn ($q, $s) => $q->where('produk_nama', 'like', "%{$s}%"))
            ->orderByDesc('produk_id')->paginate(12);
    }

    public function produkShow(Produk $produk) { return $produk; }

    public function topengIndex() { return Topeng::orderBy('topeng_nama')->get(); }

    public function topengShow(Topeng $topeng)
    {
        return $topeng->load(['videos' => fn ($q) => $q->where('video_status', 'selesai')]);
    }

    public function kuisIndex()
    {
        // topeng_id sengaja disembunyikan agar jawaban tidak "bocor" ke frontend
        return Kuis::with('pilihan:pilihan_id,kuis_id,pilihan_jawaban')->get();
    }

    public function videoIndex()
    {
        return VideoAi::with('topeng:topeng_id,topeng_nama')->where('video_status', 'selesai')
            ->orderByDesc('video_id')->paginate(12);
    }
}
