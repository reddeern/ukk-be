<?php

namespace Database\Seeders;

use App\Models\Topeng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TopengSeeder extends Seeder
{
    public function run(): void
    {
        $images = Storage::disk('public')->files('topeng');

        $data = [
            ['Bijaksana', 'Topeng Panji', 'Topeng Panji melambangkan ketenangan, kebijaksanaan, dan budi pekerti luhur. Gerakannya halus dan penuh wibawa.'],
            ['Gagah', 'Topeng Bapang', 'Topeng Bapang menggambarkan prajurit yang gagah berani, tegas, dan penuh semangat juang.'],
            ['Angkuh', 'Topeng Klana', 'Topeng Klana berkarakter angkuh, kuat, dan menakutkan. Biasanya tampil sebagai raja atau raksasa.'],
            ['Lineah', 'Topeng Jaranan', 'Topeng Jaranan melambangkan kelincahan dan kegembiraan, sering dipakai dalam tari Jaranan.'],
            ['Sangar', 'Topeng Tumpang Tindih', 'Topeng Tumpang Tindih khas Malang dengan tatahan bertumpuk, berwibawa dan bernuansa magis.'],
        ];

        foreach ($data as $i => [$sifat, $nama, $deskripsi]) {
            Topeng::firstOrCreate(
                ['topeng_nama' => $nama],
                [
                    'sifat_topeng' => $sifat,
                    'topeng_deskripsi' => $deskripsi,
                    'topeng_gambar' => $images ? $images[$i % count($images)] : null,
                ]
            );
        }
    }
}
