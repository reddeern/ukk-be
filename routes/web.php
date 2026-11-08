<?php

use App\Models\Topeng;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/t/{topeng}', function (Topeng $topeng) {
    $base = config('mobile.deep_link_base_url') ?: config('mobile.qr_base_url');

    return view('topeng', [
        'topeng' => $topeng,
        'deepLink' => rtrim((string) $base, '/').'/'.$topeng->topeng_id,
        'hasVideo' => $topeng->videos()->where('video_status', 'selesai')->exists(),
    ]);
});
