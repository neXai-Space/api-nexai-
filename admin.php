<?php
require_once __DIR__ . '/includes/functions.php';
global $PLANS;

session_start();

// --- Login sederhana ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
    } else {
        $loginError = 'Password salah.';
    }
}

if (!empty($_POST['upgrade_key']) && !empty($_SESSION['is_admin'])) {
    upgrade_key_plan($_POST['upgrade_key'], $_POST['new_plan']);
    header('Location: admin.php');
    exit;
}

if (!empty($_SESSION['is_admin'])):
    $keys = load_keys();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin - Nexai Search API</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <a href="index.html" class="logo">⚡ Nexai Search API — Admin</a>
    <div class="card">
        <h2>Semua API Key (<?= count($keys) ?>)</h2>
        <p class="muted small">Upgrade plan di sini setelah kamu terima pembayaran manual dari user (transfer/QRIS/dll).</p>
        <table class="admin-table">
            <tr>
                <th>API Key</th><th>Email</th><th>Plan</th><th>Expired</th><th>Aksi</th>
            </tr>
            <?php foreach ($keys as $k => $d): ?>
            <tr>
                <td><code><?= htmlspecialchars($k) ?></code></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><?= htmlspecialchars($d['plan']) ?></td>
                <td><?= htmlspecialchars($d['expires_at'] ?? '-') ?></td>
                <td>
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="upgrade_key" value="<?= htmlspecialchars($k) ?>">
                        <select name="new_plan">
                            <?php foreach ($PLANS as $pk => $pd): ?>
                                <option value="<?= $pk ?>" <?= $d['plan'] === $pk ? 'selected' : '' ?>><?= $pd['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-copy">Simpan</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>
<?php
else:
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Admin Login</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="container narrow">
    <div class="card form-card">
        <h2>Login Admin</h2>
        <?php if (!empty($loginError)): ?><div class="alert alert-error"><?= $loginError ?></div><?php endif; ?>
        <form method="POST">
            <label>Password Admin</label>
            <input type="password" name="password" required>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
    </div>
</div>
</body>
</html>
<?php endif; ?>
