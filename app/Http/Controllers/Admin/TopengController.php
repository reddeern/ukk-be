<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topeng;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TopengController extends Controller
{
    public function index(Request $r)
    {
        return Topeng::when($r->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('topeng_nama', 'like', "%{$s}%")->orWhere('sifat_topeng', 'like', "%{$s}%");
            }))
            ->orderBy('topeng_nama')->get();
    }

    public function show(Topeng $topeng) { return $topeng->load('videos'); }

    public function store(Request $r)
    {
        $d = $r->validate([
            'topeng_nama' => 'required|string|max:150',
            'sifat_topeng' => 'required|string',
            'topeng_deskripsi' => 'required|string',
            'gambar' => 'nullable|image|max:4096',
        ]);
        if ($r->hasFile('gambar')) {
            $d['topeng_gambar'] = $r->file('gambar')->store('topeng', 'public');
        }
        unset($d['gambar']);
        return response()->json(Topeng::create($d), 201);
    }

    public function update(Request $r, Topeng $topeng)
    {
        $d = $r->validate([
            'topeng_nama' => 'sometimes|required|string|max:150',
            'sifat_topeng' => 'sometimes|required|string',
            'topeng_deskripsi' => 'sometimes|required|string',
            'gambar' => 'nullable|image|max:4096',
        ]);
        if ($r->hasFile('gambar')) {
            if ($topeng->topeng_gambar) Storage::disk('public')->delete($topeng->topeng_gambar);
            $d['topeng_gambar'] = $r->file('gambar')->store('topeng', 'public');
        }
        unset($d['gambar']);
        $topeng->update($d);
        return $topeng;
    }

    public function destroy(Topeng $topeng)
    {
        if ($topeng->topeng_gambar) Storage::disk('public')->delete($topeng->topeng_gambar);
        $topeng->delete(); // video & pilihan jawaban terkait ikut terhapus (cascade)
        return response()->noContent();
    }
}
