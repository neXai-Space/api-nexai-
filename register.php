<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';
$newKey = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $plan  = $_POST['plan'] ?? 'free';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid.';
    } else {
        // Catatan: paket Pro & Enterprise sebaiknya baru dibuatkan key
        // SETELAH pembayaran dikonfirmasi manual oleh admin (lihat admin.php).
        // Di sini, demi kemudahan, semua orang bisa langsung dapat key Free.
        // Untuk Pro/Enterprise, arahkan ke halaman kontak/pembayaran dulu.
        if ($plan !== 'free') {
            header('Location: pricing.html?upgrade=' . urlencode($plan));
            exit;
        }
        $newKey = create_new_key($email, 'free');
        header('Location: dashboard.php?key=' . urlencode($newKey));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar - Nexai Search API</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container narrow">
    <a href="index.html" class="logo">⚡ Nexai Search API</a>
    <div class="card form-card">
        <h2>Ambil API Key Gratis</h2>
        <p class="muted">10.000 request/bulan, langsung aktif tanpa kartu kredit.</p>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" placeholder="kamu@email.com" required>
            <input type="hidden" name="plan" value="free">
            <button type="submit" class="btn btn-primary btn-block">Buat API Key</button>
        </form>
        <p class="muted small">Sudah punya API key? <a href="dashboard.php">Lihat dashboard</a></p>
    </div>
</div>
</body>
</html>
