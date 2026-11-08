<?php

namespace Tests\Feature;

use App\Models\Topeng;
use App\Models\VideoAi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopengLandingTest extends TestCase
{
    use RefreshDatabase;

    private function topeng(string $nama = 'Topeng Panji'): Topeng
    {
        return Topeng::create([
            'topeng_nama' => $nama,
            'sifat_topeng' => 'Bijaksana',
            'topeng_deskripsi' => 'Deskripsi ' . $nama,
        ]);
    }

    public function test_halaman_topeng_tampil_dengan_status_video(): void
    {
        $topeng = $this->topeng();
        VideoAi::create([
            'topeng_id' => $topeng->topeng_id,
            'video_judul' => 'Video demo',
            'video_status' => 'selesai',
        ]);

        $this->get('/t/' . $topeng->topeng_id)
            ->assertOk()
            ->assertSee('Topeng Panji')
            ->assertSee('Bijaksana')
            ->assertSee('Video AI tersedia')
            ->assertSee('Buka aplikasi');
    }

    public function test_halaman_topeng_tanpa_video(): void
    {
        $topeng = $this->topeng();

        $this->get('/t/' . $topeng->topeng_id)
            ->assertOk()
            ->assertSee('Belum ada video AI');
    }

    public function test_topeng_tidak_ditemukan_404(): void
    {
        $this->get('/t/999999')->assertNotFound();
    }

    public function test_tombol_memakai_deep_link_config(): void
    {
        $topeng = $this->topeng();
        config([
            'mobile.qr_base_url' => 'http://192.168.1.10:8000/t',
            'mobile.deep_link_base_url' => 'exp://192.168.1.10:8081/--/t',
        ]);

        $this->get('/t/' . $topeng->topeng_id)
            ->assertOk()
            ->assertSee('exp://192.168.1.10:8081/--/t/' . $topeng->topeng_id);
    }

    public function test_deep_link_kosong_memakai_qr_base_url(): void
    {
        $topeng = $this->topeng();
        config([
            'mobile.qr_base_url' => 'https://contoh.test/t',
            'mobile.deep_link_base_url' => '',
        ]);

        $this->get('/t/' . $topeng->topeng_id)
            ->assertOk()
            ->assertSee('https://contoh.test/t/' . $topeng->topeng_id);
    }
}
