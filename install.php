<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$userCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($userCount > 0) {
    redirect('admin/login.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Nama dan alamat email yang valid wajib diisi.';
    } elseif (strlen($password) < 12) {
        $error = 'Kata sandi administrator harus terdiri dari minimal 12 karakter.';
    } else {
        $insert = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "admin")');
        $insert->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) db()->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => 'admin'];
        redirect('admin/index.php');
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Setup | Dashboard Admin Web SOLNOVIS</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="admin.css">
</head>
<body class="admin-login-body">
    <main class="admin-login-card">
        <a class="admin-brand" href="index.php"><span class="brand-mark">S</span><span class="admin-brand-title">Dashboard Admin Web <small>SOLNOVIS</small></span></a>
        <p class="eyebrow">Pengaturan awal</p><h1>Buat akun administrator</h1>
        <p class="admin-muted">Akun ini akan memiliki akses penuh ke dashboard CMS.</p>
        <?php if ($error !== ''): ?><p class="admin-alert error" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form class="admin-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Nama administrator<input name="name" autocomplete="name" required value="<?= e($_POST['name'] ?? '') ?>"></label>
            <label>Email<input type="email" name="email" autocomplete="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
            <label>Kata sandi (minimal 12 karakter)<input type="password" name="password" autocomplete="new-password" minlength="12" required></label>
            <button class="button button-primary" type="submit">Buat akun & buka dashboard →</button>
        </form>
    </main>
</body>
</html>
