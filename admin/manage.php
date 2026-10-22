<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
$user = require_admin();
require_once __DIR__ . '/../includes/admin-layout.php';

$type = (string) ($_GET['type'] ?? '');
$config = [
    'posts' => ['table' => 'posts', 'title' => 'Post', 'active' => 'posts'],
    'pages' => ['table' => 'pages', 'title' => 'Halaman', 'active' => 'pages'],
    'menus' => ['table' => 'menus', 'title' => 'Menu navigasi', 'active' => 'menus'],
    'hero_slides' => ['table' => 'hero_slides', 'title' => 'Slider Hero', 'active' => 'sliders'],
    'profiles' => ['table' => 'users', 'title' => 'Profiles admin', 'active' => 'profiles'],
];
if (!isset($config[$type])) {
    http_response_code(404);
    exit('Jenis konten tidak ditemukan.');
}
if ($type === 'profiles' && $user['role'] !== 'admin') {
    http_response_code(403);
    exit('Hanya administrator yang dapat mengelola akun.');
}
$item = $config[$type];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            $error = 'Item yang dipilih tidak valid.';
        } elseif ($type === 'profiles' && ((int) $id === (int) $user['id'] || (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn() <= 1)) {
            $error = 'Akun yang sedang digunakan atau satu-satunya admin tidak dapat dihapus.';
        } else {
            $delete = db()->prepare('DELETE FROM ' . $item['table'] . ' WHERE id = ?');
            $delete->execute([$id]);
            flash('Item berhasil dihapus.');
            redirect('manage.php?type=' . rawurlencode($type));
        }
    }
}

if ($type === 'posts') {
    $rows = db()->query('SELECT id, title, slug, status, updated_at FROM posts ORDER BY updated_at DESC, id DESC')->fetchAll();
} elseif ($type === 'pages') {
    $rows = db()->query('SELECT id, title, slug, status, is_home, updated_at FROM pages ORDER BY is_home DESC, title')->fetchAll();
} elseif ($type === 'menus') {
    $rows = db()->query('SELECT id, label AS title, url AS slug, is_visible AS status, sort_order, is_cta FROM menus ORDER BY sort_order, id')->fetchAll();
} elseif ($type === 'hero_slides') {
    $rows = db()->query('SELECT id, title, image_url AS slug, is_visible AS status, sort_order, kicker FROM hero_slides ORDER BY sort_order, id')->fetchAll();
} else {
    $rows = db()->query('SELECT id, name AS title, email AS slug, role AS status, created_at AS updated_at FROM users ORDER BY name')->fetchAll();
}

admin_header($item['title'], $item['active']);
if ($error !== ''): ?><p class="admin-alert error" role="alert"><?= e($error) ?></p><?php endif; ?>
<section class="admin-panel">
    <div class="admin-panel-heading"><div><p class="eyebrow">Kelola konten</p><h2><?= e($item['title']) ?></h2></div>
        <a class="admin-button" href="edit.php?type=<?= e($type) ?>"><?= $type === 'profiles' ? 'Tambah akun' : 'Tambah ' . e(strtolower($item['title'])) ?> +</a>
    </div>
    <?php if ($rows === []): ?>
        <p class="admin-muted">Belum ada data untuk ditampilkan.</p>
    <?php else: ?>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr>
            <?php if ($type === 'menus'): ?><th>Label</th><th>URL</th><th>Urutan</th><th>Status</th>
            <?php elseif ($type === 'hero_slides'): ?><th>Judul</th><th>Gambar</th><th>Urutan</th><th>Status</th>
            <?php elseif ($type === 'profiles'): ?><th>Nama</th><th>Email</th><th>Peran</th><th>Dibuat</th>
            <?php else: ?><th>Judul</th><th>Slug</th><th>Status</th><th>Diperbarui</th><?php endif; ?>
            <th>Aksi</th></tr></thead><tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php if ($type === 'menus'): ?><td><?= e($row['title']) ?><?= (int) $row['is_cta'] === 1 ? ' · CTA' : '' ?></td><td class="admin-url"><?= e($row['slug']) ?></td><td><?= (int) $row['sort_order'] ?></td><td><?= (int) $row['status'] === 1 ? 'Tampil' : 'Disembunyikan' ?></td>
                <?php elseif ($type === 'hero_slides'): ?><td><strong><?= e($row['title']) ?></strong><small class="admin-table-subtitle"><?= e($row['kicker']) ?></small></td><td class="admin-url"><?= e($row['slug']) ?></td><td><?= (int) $row['sort_order'] ?></td><td><?= (int) $row['status'] === 1 ? 'Tampil' : 'Disembunyikan' ?></td>
                <?php elseif ($type === 'profiles'): ?><td><?= e($row['title']) ?></td><td><?= e($row['slug']) ?></td><td><?= e(ucfirst($row['status'])) ?></td><td><?= e($row['updated_at']) ?></td>
                <?php else: ?><td><?= e($row['title']) ?><?= $type === 'pages' && (int) $row['is_home'] === 1 ? ' · Beranda' : '' ?></td><td><?= e($row['slug']) ?></td><td><span class="status-badge <?= e($row['status']) ?>"><?= $row['status'] === 'published' ? 'Terbit' : 'Draft' ?></span></td><td><?= e($row['updated_at']) ?></td><?php endif; ?>
                <td class="admin-actions">
                    <a class="admin-action-link" href="edit.php?type=<?= e($type) ?>&id=<?= (int) $row['id'] ?>">Edit</a>
                    <?php if ($type !== 'profiles' || (int) $row['id'] !== (int) $user['id']): ?>
                    <form method="post" onsubmit="return confirm('Hapus item ini?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><button class="admin-action-link danger" type="submit">Hapus</button></form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<?php admin_footer(); ?>
