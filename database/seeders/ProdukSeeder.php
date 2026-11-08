<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $images = Storage::disk('public')->files('produk');

        $data = [
            ['Topeng Panji Ukir', 250000, 8],
            ['Topeng Bapang Mini', 85000, 15],
            ['Topeng Klana Wall Art', 320000, 4],
            ['Topeng Jaranan Hias', 150000, 6],
            ['Topeng Malangan Klasik', 275000, 3],
            ['Paket 3 Topeng Dekorasi', 450000, 5],
            ['Gantungan Kunci Topeng', 25000, 40],
            ['Kaos Motif Topeng Malang', 95000, 12],
        ];

        foreach ($data as $i => [$nama, $harga, $stok]) {
            Produk::firstOrCreate(
                ['produk_nama' => $nama],
                [
                    'produk_harga' => $harga,
                    'produk_stok' => $stok,
                    'produk_gambar' => $images ? $images[$i % count($images)] : null,
                ]
            );
        }
    }
}
