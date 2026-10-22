<?php
declare(strict_types=1);

function admin_header(string $title, string $active = ''): void
{
    $user = current_user();
    $flash = take_flash();
    ?>
    <!doctype html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex,nofollow">
        <title><?= e($title) ?> | Dashboard Admin Web SOLNOVIS</title>
        <link rel="stylesheet" href="../style.css">
        <link rel="stylesheet" href="../admin.css">
    </head>
    <body class="admin-body">
        <header class="admin-topbar">
            <a class="admin-brand" href="index.php"><span class="brand-mark">S</span><span class="admin-brand-title">Dashboard Admin Web <small>SOLNOVIS</small></span></a>
            <div class="admin-user"><span><?= e($user['name'] ?? '') ?></span><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button type="submit">Keluar</button></form></div>
        </header>
        <div class="admin-shell">
            <aside class="admin-sidebar">
                <p class="admin-label">Dashboard</p>
                <a class="<?= $active === 'dashboard' ? 'current' : '' ?>" href="index.php">Ringkasan</a>
                <a class="<?= $active === 'posts' ? 'current' : '' ?>" href="manage.php?type=posts">Post</a>
                <a class="<?= $active === 'pages' ? 'current' : '' ?>" href="manage.php?type=pages">Halaman</a>
                <a class="<?= $active === 'menus' ? 'current' : '' ?>" href="manage.php?type=menus">Menu</a>
                <a class="<?= $active === 'sliders' ? 'current' : '' ?>" href="manage.php?type=hero_slides">Slider Hero</a>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <a class="<?= $active === 'settings' ? 'current' : '' ?>" href="settings.php">Settings</a>
                    <a class="<?= $active === 'profiles' ? 'current' : '' ?>" href="manage.php?type=profiles">Profiles</a>
                <?php endif; ?>
                <a class="admin-view-site" href="../index.php" target="_blank" rel="noopener">Lihat website ↗</a>
            </aside>
            <main class="admin-main">
                <div class="admin-page-heading"><p class="eyebrow">Dashboard Admin Web SOLNOVIS</p><h1><?= e($title) ?></h1></div>
                <?php if ($flash !== null): ?>
                    <p class="admin-alert <?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
                <?php endif; ?>
    <?php
}

function admin_footer(): void
{
    ?>
            </main>
        </div>
    </body>
    </html>
    <?php
}
