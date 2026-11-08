<?php

return [
    // 'public' = disk lokal (storage/app/public). Ganti ke 'supabase' lewat .env: VIDEO_DISK=supabase
    'disk' => env('VIDEO_DISK', 'public'),
];
