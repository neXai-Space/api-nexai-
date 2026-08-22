<?php
require_once __DIR__ . '/includes/functions.php';
global $PLANS;

$apiKey = $_GET['key'] ?? '';
$keyData = $apiKey ? find_key($apiKey) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard - Nexai Search API</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container narrow">
    <a href="index.html" class="logo">⚡ Nexai Search API</a>

    <?php if (!$apiKey): ?>
        <div class="card form-card">
            <h2>Cek Dashboard API Key</h2>
            <form method="GET">
                <label>Masukkan API Key kamu</label>
                <input type="text" name="key" placeholder="nexai_xxxxxxxx" required>
                <button type="submit" class="btn btn-primary btn-block">Lihat Dashboard</button>
            </form>
        </div>

    <?php elseif (!$keyData): ?>
        <div class="card">
            <div class="alert alert-error">API key tidak ditemukan.</div>
            <a href="register.php" class="btn btn-primary">Buat API Key Baru</a>
        </div>

    <?php else:
        $plan = $keyData['plan'];
        $planConfig = $PLANS[$plan];
        $period = get_current_period($planConfig['quota_period']);
        $used = $keyData['usage'][$period] ?? 0;
        $limit = $planConfig['quota'];
        $percent = $limit > 0 ? min(100, round(($used / $limit) * 100)) : 0;
        $expired = is_key_expired($keyData);
    ?>
        <div class="card">
            <div class="badge badge-<?= htmlspecialchars($plan) ?>"><?= htmlspecialchars($planConfig['label']) ?> Plan</div>
            <h2>Dashboard API Kamu</h2>

            <label class="muted small">API Key</label>
            <div class="key-box">
                <code id="apiKeyText"><?= htmlspecialchars($apiKey) ?></code>
                <button class="btn btn-copy" onclick="copyKey()">Copy</button>
            </div>

            <?php if ($expired): ?>
                <div class="alert alert-error">⚠️ API key ini sudah EXPIRED. Perpanjang di halaman <a href="pricing.html">Pricing</a>.</div>
            <?php endif; ?>

            <div class="usage-block">
                <div class="usage-label">
                    <span>Pemakaian periode ini (<?= htmlspecialchars($period) ?>)</span>
                    <span><?= number_format($used) ?> / <?= number_format($limit) ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $percent ?>%"></div>
                </div>
            </div>

            <p class="muted small">
                Berlaku sampai:
                <strong><?= $keyData['expires_at'] ? htmlspecialchars($keyData['expires_at']) : 'Tidak ada batas (Free)' ?></strong>
            </p>

            <h3>Contoh Pemakaian</h3>
            <pre class="code-block">GET https://domainmu.com/api/v1/search.php?api_key=<?= htmlspecialchars($apiKey) ?>&q=kata_kunci</pre>

            <a href="pricing.html" class="btn btn-outline btn-block">Upgrade Plan</a>
        </div>
    <?php endif; ?>
</div>
<script src="js/main.js"></script>
</body>
</html>
