<?php

namespace Database\Seeders;

use App\Models\Kuis;
use App\Models\Topeng;
use Illuminate\Database\Seeder;

class KuisSeeder extends Seeder
{
    public function run(): void
    {
        $topeng = Topeng::pluck('topeng_id', 'topeng_nama');
        $id = fn (string $nama) => $topeng[$nama] ?? $topeng->first();

        $data = [
            [
                'Saat menghadapi masalah besar, sikapmu paling mirip...',
                [
                    ['Tenang dan mencari jalan keluar dengan bijak', $id('Topeng Panji')],
                    ['Langsung maju menghadapi tantangan', $id('Topeng Bapang')],
                    ['Berani mengambil kendali tanpa ragu', $id('Topeng Klana')],
                    ['Mencari cara seru agar suasana tetap ringan', $id('Topeng Jaranan')],
                ],
            ],
            [
                'Di acara kumpul keluarga, kamu biasanya...',
                [
                    ['Menjadi pendengar dan penengah yang tenang', $id('Topeng Panji')],
                    ['Inisiatif mengatur dan memimpin acara', $id('Topeng Bapang')],
                    ['Tampil percaya diri dan jadi pusat perhatian', $id('Topeng Klana')],
                    ['Mengajak semua orang bermain dan tertawa', $id('Topeng Jaranan')],
                ],
            ],
            [
                'Kawan menggambarkan dirimu sebagai orang yang...',
                [
                    ['Bijak dan bisa dipercaya', $id('Topeng Panji')],
                    ['Pemberani dan pantang menyerah', $id('Topeng Bapang')],
                    ['Tegas dan berwibawa', $id('Topeng Klana')],
                    ['Ceria dan penuh energi', $id('Topeng Jaranan')],
                ],
            ],
            [
                'Gaya belajarmu paling efektif saat...',
                [
                    ['Membaca dan merenung dengan tenang', $id('Topeng Panji')],
                    ['Mencoba langsung dan berlatih keras', $id('Topeng Bapang')],
                    ['Menantang diri dengan target tinggi', $id('Topeng Klana')],
                    ['Berdiskusi sambil bersenang-senang', $id('Topeng Jaranan')],
                ],
            ],
        ];

        foreach ($data as [$pertanyaan, $pilihan]) {
            $kuis = Kuis::firstOrCreate(['pertanyaan_teks' => $pertanyaan]);

            if ($kuis->wasRecentlyCreated) {
                foreach ($pilihan as [$teks, $topengId]) {
                    $kuis->pilihan()->create([
                        'topeng_id' => $topengId,
                        'pilihan_jawaban' => $teks,
                    ]);
                }
            }
        }
    }
}
