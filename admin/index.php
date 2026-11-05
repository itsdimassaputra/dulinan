<?php
require_once __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    redirect(url('admin/dashboard.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if (verify_admin($u, $p)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $u;
        redirect(url('admin/dashboard.php'));
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — Dulinan</title>
    <link rel="icon" type="image/png" href="<?= favicon_url() ?>">
    <link rel="apple-touch-icon" href="<?= logo_url() ?>">
    <script src="<?= url('assets/js/theme-init.js') ?>"></script>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<div class="auth-wrap">
    <button id="themeToggle" class="theme-btn" title="Ganti tema" aria-label="Ganti tema"
            style="position:absolute;top:20px;right:20px;">
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
        </svg>
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </button>

    <div class="auth-card">
        <div style="text-align:center;margin-bottom:16px;">
            <img src="<?= logo_url() ?>" alt="Dulinan" class="logo-img"
                 style="margin:0 auto 12px;">
            <div style="font-weight:800;font-size:18px;letter-spacing:-0.02em;">Admin Login</div>
        </div>
        <p class="subtitle" style="text-align:center;">Masuk untuk mengelola game &amp; kategori.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Masuk</button>
        </form>

        <p style="margin-top:20px;text-align:center;font-size:13px;color:var(--text-secondary);">
            <a href="<?= url('index.php') ?>" style="color:var(--accent);font-weight:600;">← Kembali ke beranda</a>
        </p>
    </div>
</div>

<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>