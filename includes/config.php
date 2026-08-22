<?php
/**
 * =========================================================
 *  KONFIGURASI UTAMA NEXAI SEARCH API PLATFORM
 * =========================================================
 *  Semua pengaturan penting ada di sini. Kalau mau ubah
 *  harga, kuota, atau link API asli, cukup edit file ini.
 * =========================================================
 */

// Jangan pernah expose ini ke publik / ke frontend!
define('UPSTREAM_API_URL', 'https://data-nexai.starpit.my.id/data.js');

// Password admin buat halaman admin.php (WAJIB ganti sebelum online!)
define('ADMIN_PASSWORD', 'ganti_password_ini_123');

// File "database" (JSON, tanpa MySQL)
define('DATA_DIR', __DIR__ . '/../data');
define('KEYS_FILE', DATA_DIR . '/keys.json');

// Daftar paket / plan
// quota_period: 'monthly' = reset tiap bulan, 'yearly' = reset tiap tahun
// duration_days: null = tidak expired (khusus Free), angka = umur key dalam hari
$PLANS = [
    'free' => [
        'label'         => 'Free',
        'price'         => 0,
        'quota'         => 10000,     // 10.000 request
        'quota_period'  => 'monthly',
        'duration_days' => null,      // key gratis tidak expired
    ],
    'pro' => [
        'label'         => 'Pro',
        'price'         => 15000,     // Rp 15.000 / tahun
        'quota'         => 100000,    // 100.000 request / bulan
        'quota_period'  => 'monthly',
        'duration_days' => 365,
    ],
    'enterprise' => [
        'label'         => 'Enterprise',
        'price'         => 30000,     // Rp 30.000 / tahun
        'quota'         => 1000000,   // 1.000.000 request / tahun
        'quota_period'  => 'yearly',
        'duration_days' => 365,
    ],
];
