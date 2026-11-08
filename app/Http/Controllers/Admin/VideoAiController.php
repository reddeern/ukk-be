<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoAi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VideoAiController extends Controller
{
    private const STATUS = ['pending', 'processing', 'selesai', 'gagal'];

    // max dalam KB: 307200 = 300 MB. Samakan dengan upload_max_filesize di php.ini.
    private const FILE_RULE = 'nullable|file|mimes:mp4,webm,mov|max:307200';

    public function index()
    {
        return VideoAi::with('topeng:topeng_id,topeng_nama')->orderByDesc('video_id')->paginate(20);
    }

    public function show(VideoAi $video) { return $video->load('topeng:topeng_id,topeng_nama'); }

    /** multipart/form-data: topeng_id, video_judul, video (file), video_status (opsional) */
    public function store(Request $r)
    {
        $d = $r->validate([
            'topeng_id' => 'required|exists:topeng,topeng_id',
            'video_judul' => 'required|string|max:150',
            'video_status' => ['sometimes', Rule::in(self::STATUS)],
            'video' => self::FILE_RULE,
        ]);

        if ($r->hasFile('video')) {
            set_time_limit(0); // upload ke storage cloud bisa lama; di Windows waktu jaringan ikut dihitung
            $d['video_file'] = $r->file('video')->store('video', config('video.disk'));
            $d['video_status'] = $d['video_status'] ?? 'selesai';
        }
        unset($d['video']);

        return response()->json(VideoAi::create($d)->fresh(), 201);
    }

    // Bila mengganti file video, kirim sebagai POST + field _method=PUT (multipart).
    public function update(Request $r, VideoAi $video)
    {
        $d = $r->validate([
            'topeng_id' => 'sometimes|required|exists:topeng,topeng_id',
            'video_judul' => 'sometimes|required|string|max:150',
            'video_status' => ['sometimes', Rule::in(self::STATUS)],
            'video' => self::FILE_RULE,
        ]);

        if ($r->hasFile('video')) {
            set_time_limit(0);
            if ($video->video_file) Storage::disk(config('video.disk'))->delete($video->video_file);
            $d['video_file'] = $r->file('video')->store('video', config('video.disk'));
        }
        unset($d['video']);

        $video->update($d);
        return $video;
    }

    public function destroy(VideoAi $video)
    {
        if ($video->video_file) Storage::disk(config('video.disk'))->delete($video->video_file);
        $video->delete();
        return response()->noContent();
    }
}
