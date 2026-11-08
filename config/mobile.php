<?php

return [
    // Basis URL yang ditanam di QR. Dev Expo Go: exp://192.168.x.x:8081/--/t
    // Produksi: https://domain/t
    'qr_base_url' => rtrim(
        env('MOBILE_QR_BASE_URL', rtrim((string) env('APP_URL', 'http://localhost'), '/').'/t'),
        '/'
    ),

    // Basis deep link tombol "Buka aplikasi" di halaman fallback /t/{id}.
    // Demo Expo Go: exp://192.168.x.x:8081/--/t
    // Produksi: topeng://t (atau biarkan kosong agar ikut qr_base_url)
    'deep_link_base_url' => rtrim((string) env('MOBILE_DEEP_LINK_BASE_URL', ''), '/'),
];
