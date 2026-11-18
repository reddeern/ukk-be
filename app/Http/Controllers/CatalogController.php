<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\PilihanJawaban;
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
        // topeng_id sengaja tidak dipilih agar jawaban tidak "bocor" ke frontend
        return Kuis::select('kuis_id', 'pertanyaan_teks')
            ->with('pilihan:pilihan_id,kuis_id,pilihan_jawaban')
            ->get();
    }

    public function kuisHasil(Request $r)
    {
        $d = $r->validate([
            'pilihan' => 'required|array|min:1',
            'pilihan.*' => 'required|integer|exists:pilihan_jawaban,pilihan_id',
        ]);

        $ids = array_map('intval', $d['pilihan']);

        $pilihan = PilihanJawaban::query()
            ->whereIn('pilihan_id', $ids)
            ->get()
            ->keyBy('pilihan_id');

        if ($pilihan->count() !== count(array_unique($ids))) {
            return response()->json(['message' => 'Sebagian pilihan jawaban tidak ditemukan.'], 422);
        }

        // Skor dihitung per topeng, mengikuti urutan jawaban agar tie-break deterministik.
        $skor = [];
        foreach ($ids as $id) {
            $topengId = (int) $pilihan[$id]->topeng_id;
            $skor[$topengId] = ($skor[$topengId] ?? 0) + 1;
        }

        $topengId = null;
        $max = 0;
        foreach ($skor as $id => $jumlah) {
            if ($jumlah > $max) {
                $max = $jumlah;
                $topengId = (int) $id;
            }
        }

        $topeng = $topengId ? Topeng::find($topengId) : null;

        if (! $topeng) {
            return response()->json(['message' => 'Topeng hasil kuis tidak ditemukan.'], 404);
        }

        // Data lama dari admin mungkin masih memakai nama kelompok.
        $kelompok = [
            'Punakawan' => 'Prasonto',
            'Sabrang' => 'Bapang',
            'Prabu Klana' => 'Klana Sewandana',
        ];

        if (isset($kelompok[$topeng->topeng_nama])) {
            $topeng = Topeng::where('topeng_nama', $kelompok[$topeng->topeng_nama])->first() ?? $topeng;
        }

        return $topeng;
    }

    public function videoIndex()
    {
        return VideoAi::with('topeng:topeng_id,topeng_nama')->where('video_status', 'selesai')
            ->orderByDesc('video_id')->paginate(12);
    }
}
