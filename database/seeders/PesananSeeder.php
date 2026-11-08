<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PesananSeeder extends Seeder
{
    public function run(): void
    {
        if (Pesanan::exists()) {
            return;
        }

        $pelanggan = Pelanggan::orderBy('pelanggan_id')->get();
        $produk = Produk::orderBy('produk_id')->get();

        if ($pelanggan->isEmpty() || $produk->isEmpty()) {
            return;
        }

        $alamat = [
            'Jl. Semeru No. 12, Malang',
            'Jl. Ijen No. 45, Malang',
            'Jl. Soekarno Hatta No. 8, Malang',
            'Jl. Basuki Rahmat No. 21, Malang',
            'Jl. Raya Tumpang No. 3, Kab. Malang',
        ];
        $metode = ['Transfer BCA', 'Transfer BRI', 'COD', 'DANA'];

        // [index pelanggan, hari lalu, status pesanan, status pembayaran, item [index produk, jumlah]]
        $orders = [
            [0, 0, 'selesai', 'lunas', [[0, 1], [6, 2]]],
            [1, 3, 'dikirim', 'lunas', [[1, 2]]],
            [2, 6, 'diproses', 'menunggu_verifikasi', [[2, 1], [7, 1]]],
            [3, 10, 'selesai', 'lunas', [[3, 1]]],
            [4, 16, 'menunggu', 'belum_bayar', [[4, 1], [6, 3]]],
            [5, 22, 'selesai', 'lunas', [[1, 1], [3, 1]]],
            [0, 28, 'dibatalkan', 'ditolak', [[5, 1]]],
            [1, 35, 'selesai', 'lunas', [[0, 2]]],
            [2, 47, 'selesai', 'lunas', [[2, 1]]],
            [3, 62, 'dikirim', 'lunas', [[3, 2], [7, 1]]],
            [4, 78, 'selesai', 'lunas', [[4, 1]]],
            [5, 96, 'menunggu', 'belum_bayar', [[6, 5]]],
            [0, 124, 'selesai', 'lunas', [[5, 1], [0, 1]]],
            [1, 152, 'dibatalkan', 'ditolak', [[7, 2]]],
            [2, 181, 'selesai', 'lunas', [[1, 3]]],
        ];

        DB::transaction(function () use ($orders, $pelanggan, $produk, $alamat, $metode) {
            // Insert dari yang terlama agar pesanan terbaru punya id terbesar.
            foreach (array_reverse($orders) as $i => [$pelangganIndex, $daysAgo, $status, $pembayaran, $items]) {
                $p = $pelanggan[$pelangganIndex % $pelanggan->count()];
                $subtotal = 0;
                $details = [];

                foreach ($items as [$produkIndex, $jumlah]) {
                    $pr = $produk[$produkIndex % $produk->count()];
                    $harga = (float) $pr->produk_harga;
                    $sub = $harga * $jumlah;
                    $subtotal += $sub;
                    $details[] = [
                        'produk_id' => $pr->produk_id,
                        'detail_jumlah' => $jumlah,
                        'detail_harga' => $harga,
                        'detail_subtotal' => $sub,
                    ];
                }

                $pesanan = Pesanan::create([
                    'pelanggan_id' => $p->pelanggan_id,
                    'pesanan_nama_penerima' => $p->nama,
                    'pesanan_no_hp' => '0812345678'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                    'pesanan_alamat_pengiriman' => $alamat[$i % count($alamat)],
                    'pesanan_subtotal' => $subtotal,
                    'pesanan_total' => $subtotal,
                    'pesanan_metode_pembayaran' => $metode[$i % count($metode)],
                    'pesanan_status' => $status,
                    'pesanan_status_pembayaran' => $pembayaran,
                ]);

                $pesanan->pesanan_tanggal = now()->subDays($daysAgo);
                $pesanan->save();

                $pesanan->details()->createMany($details);
            }
        });
    }
}
