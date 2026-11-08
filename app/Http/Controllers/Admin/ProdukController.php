<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index(Request $r)
    {
        return Produk::when($r->q, fn ($q, $s) => $q->where('produk_nama', 'like', "%{$s}%"))
            ->orderByDesc('produk_id')->paginate(min(max((int) $r->input('per_page', 20), 1), 100));
    }

    public function show(Produk $produk) { return $produk; }

    public function store(Request $r)
    {
        $d = $r->validate([
            'produk_nama' => 'required|string|max:150',
            'produk_harga' => 'required|numeric|min:0',
            'produk_stok' => 'required|integer|min:0',
            'gambar' => 'nullable|image|max:4096',
        ]);
        if ($r->hasFile('gambar')) {
            $d['produk_gambar'] = $r->file('gambar')->store('produk', 'public');
        }
        unset($d['gambar']);
        return response()->json(Produk::create($d)->fresh(), 201);
    }

    // Kirim sebagai POST + field _method=PUT bila memakai upload gambar (multipart).
    public function update(Request $r, Produk $produk)
    {
        $d = $r->validate([
            'produk_nama' => 'sometimes|required|string|max:150',
            'produk_harga' => 'sometimes|required|numeric|min:0',
            'produk_stok' => 'sometimes|required|integer|min:0',
            'gambar' => 'nullable|image|max:4096',
        ]);
        if ($r->hasFile('gambar')) {
            if ($produk->produk_gambar) Storage::disk('public')->delete($produk->produk_gambar);
            $d['produk_gambar'] = $r->file('gambar')->store('produk', 'public');
        }
        unset($d['gambar']);
        $produk->update($d);
        return $produk;
    }

    public function destroy(Produk $produk)
    {
        if (DetailPesanan::where('produk_id', $produk->produk_id)->exists()) {
            return response()->json(['message' => 'Produk sudah pernah dipesan, tidak bisa dihapus. Set stok ke 0 saja.'], 409);
        }
        if ($produk->produk_gambar) Storage::disk('public')->delete($produk->produk_gambar);
        $produk->delete();
        return response()->noContent();
    }
}
