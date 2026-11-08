<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Kuis;
use App\Models\Topeng;
use App\Models\VideoAi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QrKuisTest extends TestCase
{
    use RefreshDatabase;

    private function topeng(string $nama): Topeng
    {
        return Topeng::create([
            'topeng_nama' => $nama,
            'sifat_topeng' => 'Bijaksana',
            'topeng_deskripsi' => 'Deskripsi ' . $nama,
        ]);
    }

    public function test_topeng_qr_url_memakai_config(): void
    {
        config(['mobile.qr_base_url' => 'https://contoh.test/t']);
        $topeng = $this->topeng('Topeng Panji');

        $this->assertSame(
            'https://contoh.test/t/' . $topeng->topeng_id,
            $topeng->topeng_qr_url
        );
    }

    public function test_kuis_universal_tidak_membocorkan_topeng_id(): void
    {
        $topeng = $this->topeng('Topeng Panji');
        $kuis = Kuis::create(['pertanyaan_teks' => 'Siapa kamu?']);
        $kuis->pilihan()->create([
            'topeng_id' => $topeng->topeng_id,
            'pilihan_jawaban' => 'Pilihan A',
        ]);

        $this->getJson('/api/kuis')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.pertanyaan_teks', 'Siapa kamu?')
            ->assertJsonMissingPath('0.topeng_id')
            ->assertJsonMissingPath('0.pilihan.0.topeng_id');
    }

    public function test_topeng_tanpa_video_mengembalikan_videos_kosong(): void
    {
        $topeng = $this->topeng('Topeng Panji');

        $this->getJson('/api/topeng/' . $topeng->topeng_id)
            ->assertOk()
            ->assertJsonCount(0, 'videos');
    }

    public function test_video_belum_selesai_tidak_ditampilkan(): void
    {
        $topeng = $this->topeng('Topeng Panji');
        VideoAi::create([
            'topeng_id' => $topeng->topeng_id,
            'video_judul' => 'Video diproses',
            'video_status' => 'processing',
        ]);

        $this->getJson('/api/topeng/' . $topeng->topeng_id)
            ->assertOk()
            ->assertJsonCount(0, 'videos');
    }

    public function test_admin_topeng_menampilkan_status_video(): void
    {
        Sanctum::actingAs(Admin::create([
            'admin_username' => 'admin',
            'admin_password' => 'rahasia123',
        ]));

        $topeng = $this->topeng('Topeng Panji');
        VideoAi::create([
            'topeng_id' => $topeng->topeng_id,
            'video_judul' => 'Video pending',
            'video_status' => 'pending',
        ]);

        $this->getJson('/api/admin/topeng')
            ->assertOk()
            ->assertJsonPath('0.videos.0.video_status', 'pending');
    }

    public function test_video_hanya_satu_per_topeng(): void
    {
        Sanctum::actingAs(Admin::create([
            'admin_username' => 'admin',
            'admin_password' => 'rahasia123',
        ]));

        $topeng = $this->topeng('Topeng Panji');

        $this->postJson('/api/admin/video', [
            'topeng_id' => $topeng->topeng_id,
            'video_judul' => 'Video pertama',
        ])->assertCreated();

        $this->postJson('/api/admin/video', [
            'topeng_id' => $topeng->topeng_id,
            'video_judul' => 'Video kedua',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('topeng_id')
            ->assertJsonPath('errors.topeng_id.0', 'Topeng ini sudah punya video AI.');
    }
}
