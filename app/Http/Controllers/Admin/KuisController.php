<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kuis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KuisController extends Controller
{
    private function rules(bool $required): array
    {
        $req = $required ? 'required' : 'sometimes';
        return [
            'pertanyaan_teks' => "$req|string",
            'pilihan' => "$req|array|min:2",
            'pilihan.*.pilihan_jawaban' => 'required|string',
            'pilihan.*.topeng_id' => 'required|exists:topeng,topeng_id',
        ];
    }

    public function index(Request $r)
    {
        return Kuis::with('pilihan.topeng:topeng_id,topeng_nama')
            ->when($r->q, fn ($q, $s) => $q->where('pertanyaan_teks', 'like', "%{$s}%"))
            ->get();
    }

    public function show(Kuis $kuis) { return $kuis->load('pilihan.topeng:topeng_id,topeng_nama'); }

    public function store(Request $r)
    {
        $d = $r->validate($this->rules(true));
        $kuis = DB::transaction(function () use ($d) {
            $k = Kuis::create(['pertanyaan_teks' => $d['pertanyaan_teks']]);
            $k->pilihan()->createMany($d['pilihan']);
            return $k;
        });
        return response()->json($kuis->load('pilihan'), 201);
    }

    /** Jika "pilihan" dikirim, seluruh pilihan lama diganti. */
    public function update(Request $r, Kuis $kuis)
    {
        $d = $r->validate($this->rules(false));
        DB::transaction(function () use ($kuis, $d) {
            if (isset($d['pertanyaan_teks'])) $kuis->update(['pertanyaan_teks' => $d['pertanyaan_teks']]);
            if (isset($d['pilihan'])) {
                $kuis->pilihan()->delete();
                $kuis->pilihan()->createMany($d['pilihan']);
            }
        });
        return $kuis->load('pilihan');
    }

    public function destroy(Kuis $kuis)
    {
        $kuis->delete();
        return response()->noContent();
    }
}
