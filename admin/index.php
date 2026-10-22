<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
$user = require_admin();
require_once __DIR__ . '/../includes/admin-layout.php';

$counts = [];
foreach (['posts', 'pages', 'menus', 'users'] as $table) {
    $counts[$table] = (int) db()->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn();
}
$recentPosts = db()->query('SELECT title, status, updated_at FROM posts ORDER BY updated_at DESC, id DESC LIMIT 5')->fetchAll();

admin_header('Ringkasan dashboard', 'dashboard');
?>
<section class="admin-stats">
    <a href="manage.php?type=posts"><strong><?= $counts['posts'] ?></strong><span>Post</span></a>
    <a href="manage.php?type=pages"><strong><?= $counts['pages'] ?></strong><span>Halaman</span></a>
    <a href="manage.php?type=menus"><strong><?= $counts['menus'] ?></strong><span>Item menu</span></a>
    <a href="manage.php?type=profiles"><strong><?= $counts['users'] ?></strong><span>Profiles admin</span></a>
</section>
<section class="admin-dashboard-widgets" aria-label="Widget dashboard">
    <article class="admin-widget admin-traffic-widget">
        <div class="admin-widget-heading">
            <div><p class="eyebrow">Performa website</p><h2>Traffic web</h2></div>
            <span class="admin-widget-period">7 hari terakhir</span>
        </div>
        <div class="traffic-summary"><strong>—</strong><span>Data analytics belum terhubung</span></div>
        <div class="traffic-chart" aria-label="Grafik traffic web 7 hari terakhir">
            <div class="traffic-bar" style="--traffic-height: 42%"><span>Sen</span></div>
            <div class="traffic-bar" style="--traffic-height: 58%"><span>Sel</span></div>
            <div class="traffic-bar" style="--traffic-height: 48%"><span>Rab</span></div>
            <div class="traffic-bar" style="--traffic-height: 74%"><span>Kam</span></div>
            <div class="traffic-bar" style="--traffic-height: 63%"><span>Jum</span></div>
            <div class="traffic-bar" style="--traffic-height: 86%"><span>Sab</span></div>
            <div class="traffic-bar" style="--traffic-height: 69%"><span>Min</span></div>
        </div>
        <p class="admin-widget-note">Hubungkan Google Analytics untuk menampilkan kunjungan aktual.</p>
    </article>
    <article class="admin-widget admin-clock-widget">
        <p class="eyebrow">Waktu lokal</p>
        <div class="admin-clock" data-clock>00:00:00</div>
        <p class="admin-clock-date" data-clock-date>Memuat tanggal...</p>
        <span class="admin-clock-zone">Asia/Jakarta</span>
    </article>
    <article class="admin-widget admin-calendar-widget">
        <div class="admin-widget-heading"><div><p class="eyebrow">Agenda</p><h2 data-calendar-title>Kalender</h2></div></div>
        <div class="admin-calendar" data-calendar aria-label="Kalender bulan berjalan"></div>
    </article>
</section>
<section class="admin-panel">
    <div class="admin-panel-heading"><div><p class="eyebrow">Konten terbaru</p><h2>Post diperbarui</h2></div><a class="admin-button" href="edit.php?type=posts">Buat post +</a></div>
    <?php if ($recentPosts === []): ?>
        <p class="admin-muted">Belum ada post. Buat post pertama untuk mulai mengisi website.</p>
    <?php else: ?>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Judul</th><th>Status</th><th>Diperbarui</th></tr></thead><tbody>
        <?php foreach ($recentPosts as $post): ?><tr><td><?= e($post['title']) ?></td><td><span class="status-badge <?= e($post['status']) ?>"><?= $post['status'] === 'published' ? 'Terbit' : 'Draft' ?></span></td><td><?= e($post['updated_at']) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<script src="../admin-dashboard.js" defer></script>
<?php admin_footer(); ?>
