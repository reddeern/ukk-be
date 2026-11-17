<?php

namespace Database\Seeders;

use App\Models\VideoAi;
use Illuminate\Database\Seeder;

class VideoAiSeeder extends Seeder
{
    public function run(): void
    {
        // Video AI sengaja dikosongkan. Admin akan mengisi video satu per satu.
        VideoAi::query()->delete();
    }
}
