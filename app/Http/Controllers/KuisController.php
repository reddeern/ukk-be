<?php

namespace App\Http\Controllers;

use App\Models\PilihanJawaban;
use App\Models\Topeng;
use Illuminate\Http\Request;

class KuisController extends Controller
{
    /**
     * Body: { "jawaban": [pilihan_id, pilihan_id, ...] }
     * Topeng yang paling banyak ditunjuk jawaban = hasil.
     */
    public function hasil(Request $r)
    {
        $d = $r->validate([
            'jawaban' => 'required|array|min:1',
            'jawaban.*' => 'integer|exists:pilihan_jawaban,pilihan_id',
        ]);

        $skor = PilihanJawaban::whereIn('pilihan_id', $d['jawaban'])
            ->selectRaw('topeng_id, COUNT(*) as total')
            ->groupBy('topeng_id')
            ->orderByDesc('total')
            ->get();

        $topeng = Topeng::with(['videos' => fn ($q) => $q->where('video_status', 'selesai')])
            ->find($skor->first()->topeng_id);

        return ['topeng' => $topeng, 'skor' => $skor];
    }
}
