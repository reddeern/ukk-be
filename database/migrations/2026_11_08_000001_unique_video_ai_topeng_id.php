<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu topeng hanya boleh punya satu video AI.
        $topengIds = DB::table('video_ai')
            ->select('topeng_id')
            ->groupBy('topeng_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('topeng_id');

        foreach ($topengIds as $id) {
            $keep = DB::table('video_ai')
                ->where('topeng_id', $id)
                ->orderByRaw("CASE WHEN video_status = 'selesai' THEN 0 ELSE 1 END")
                ->orderByDesc('video_id')
                ->value('video_id');

            DB::table('video_ai')
                ->where('topeng_id', $id)
                ->where('video_id', '!=', $keep)
                ->delete();
        }

        Schema::table('video_ai', function (Blueprint $t) {
            $t->unique('topeng_id');
        });
    }

    public function down(): void
    {
        Schema::table('video_ai', function (Blueprint $t) {
            $t->dropUnique(['topeng_id']);
        });
    }
};
