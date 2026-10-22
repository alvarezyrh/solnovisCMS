<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
$user = require_admin();
require_once __DIR__ . '/../includes/admin-layout.php';

$type = (string) ($_GET['type'] ?? '');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$configs = [
    'posts' => ['table' => 'posts', 'title' => 'Post', 'active' => 'posts'],
    'pages' => ['table' => 'pages', 'title' => 'Halaman', 'active' => 'pages'],
    'menus' => ['table' => 'menus', 'title' => 'Menu', 'active' => 'menus'],
    'hero_slides' => ['table' => 'hero_slides', 'title' => 'Slider Hero', 'active' => 'sliders'],
    'profiles' => ['table' => 'users', 'title' => 'Profile admin', 'active' => 'profiles'],
];
if (!isset($configs[$type])) {
    http_response_code(404);
    exit('Jenis konten tidak ditemukan.');
}
if ($type === 'profiles' && $user['role'] !== 'admin') {
    http_response_code(403);
    exit('Hanya administrator yang dapat mengelola akun.');
}
$config = $configs[$type];
$fields = [
    'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'status' => 'draft',
    'is_home' => 0, 'label' => '', 'url' => '', 'sort_order' => 0, 'is_cta' => 0,
    'is_visible' => 1, 'name' => '', 'email' => '', 'role' => 'editor', 'kicker' => '',
    'image_url' => '', 'caption' => '', 'cta_label' => 'Hubungi kami', 'cta_url' => 'page.php?slug=contact',
];
$error = '';
if ($id > 0) {
    $query = db()->prepare('SELECT * FROM ' . $config['table'] . ' WHERE id = ?');
    $query->execute([$id]);
    $existing = $query->fetch();
    if (!$existing) {
        http_response_code(404);
        exit('Data tidak ditemukan.');
    }
    $fields = array_merge($fields, $existing);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (array_keys($fields) as $field) {
        if (isset($_POST[$field]) && is_scalar($_POST[$field])) {
            $fields[$field] = trim((string) $_POST[$field]);
        }
    }
    $checkboxes = $type === 'pages' ? ['is_home'] : (in_array($type, ['menus', 'hero_slides'], true) ? ($type === 'menus' ? ['is_cta', 'is_visible'] : ['is_visible']) : []);
    foreach ($checkboxes as $checkbox) {
        $fields[$checkbox] = isset($_POST[$checkbox]) ? 1 : 0;
    }

    try {
        if ($type === 'posts' || $type === 'pages') {
            $fields['slug'] = slugify((string) $fields['slug'] !== '' ? (string) $fields['slug'] : (string) $fields['title']);
            if ($fields['title'] === '' || $fields['slug'] === '') {
                throw new InvalidArgumentException('Judul wajib diisi dan harus menghasilkan slug yang valid.');
            }
            if (!in_array($fields['status'], ['draft', 'published'], true)) {
                throw new InvalidArgumentException('Status konten tidak valid.');
            }
            $duplicate = db()->prepare('SELECT id FROM ' . $config['table'] . ' WHERE slug = ? AND id != ?');
            $duplicate->execute([$fields['slug'], $id]);
            if ($duplicate->fetch()) {
                throw new InvalidArgumentException('Slug sudah digunakan. Pilih slug yang berbeda.');
            }
            db()->beginTransaction();
            if ($type === 'pages' && (int) $fields['is_home'] === 1) {
                db()->exec('UPDATE pages SET is_home = 0');
            }
            if ($id > 0) {
                $statement = db()->prepare('UPDATE ' . $config['table'] . ' SET title = ?, slug = ?, excerpt = ?, content = ?, status = ?, ' . ($type === 'pages' ? 'is_home = ?, ' : '') . 'updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                $values = [$fields['title'], $fields['slug'], $fields['excerpt'], $fields['content'], $fields['status']];
                if ($type === 'pages') {
                    $values[] = (int) $fields['is_home'];
                }
                $values[] = $id;
                $statement->execute($values);
            } else {
                $statement = db()->prepare('INSERT INTO ' . $config['table'] . ' (title, slug, excerpt, content, status, ' . ($type === 'pages' ? 'is_home' : 'author_id') . ') VALUES (?, ?, ?, ?, ?, ?)');
                $statement->execute([$fields['title'], $fields['slug'], $fields['excerpt'], $fields['content'], $fields['status'], $type === 'pages' ? (int) $fields['is_home'] : (int) $user['id']]);
            }
            db()->commit();
        } elseif ($type === 'hero_slides') {
            $fields['title'] = trim((string) $fields['title']);
            $fields['image_url'] = trim((string) $fields['image_url']);
            $fields['cta_url'] = trim((string) $fields['cta_url']);
            if ($fields['title'] === '' || !filter_var($fields['image_url'], FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $fields['image_url'])) {
                throw new InvalidArgumentException('Judul dan URL gambar HTTPS/HTTP yang valid wajib diisi.');
            }
            if ($fields['cta_url'] !== '' && !preg_match('/^(index\.php|posts\.php|page\.php\?slug=[a-z0-9-]+|https:\/\/[^\s]+)$/i', $fields['cta_url'])) {
                throw new InvalidArgumentException('URL tombol harus berupa URL internal yang didukung atau URL HTTPS.');
            }
            $values = [$fields['kicker'], $fields['title'], $fields['excerpt'], $fields['image_url'], $fields['label'], $fields['caption'], $fields['cta_label'], $fields['cta_url'], max(0, (int) $fields['sort_order']), (int) $fields['is_visible']];
            if ($id > 0) {
                $statement = db()->prepare('UPDATE hero_slides SET kicker = ?, title = ?, excerpt = ?, image_url = ?, label = ?, caption = ?, cta_label = ?, cta_url = ?, sort_order = ?, is_visible = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                $statement->execute([...$values, $id]);
            } else {
                $statement = db()->prepare('INSERT INTO hero_slides (kicker, title, excerpt, image_url, label, caption, cta_label, cta_url, sort_order, is_visible) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $statement->execute($values);
            }
        } elseif ($type === 'menus') {
            $fields['label'] = trim((string) $fields['label']);
            $fields['url'] = trim((string) $fields['url']);
            if ($fields['label'] === '' || !preg_match('/^(index\.php|posts\.php|page\.php\?slug=[a-z0-9-]+|https:\/\/[^\s]+)$/i', $fields['url'])) {
                throw new InvalidArgumentException('Isi label dan URL internal yang didukung atau URL HTTPS.');
            }
            $values = [$fields['label'], $fields['url'], max(0, (int) $fields['sort_order']), (int) $fields['is_cta'], (int) $fields['is_visible']];
            if ($id > 0) {
                $statement = db()->prepare('UPDATE menus SET label = ?, url = ?, sort_order = ?, is_cta = ?, is_visible = ? WHERE id = ?');
                $statement->execute([...$values, $id]);
            } else {
                $statement = db()->prepare('INSERT INTO menus (label, url, sort_order, is_cta, is_visible) VALUES (?, ?, ?, ?, ?)');
                $statement->execute($values);
            }
        } else {
            $fields['name'] = trim((string) $fields['name']);
            $fields['email'] = strtolower(trim((string) $fields['email']));
            $password = (string) ($_POST['password'] ?? '');
            $role = (string) $fields['role'];
            if ($fields['name'] === '' || !filter_var($fields['email'], FILTER_VALIDATE_EMAIL) || !in_array($role, ['admin', 'editor'], true)) {
                throw new InvalidArgumentException('Nama, email valid, dan peran wajib diisi.');
            }
            if ($id === 0 && strlen($password) < 12) {
                throw new InvalidArgumentException('Kata sandi akun baru minimal 12 karakter.');
            }
            if ($password !== '' && strlen($password) < 12) {
                throw new InvalidArgumentException('Kata sandi minimal 12 karakter.');
            }
            if ($id > 0 && $id === (int) $user['id'] && $role !== 'admin') {
                throw new InvalidArgumentException('Peran akun yang sedang digunakan tidak dapat diturunkan.');
            }
            if ($id > 0) {
                $statement = db()->prepare('UPDATE users SET name = ?, email = ?, role = ?' . ($password !== '' ? ', password_hash = ?' : '') . ' WHERE id = ?');
                $values = [$fields['name'], $fields['email'], $role];
                if ($password !== '') {
                    $values[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $values[] = $id;
                $statement->execute($values);
                if ($id === (int) $user['id']) {
                    $_SESSION['user']['name'] = $fields['name'];
                    $_SESSION['user']['email'] = $fields['email'];
                }
            } else {
                $statement = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
                $statement->execute([$fields['name'], $fields['email'], password_hash($password, PASSWORD_DEFAULT), $role]);
            }
        }
        flash($config['title'] . ' berhasil disimpan.');
        redirect('manage.php?type=' . rawurlencode($type));
    } catch (InvalidArgumentException $exception) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        if (str_contains(strtolower($exception->getMessage()), 'unique')) {
            $error = 'Data tersebut sudah digunakan. Pastikan email atau slug tidak duplikat.';
        } else {
            throw $exception;
        }
    }
}

$title = ($id > 0 ? 'Edit ' : 'Tambah ') . $config['title'];
admin_header($title, $config['active']);
?>
<section class="admin-panel admin-editor">
    <?php if ($error !== ''): ?><p class="admin-alert error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form class="admin-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($type === 'hero_slides'): ?>
            <label>Label kecil<input name="kicker" required value="<?= e($fields['kicker']) ?>"></label>
            <label>Judul slide<input name="title" required value="<?= e($fields['title']) ?>"></label>
            <label>Deskripsi<textarea name="excerpt" rows="3" required><?= e($fields['excerpt']) ?></textarea></label>
            <label>URL gambar<input type="url" name="image_url" required value="<?= e($fields['image_url']) ?>" placeholder="https://..."><small>Gunakan URL gambar HTTP/HTTPS. Ukuran landscape direkomendasikan.</small></label>
            <label>Label pada gambar<input name="label" value="<?= e($fields['label']) ?>"></label>
            <label>Caption pada gambar<textarea name="caption" rows="2"><?= e($fields['caption']) ?></textarea></label>
            <label>Teks tombol<input name="cta_label" value="<?= e($fields['cta_label']) ?>"></label>
            <label>URL tombol<input name="cta_url" value="<?= e($fields['cta_url']) ?>" placeholder="page.php?slug=contact"></label>
            <label>Urutan<input type="number" min="0" name="sort_order" value="<?= (int) $fields['sort_order'] ?>"></label>
            <label class="admin-checkbox"><input type="checkbox" name="is_visible" value="1" <?= (int) $fields['is_visible'] === 1 ? 'checked' : '' ?>> Tampilkan slide</label>
        <?php elseif ($type === 'posts' || $type === 'pages'): ?>
            <label>Judul<input name="title" required value="<?= e($fields['title']) ?>"></label>
            <label>Slug<input name="slug" value="<?= e($fields['slug']) ?>" placeholder="otomatis-dari-judul"><small>Dipakai sebagai alamat konten dan hanya menggunakan huruf kecil, angka, serta tanda hubung.</small></label>
            <label>Ringkasan<textarea name="excerpt" rows="3"><?= e($fields['excerpt']) ?></textarea></label>
            <label>Konten<textarea name="content" rows="12"><?= e($fields['content']) ?></textarea><small>Gunakan baris kosong untuk memisahkan paragraf. HTML akan ditampilkan sebagai teks biasa.</small></label>
            <label>Status<select name="status"><option value="draft" <?= $fields['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= $fields['status'] === 'published' ? 'selected' : '' ?>>Terbit</option></select></label>
            <?php if ($type === 'pages'): ?><label class="admin-checkbox"><input type="checkbox" name="is_home" value="1" <?= (int) $fields['is_home'] === 1 ? 'checked' : '' ?>> Jadikan halaman beranda</label><?php endif; ?>
        <?php elseif ($type === 'menus'): ?>
            <label>Label menu<input name="label" required value="<?= e($fields['label']) ?>"></label>
            <label>URL<input name="url" required value="<?= e($fields['url']) ?>" placeholder="page.php?slug=about"><small>Gunakan index.php, posts.php, page.php?slug=..., atau URL HTTPS.</small></label>
            <label>Urutan<input type="number" min="0" name="sort_order" value="<?= (int) $fields['sort_order'] ?>"></label>
            <label class="admin-checkbox"><input type="checkbox" name="is_cta" value="1" <?= (int) $fields['is_cta'] === 1 ? 'checked' : '' ?>> Tampilkan sebagai tombol CTA</label>
            <label class="admin-checkbox"><input type="checkbox" name="is_visible" value="1" <?= (int) $fields['is_visible'] === 1 ? 'checked' : '' ?>> Tampilkan di navigasi</label>
        <?php else: ?>
            <label>Nama<input name="name" autocomplete="name" required value="<?= e($fields['name']) ?>"></label>
            <label>Email<input type="email" name="email" autocomplete="email" required value="<?= e($fields['email']) ?>"></label>
            <label>Peran<select name="role"><option value="admin" <?= $fields['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option><option value="editor" <?= $fields['role'] === 'editor' ? 'selected' : '' ?>>Editor</option></select></label>
            <label>Kata sandi <?= $id > 0 ? '(kosongkan jika tidak diubah)' : '(minimal 12 karakter)' ?><input type="password" name="password" autocomplete="new-password" <?= $id === 0 ? 'required minlength="12"' : 'minlength="12"' ?>></label>
        <?php endif; ?>
        <div class="admin-form-actions"><button class="admin-button" type="submit">Simpan <?= e(strtolower($config['title'])) ?></button><a class="admin-cancel" href="manage.php?type=<?= e($type) ?>">Batal</a></div>
    </form>
</section>
<?php admin_footer(); ?>
