<?php

namespace Database\Seeders;

use App\Models\DetailKeranjang;
use App\Models\Keranjang;
use App\Models\Pelanggan;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class KeranjangSeeder extends Seeder
{
    public function run(): void
    {
        $produk = Produk::pluck('produk_id', 'produk_nama');

        $data = [
            'budi' => [
                ['Topeng Panji Ukir', 1],
                ['Gantungan Kunci Topeng', 2],
            ],
            'sinta' => [
                ['Topeng Bapang Mini', 1],
                ['Topeng Jaranan Hias', 1],
                ['Kaos Motif Topeng Malang', 2],
            ],
        ];

        foreach ($data as $username => $items) {
            $pelanggan = Pelanggan::where('username', $username)->first();
            if (! $pelanggan) {
                continue;
            }

            $keranjang = Keranjang::firstOrCreate(['pelanggan_id' => $pelanggan->pelanggan_id]);

            foreach ($items as [$namaProduk, $jumlah]) {
                if (! isset($produk[$namaProduk])) {
                    continue;
                }

                DetailKeranjang::firstOrCreate(
                    [
                        'keranjang_id' => $keranjang->keranjang_id,
                        'produk_id' => $produk[$namaProduk],
                    ],
                    ['jumlah' => $jumlah]
                );
            }
        }
    }
}
