<?php

namespace App\Http\Controllers;

use App\Models\DetailKeranjang;
use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    private function cart(Request $r): Keranjang
    {
        return Keranjang::firstOrCreate(['pelanggan_id' => $r->user()->pelanggan_id]);
    }

    private function payload(Keranjang $c)
    {
        $c->load('items.produk');
        $total = $c->items->sum(fn ($i) => $i->jumlah * $i->produk->produk_harga);
        return ['keranjang' => $c, 'total' => number_format($total, 2, '.', '')];
    }

    public function show(Request $r) { return $this->payload($this->cart($r)); }

    public function tambah(Request $r)
    {
        $d = $r->validate(['produk_id' => 'required|exists:produk,produk_id', 'jumlah' => 'required|integer|min:1']);
        $cart = $this->cart($r);
        $produk = Produk::findOrFail($d['produk_id']);

        $item = $cart->items()->firstOrNew(['produk_id' => $produk->produk_id]);
        $qty = ($item->jumlah ?? 0) + $d['jumlah'];
        if ($qty > $produk->produk_stok) {
            return response()->json(['message' => 'Stok tidak mencukupi.'], 422);
        }
        $item->jumlah = $qty;
        $item->save();

        return $this->payload($cart);
    }

    public function ubah(Request $r, DetailKeranjang $item)
    {
        $cart = $this->cart($r);
        abort_unless($item->keranjang_id === $cart->keranjang_id, 404);
        $d = $r->validate(['jumlah' => 'required|integer|min:1']);
        if ($d['jumlah'] > $item->produk->produk_stok) {
            return response()->json(['message' => 'Stok tidak mencukupi.'], 422);
        }
        $item->update($d);
        return $this->payload($cart);
    }

    public function hapus(Request $r, DetailKeranjang $item)
    {
        $cart = $this->cart($r);
        abort_unless($item->keranjang_id === $cart->keranjang_id, 404);
        $item->delete();
        return $this->payload($cart);
    }
}
