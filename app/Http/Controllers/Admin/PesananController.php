<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    private const STATUS = ['menunggu', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
    private const BAYAR = ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'ditolak'];

    public function index(Request $r)
    {
        return Pesanan::with('pelanggan:pelanggan_id,nama,email')
            ->when($r->status, fn ($q, $s) => $q->where('pesanan_status', $s))
            ->when($r->pembayaran, fn ($q, $s) => $q->where('pesanan_status_pembayaran', $s))
            ->when($r->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('pesanan_nama_penerima', 'like', "%{$s}%")
                  ->orWhere('pesanan_no_hp', 'like', "%{$s}%")
                  ->orWhereHas('pelanggan', fn ($p) => $p->where('nama', 'like', "%{$s}%"))
                  ->when(is_numeric($s), fn ($w2) => $w2->orWhere('pesanan_id', $s));
            }))
            ->orderByDesc('pesanan_id')->paginate(min(max((int) $r->input('per_page', 20), 1), 100));
    }

    public function show(Pesanan $pesanan) { return $pesanan->load('pelanggan', 'details.produk'); }

    /** Admin membuatkan pesanan manual untuk pelanggan (mis. pesanan via WhatsApp). */
    public function store(Request $r)
    {
        $d = $r->validate([
            'pelanggan_id' => 'required|exists:pelanggan,pelanggan_id',
            'pesanan_nama_penerima' => 'required|string|max:100',
            'pesanan_no_hp' => 'required|string|max:20',
            'pesanan_alamat_pengiriman' => 'required|string',
            'pesanan_metode_pembayaran' => 'required|string|max:50',
            'pesanan_status' => ['sometimes', Rule::in(self::STATUS)],
            'pesanan_status_pembayaran' => ['sometimes', Rule::in(self::BAYAR)],
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,produk_id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        // gabungkan produk yang dobel
        $items = collect($d['items'])->groupBy('produk_id')->map(fn ($g) => $g->sum('jumlah'));

        $pesanan = DB::transaction(function () use ($d, $items) {
            $subtotal = 0;
            $baris = [];
            foreach ($items as $produkId => $jumlah) {
                $produk = Produk::lockForUpdate()->find($produkId);
                if ($produk->produk_stok < $jumlah) {
                    throw ValidationException::withMessages(['items' => "Stok {$produk->produk_nama} tidak mencukupi."]);
                }
                $sub = $produk->produk_harga * $jumlah;
                $subtotal += $sub;
                $baris[] = [
                    'produk_id' => $produkId,
                    'detail_jumlah' => $jumlah,
                    'detail_harga' => $produk->produk_harga,
                    'detail_subtotal' => $sub,
                ];
                $produk->decrement('produk_stok', $jumlah);
            }

            $pesanan = Pesanan::create(Arr::except($d, ['items']) + [
                'pesanan_subtotal' => $subtotal,
                'pesanan_total' => $subtotal,
            ]);
            $pesanan->details()->createMany($baris);
            return $pesanan;
        });

        return response()->json($pesanan->fresh()->load('pelanggan', 'details.produk'), 201);
    }

    public function update(Request $r, Pesanan $pesanan)
    {
        $d = $r->validate([
            'pesanan_status' => ['sometimes', Rule::in(self::STATUS)],
            'pesanan_status_pembayaran' => ['sometimes', Rule::in(self::BAYAR)],
        ]);

        if ($pesanan->pesanan_status === 'dibatalkan'
            && isset($d['pesanan_status']) && $d['pesanan_status'] !== 'dibatalkan') {
            return response()->json(['message' => 'Pesanan yang sudah dibatalkan tidak bisa diaktifkan kembali.'], 422);
        }

        DB::transaction(function () use ($pesanan, $d) {
            $pesanan->load('details');
            if (($d['pesanan_status'] ?? null) === 'dibatalkan') {
                $pesanan->batalkan();
                unset($d['pesanan_status']);
            }
            if ($d) $pesanan->update($d);
        });

        return $pesanan->fresh()->load('details.produk');
    }

    /** Hapus pesanan. Pesanan lunas yang masih aktif harus dibatalkan dulu agar laporan tidak berubah diam-diam. */
    public function destroy(Pesanan $pesanan)
    {
        if ($pesanan->pesanan_status_pembayaran === 'lunas' && $pesanan->pesanan_status !== 'dibatalkan') {
            return response()->json(['message' => 'Pesanan lunas tidak bisa dihapus. Batalkan dulu bila memang perlu.'], 409);
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->load('details')->batalkan(); // kembalikan stok bila belum dibatalkan
            if ($pesanan->pesanan_bukti_pembayaran) {
                Storage::disk('public')->delete($pesanan->pesanan_bukti_pembayaran);
            }
            $pesanan->delete();
        });

        return response()->noContent();
    }
}
