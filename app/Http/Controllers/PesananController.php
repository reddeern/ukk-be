<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    private function milik(Request $r, Pesanan $pesanan): Pesanan
    {
        abort_unless($pesanan->pelanggan_id === $r->user()->pelanggan_id, 404);
        return $pesanan;
    }

    public function index(Request $r)
    {
        return Pesanan::with('details.produk')
            ->where('pelanggan_id', $r->user()->pelanggan_id)
            ->orderByDesc('pesanan_id')->paginate(10);
    }

    public function show(Request $r, Pesanan $pesanan)
    {
        return $this->milik($r, $pesanan)->load('details.produk');
    }

    /** Checkout: keranjang -> pesanan (harga & stok dikunci dalam transaksi). */
    public function store(Request $r)
    {
        $d = $r->validate([
            'pesanan_nama_penerima' => 'required|string|max:100',
            'pesanan_no_hp' => 'required|string|max:20',
            'pesanan_alamat_pengiriman' => 'required|string',
            'pesanan_metode_pembayaran' => 'required|string|max:50',
        ]);

        $cart = Keranjang::with('items')->where('pelanggan_id', $r->user()->pelanggan_id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong.'], 422);
        }

        $pesanan = DB::transaction(function () use ($cart, $d, $r) {
            $subtotal = 0;
            $baris = [];

            foreach ($cart->items as $item) {
                $produk = Produk::lockForUpdate()->find($item->produk_id);
                if (!$produk || $produk->produk_stok < $item->jumlah) {
                    throw ValidationException::withMessages([
                        'keranjang' => 'Stok tidak mencukupi untuk ' . ($produk->produk_nama ?? 'produk'),
                    ]);
                }
                $sub = $produk->produk_harga * $item->jumlah;
                $subtotal += $sub;
                $baris[] = [
                    'produk_id' => $produk->produk_id,
                    'detail_jumlah' => $item->jumlah,
                    'detail_harga' => $produk->produk_harga,
                    'detail_subtotal' => $sub,
                ];
                $produk->decrement('produk_stok', $item->jumlah);
            }

            $pesanan = Pesanan::create($d + [
                'pelanggan_id' => $r->user()->pelanggan_id,
                'pesanan_subtotal' => $subtotal,
                'pesanan_total' => $subtotal, // belum ada kolom ongkir di PDM
            ]);
            $pesanan->details()->createMany($baris);
            $cart->items()->delete();

            return $pesanan;
        });

        return response()->json($pesanan->fresh()->load('details.produk'), 201);
    }

    public function uploadBukti(Request $r, Pesanan $pesanan)
    {
        $this->milik($r, $pesanan);
        if ($pesanan->pesanan_status === 'dibatalkan' || $pesanan->pesanan_status_pembayaran === 'lunas') {
            return response()->json(['message' => 'Pesanan tidak bisa menerima bukti pembayaran.'], 422);
        }
        $r->validate(['bukti' => 'required|image|max:4096']);

        $pesanan->update([
            'pesanan_bukti_pembayaran' => $r->file('bukti')->store('bukti', 'public'),
            'pesanan_status_pembayaran' => 'menunggu_verifikasi',
        ]);
        return $pesanan->load('details.produk');
    }

    public function batal(Request $r, Pesanan $pesanan)
    {
        $this->milik($r, $pesanan);
        if ($pesanan->pesanan_status !== 'menunggu' || $pesanan->pesanan_status_pembayaran === 'lunas') {
            return response()->json(['message' => 'Pesanan tidak bisa dibatalkan.'], 422);
        }
        DB::transaction(fn () => $pesanan->load('details')->batalkan());
        return $pesanan->fresh();
    }
}
