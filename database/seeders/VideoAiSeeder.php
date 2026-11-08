<?php

namespace Database\Seeders;

use App\Models\Topeng;
use App\Models\VideoAi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class VideoAiSeeder extends Seeder
{
    public function run(): void
    {
        // Satu topeng = satu video AI. Semua memakai file demo yang siap diputar.
        $demoFile = 'video/Tz6IYwrMFwIhepSSklfUwgC38fFCUnm1EZl9j3LK.mp4';
        $local = Storage::disk('public')->files('video');
        if ($local) {
            $demoFile = $local[0];
        }

        foreach (Topeng::orderBy('topeng_id')->get() as $topeng) {
            VideoAi::firstOrCreate(
                ['topeng_id' => $topeng->topeng_id],
                [
                    'video_judul' => 'Video AI '.$topeng->topeng_nama,
                    'video_file' => $demoFile,
                    'video_status' => 'selesai',
                ]
            );
        }
    }
}
