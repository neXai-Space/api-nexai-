<?php
header('Content-Type: application/json');

// 1. Lokasi file database JSON
$db_file = 'users.json';

// Cek apakah file JSON ada
if (!file_exists($db_file)) {
    die(json_encode(["status" => "error", "message" => "Database JSON tidak ditemukan."]));
}

// 2. Ambil & Cek API Key dari request URL (?key=...)
if (!isset($_GET['key'])) {
    die(json_encode(["status" => "error", "message" => "API Key tidak ditemukan!"]));
}
$api_key = $_GET['key'];

// 3. Baca isi file JSON
$json_data = file_get_contents($db_file);
$users = json_decode($json_data, true);

// 4. Validasi apakah API Key terdaftar
if (!isset($users[$api_key])) {
    die(json_encode(["status" => "error", "message" => "API Key tidak valid atau tidak terdaftar!"]));
}

$userData = $users[$api_key];

// 5. Cek Masa Expired (Kadaluarsa)
$current_date = date('Y-m-d');
if ($current_date > $userData['expired_at']) {
    die(json_encode(["status" => "error", "message" => "API Key sudah expired. Silakan perpanjang!"]));
}

// 6. Cek Limit/Kuota
if ($userData['req_used'] >= $userData['req_limit']) {
    die(json_encode(["status" => "error", "message" => "Limit request API Anda bulan/tahun ini sudah habis!"]));
}

// 7. JIKA AMAN: Ambil data dari server original NexAI kamu
$original_api_url = "https://data-nexai.starpit.my.id/data.js";
$response_data = file_get_contents($original_api_url);

if ($response_data === FALSE) {
    die(json_encode(["status" => "error", "message" => "Gagal mengambil data dari server utama."]));
}

// 8. Tambahkan jumlah pemakaian (req_used + 1) dan simpan kembali ke JSON
$users[$api_key]['req_used'] += 1;

// LOCK_EX biar aman kalau ada yang akses API barengan
file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);

// 9. Tampilkan data asli ke user
echo $response_data;
?>
