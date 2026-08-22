<?php
/**
 * =========================================================
 *  GATEWAY / PROXY API — INI JANTUNG SISTEMNYA
 * =========================================================
 *  Orang yang beli API cuma tau endpoint INI, bukan link
 *  asli kamu (data-nexai.starpit.my.id). Di sinilah dicek:
 *   1. API key valid atau tidak
 *   2. Sudah expired atau belum (khusus Pro/Enterprise)
 *   3. Kuota bulanan/tahunan masih ada atau habis
 *  Baru setelah lolos semua, request diteruskan ke API asli.
 * =========================================================
 *
 * Cara pakai (contoh untuk user API kamu):
 *   GET https://domainmu.com/api/v1/search.php?api_key=nexai_xxx&q=kata_kunci
 */

require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$apiKey = $_GET['api_key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';

$check = validate_and_consume($apiKey);

// Kirim header info rate-limit (standar seperti API profesional lainnya)
if (isset($check['data'])) {
    header('X-RateLimit-Limit: ' . $check['data']['limit']);
    header('X-RateLimit-Remaining: ' . $check['data']['remaining']);
}

if (!$check['ok']) {
    http_response_code($check['code']);
    echo json_encode([
        'success' => false,
        'error'   => $check['message'],
    ], JSON_PRETTY_PRINT);
    exit;
}

// ---------------------------------------------------------
// Teruskan semua parameter (KECUALI api_key) ke API ASLI
// ---------------------------------------------------------
$params = $_GET;
unset($params['api_key']);

$upstreamUrl = UPSTREAM_API_URL;
if (!empty($params)) {
    $upstreamUrl .= '?' . http_build_query($params);
}

$ch = curl_init($upstreamUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
]);
$response   = curl_exec($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError  = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error'   => 'Gagal menghubungi server API asli.',
    ], JSON_PRETTY_PRINT);
    exit;
}

// Coba decode sebagai JSON, kalau bukan JSON kirim apa adanya
$decoded = json_decode($response, true);

http_response_code($httpCode ?: 200);
echo json_encode([
    'success' => true,
    'meta'    => [
        'plan'      => $check['data']['plan'],
        'used'      => $check['data']['used'],
        'limit'     => $check['data']['limit'],
        'remaining' => $check['data']['remaining'],
    ],
    'result'  => $decoded !== null ? $decoded : $response,
], JSON_PRETTY_PRINT);
