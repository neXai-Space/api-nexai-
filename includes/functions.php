<?php
require_once __DIR__ . '/config.php';

/**
 * =========================================================
 *  FUNGSI-FUNGSI INTI (tanpa database, murni file JSON)
 * =========================================================
 */

// Pastikan folder & file data ada
function ensure_data_file() {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    if (!file_exists(KEYS_FILE)) {
        file_put_contents(KEYS_FILE, json_encode(new stdClass()));
    }
}

// Baca semua key (dengan file lock supaya aman dari race condition)
function load_keys() {
    ensure_data_file();
    $fp = fopen(KEYS_FILE, 'r');
    flock($fp, LOCK_SH);
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

// Simpan semua key (dengan file lock)
function save_keys($data) {
    ensure_data_file();
    $fp = fopen(KEYS_FILE, 'c+');
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

// Generate API key unik, format: nexai_xxxxxxxxxxxxxxxx
function generate_api_key() {
    return 'nexai_' . bin2hex(random_bytes(16));
}

// Ambil "periode" berjalan untuk quota (bulan atau tahun ini)
function get_current_period($quota_period) {
    return $quota_period === 'yearly' ? date('Y') : date('Y-m');
}

// Buat key baru untuk sebuah plan
function create_new_key($email, $plan = 'free') {
    global $PLANS;
    if (!isset($PLANS[$plan])) $plan = 'free';
    $planData = $PLANS[$plan];

    $keys = load_keys();
    $apiKey = generate_api_key();

    $expiresAt = null;
    if ($planData['duration_days'] !== null) {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $planData['duration_days'] . ' days'));
    }

    $keys[$apiKey] = [
        'email'      => $email,
        'plan'       => $plan,
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => $expiresAt,
        'usage'      => [], // diisi otomatis per periode, contoh: {"2026-08": 120}
    ];

    save_keys($keys);
    return $apiKey;
}

// Cek apakah key sudah expired
function is_key_expired($keyData) {
    if (empty($keyData['expires_at'])) return false; // Free = tidak expired
    return strtotime($keyData['expires_at']) < time();
}

// Ambil data satu key
function find_key($apiKey) {
    $keys = load_keys();
    return $keys[$apiKey] ?? null;
}

/**
 * Validasi + konsumsi 1 quota untuk sebuah API key.
 * Return array: ['ok' => bool, 'code' => int, 'message' => string, 'data' => [...]]
 */
function validate_and_consume($apiKey) {
    global $PLANS;

    if (empty($apiKey)) {
        return ['ok' => false, 'code' => 401, 'message' => 'API key tidak ditemukan di request.'];
    }

    $keys = load_keys();
    if (!isset($keys[$apiKey])) {
        return ['ok' => false, 'code' => 401, 'message' => 'API key tidak valid.'];
    }

    $keyData = $keys[$apiKey];
    $plan = $keyData['plan'];
    $planConfig = $PLANS[$plan] ?? $PLANS['free'];

    // 1. Cek expired (khusus Pro & Enterprise)
    if (is_key_expired($keyData)) {
        return ['ok' => false, 'code' => 403, 'message' => 'API key sudah expired. Silakan perpanjang langganan.'];
    }

    // 2. Cek & hitung kuota berjalan
    $period = get_current_period($planConfig['quota_period']);
    $used = $keyData['usage'][$period] ?? 0;

    if ($used >= $planConfig['quota']) {
        return [
            'ok' => false,
            'code' => 429,
            'message' => 'Kuota request untuk periode ini sudah habis. Upgrade plan atau tunggu periode berikutnya.',
        ];
    }

    // 3. Konsumsi 1 quota (simpan)
    $keyData['usage'][$period] = $used + 1;
    $keys[$apiKey] = $keyData;
    save_keys($keys);

    return [
        'ok' => true,
        'code' => 200,
        'message' => 'OK',
        'data' => [
            'plan'      => $plan,
            'limit'     => $planConfig['quota'],
            'used'      => $used + 1,
            'remaining' => $planConfig['quota'] - ($used + 1),
            'period'    => $period,
        ],
    ];
}

// Upgrade plan sebuah key (dipakai di admin.php setelah kamu terima pembayaran manual)
function upgrade_key_plan($apiKey, $newPlan) {
    global $PLANS;
    if (!isset($PLANS[$newPlan])) return false;

    $keys = load_keys();
    if (!isset($keys[$apiKey])) return false;

    $planConfig = $PLANS[$newPlan];
    $keys[$apiKey]['plan'] = $newPlan;
    $keys[$apiKey]['expires_at'] = $planConfig['duration_days'] !== null
        ? date('Y-m-d H:i:s', strtotime('+' . $planConfig['duration_days'] . ' days'))
        : null;

    save_keys($keys);
    return true;
}
