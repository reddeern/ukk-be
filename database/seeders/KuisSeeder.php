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
        $id = function (string $nama) use ($topeng) {
            $topengId = $topeng[$nama] ?? null;

            if (! $topengId) {
                throw new \RuntimeException("Topeng tidak ditemukan: {$nama}");
            }

            return $topengId;
        };

        $data = [
            [
                'Saat menghadapi masalah besar, sikapmu paling mirip...',
                [
                    ['Tenang dan mencari jalan keluar dengan bijak', $id('Panji Asmarabangun')],
                    ['Langsung maju menghadapi tantangan', $id('Bapang')],
                    ['Berani mengambil kendali tanpa ragu', $id('Klana Sewandana')],
                    ['Mencari cara seru agar suasana tetap ringan', $id('Prasonto')],
                ],
            ],
            [
                'Di acara kumpul keluarga, kamu biasanya...',
                [
                    ['Menjadi pendengar dan penengah yang tenang', $id('Panji Asmarabangun')],
                    ['Inisiatif mengatur dan memimpin acara', $id('Bapang')],
                    ['Tampil percaya diri dan jadi pusat perhatian', $id('Klana Sewandana')],
                    ['Mengajak semua orang bermain dan tertawa', $id('Prasonto')],
                ],
            ],
            [
                'Kawan menggambarkan dirimu sebagai orang yang...',
                [
                    ['Bijak dan bisa dipercaya', $id('Panji Asmarabangun')],
                    ['Pemberani dan pantang menyerah', $id('Bapang')],
                    ['Tegas dan berwibawa', $id('Klana Sewandana')],
                    ['Ceria dan penuh energi', $id('Prasonto')],
                ],
            ],
            [
                'Gaya belajarmu paling efektif saat...',
                [
                    ['Membaca dan merenung dengan tenang', $id('Panji Asmarabangun')],
                    ['Mencoba langsung dan berlatih keras', $id('Bapang')],
                    ['Menantang diri dengan target tinggi', $id('Klana Sewandana')],
                    ['Berdiskusi sambil bersenang-senang', $id('Prasonto')],
                ],
            ],
        ];

        // Hapus kuis bawaan agar opsi dibuat ulang setelah data topeng diganti.
        Kuis::whereIn('pertanyaan_teks', array_column($data, 0))->delete();

        foreach ($data as [$pertanyaan, $pilihan]) {
            $kuis = Kuis::create(['pertanyaan_teks' => $pertanyaan]);

            foreach ($pilihan as [$teks, $topengId]) {
                $kuis->pilihan()->create([
                    'topeng_id' => $topengId,
                    'pilihan_jawaban' => $teks,
                ]);
            }
        }
    }
}
