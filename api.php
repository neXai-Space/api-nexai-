<?php
// Tampilkan error jika ada masalah di PHP (biar gampang debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$db_file = 'users.json';

if (!file_exists($db_file)) {
    die(json_encode(["status" => "error", "message" => "Database JSON tidak ditemukan."]));
}

if (!isset($_GET['key'])) {
    die(json_encode(["status" => "error", "message" => "API Key tidak ditemukan!"]));
}
$api_key = $_GET['key'];

$json_data = file_get_contents($db_file);
$users = json_decode($json_data, true);

if (!isset($users[$api_key])) {
    die(json_encode(["status" => "error", "message" => "API Key tidak valid atau tidak terdaftar!"]));
}

$userData = $users[$api_key];
$current_date = date('Y-m-d');

if ($current_date > $userData['expired_at']) {
    die(json_encode(["status" => "error", "message" => "API Key sudah expired. Silakan perpanjang!"]));
}

if ($userData['req_used'] >= $userData['req_limit']) {
    die(json_encode(["status" => "error", "message" => "Limit request API Anda habis!"]));
}

// AMBIL PERTANYAAN USER (Contoh: "siapakah presiden...")
$query = isset($_GET['q']) ? urlencode($_GET['q']) : '';

// -------------------------------------------------------------
// GANTI URL INI DENGAN API ORIGINAL YANG MAU KAMU PROXY
// -------------------------------------------------------------
// Kalau pakai Brainly (seperti di script HTML kamu sebelumnya):
$original_api_url = "https://bintangapi.my.id/api/search/brainly?q=" . $query . "&limit=10";

// ATAU kalau pakai data.js NexAI kamu:
// $original_api_url = "https://data-nexai.starpit.my.id/data.js?q=" . $query;


// PROSES MENGAMBIL DATA MENGGUNAKAN cURL (Lebih kuat dari file_get_contents)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $original_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Abaikan SSL error kalau ada
$response_data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Cek apakah gagal mengambil data
if ($response_data === FALSE || $http_code != 200) {
    die(json_encode([
        "status" => "error", 
        "message" => "Gagal mengambil data dari server utama. HTTP Code: $http_code. Error: $curl_error"
    ]));
}

// POTONG KUOTA USER
$users[$api_key]['req_used'] += 1;
file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);

// TAMPILKAN HASIL KE USER
echo $response_data;
?>
