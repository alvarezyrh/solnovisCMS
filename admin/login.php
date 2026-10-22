<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (current_user() !== null) {
    redirect('index.php');
}
$userCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($userCount === 0) {
    redirect('../install.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $attempts = (int) ($_SESSION['login_attempts'] ?? 0);
    $lastAttempt = (int) ($_SESSION['login_attempt_at'] ?? 0);
    if (time() - $lastAttempt > 900) {
        $attempts = 0;
    }
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $query = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ?');
    $query->execute([$email]);
    $user = $query->fetch();
    if ($attempts >= 8) {
        $error = 'Terlalu banyak percobaan. Coba kembali dalam 15 menit.';
    } elseif ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        unset($_SESSION['login_attempts'], $_SESSION['login_attempt_at']);
        redirect('index.php');
    } else {
        $_SESSION['login_attempts'] = $attempts + 1;
        $_SESSION['login_attempt_at'] = time();
        $error = 'Email atau kata sandi tidak cocok.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Login | Dashboard Admin Web SOLNOVIS</title><link rel="stylesheet" href="../style.css"><link rel="stylesheet" href="../admin.css">
</head>
<body class="admin-login-body">
    <main class="admin-login-card">
        <a class="admin-brand" href="../index.php"><span class="brand-mark">S</span><span class="admin-brand-title">Dashboard Admin Web <small>SOLNOVIS</small></span></a>
        <p class="eyebrow">Area administrator</p><h1>Masuk ke dashboard</h1>
        <?php if ($error !== ''): ?><p class="admin-alert error" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form class="admin-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Email<input type="email" name="email" autocomplete="username" required autofocus></label>
            <label>Kata sandi<input type="password" name="password" autocomplete="current-password" required></label>
            <button class="button button-primary" type="submit">Masuk →</button>
        </form>
    </main>
</body>
</html>
