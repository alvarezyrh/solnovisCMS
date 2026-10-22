<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/admin-layout.php';

$keys = ['site_name', 'tagline', 'contact_email', 'contact_phone', 'location'];
$labels = [
    'site_name' => 'Nama website/perusahaan',
    'tagline' => 'Tagline',
    'contact_email' => 'Email kontak publik',
    'contact_phone' => 'Telepon kontak publik',
    'location' => 'Lokasi',
];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $values = [];
    foreach ($keys as $key) {
        $value = trim((string) ($_POST[$key] ?? ''));
        if ($key === 'contact_email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $error = 'Alamat email kontak tidak valid.';
            break;
        }
        $values[$key] = $value;
    }
    if ($error === '') {
        $update = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value');
        db()->beginTransaction();
        foreach ($values as $key => $value) {
            $update->execute([$key, $value]);
        }
        db()->commit();
        flash('Pengaturan website berhasil disimpan.');
        redirect('settings.php');
    }
}

admin_header('Settings website', 'settings');
if ($error !== ''): ?><p class="admin-alert error" role="alert"><?= e($error) ?></p><?php endif; ?>
<section class="admin-panel admin-editor">
    <form class="admin-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php foreach ($keys as $key): ?>
            <label><?= e($labels[$key]) ?><input name="<?= e($key) ?>" value="<?= e($_POST[$key] ?? setting($key)) ?>"<?= $key === 'contact_email' ? ' type="email"' : '' ?>></label>
        <?php endforeach; ?>
        <div class="admin-form-actions"><button class="admin-button" type="submit">Simpan settings</button></div>
    </form>
</section>
<?php admin_footer(); ?>
