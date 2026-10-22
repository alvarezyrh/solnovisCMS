<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/site-layout.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$query = db()->prepare('SELECT * FROM pages WHERE slug = ? AND status = "published" LIMIT 1');
$query->execute([$slug]);
$page = $query->fetch();
if (!$page) {
    http_response_code(404);
    site_header('Halaman tidak ditemukan', setting('tagline'), '');
    ?>
    <main><section class="page-hero section-wrap"><p class="eyebrow">404 · Halaman tidak ditemukan</p><h1>Halaman yang Anda cari tidak tersedia.</h1><p class="page-lead">Tautan mungkin telah berubah atau halaman belum diterbitkan.</p><a class="button button-primary" href="index.php">Kembali ke beranda →</a></section></main>
    <?php
    site_footer();
    exit;
}
if ((int) $page['is_home'] === 1) {
    redirect('index.php');
}

site_header($page['title'], $page['excerpt'], $page['slug']);
?>
<main>
    <section class="page-hero section-wrap">
        <p class="eyebrow"><?= e(setting('site_name', 'PT Solnovis Main Global')) ?></p>
        <h1><?= e($page['title']) ?></h1>
        <p class="page-lead"><?= e($page['excerpt']) ?></p>
    </section>
    <section class="story-section section-wrap dynamic-page-content">
        <div class="story-art" aria-hidden="true"><span class="story-art-label">LOCAL<br>ROOTS<br><i>GLOBAL<br>REACH</i></span><span class="story-art-orbit"></span></div>
        <div class="story-copy"><p class="eyebrow">Informasi</p><h2><?= e($page['title']) ?></h2><?php render_plain_content($page['content']); ?></div>
    </section>
    <?php if ($page['slug'] === 'product'):
        $posts = db()->query('SELECT title, slug, excerpt FROM posts WHERE status = "published" ORDER BY created_at DESC')->fetchAll(); ?>
        <section class="commodity-section section-wrap"><div class="section-topline"><div><p class="eyebrow">Dari jaringan sumber lokal</p><h2>Komoditas dan informasi terbaru.</h2></div></div>
        <?php if ($posts): ?><div class="info-grid"><?php foreach ($posts as $post): ?><article class="info-card"><span>KOMODITAS / WAWASAN</span><h3><?= e($post['title']) ?></h3><p><?= e($post['excerpt']) ?></p><a class="text-link" href="post.php?slug=<?= rawurlencode($post['slug']) ?>">Baca selengkapnya <span aria-hidden="true">→</span></a></article><?php endforeach; ?></div>
        <?php else: ?><div class="value-strip"><p><strong>Kapulaga</strong>Rempah aromatik, digunakan dalam beragam tradisi kuliner.</p><p><strong>Pala dan jahe</strong>Rempah Indonesia untuk kebutuhan perdagangan dan pengolahan.</p><p><strong>Kemiri</strong>Hasil pertanian untuk kebutuhan pengolahan pangan.</p></div><?php endif; ?></section>
    <?php endif; ?>
    <?php if ($page['slug'] === 'contact'): ?>
        <section class="contact-section section-wrap">
            <div class="contact-aside"><p class="eyebrow">Permintaan bisnis</p><h2>Mulai dengan<br>detail kebutuhan.</h2><p>Sertakan nama komoditas, spesifikasi, perkiraan volume, dan negara tujuan.</p></div>
            <div class="contact-form">
                <?php if (setting('contact_email') !== ''): ?><p><strong>Email</strong><br><a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></p><?php endif; ?>
                <?php if (setting('contact_phone') !== ''): ?><p><strong>Telepon</strong><br><?= e(setting('contact_phone')) ?></p><?php endif; ?>
                <p><strong>Lokasi</strong><br><?= e(setting('location', 'Jawa Barat, Indonesia')) ?></p>
                <?php if (setting('contact_email') !== ''): ?><a class="button button-primary" href="mailto:<?= e(setting('contact_email')) ?>">Kirim pertanyaan →</a><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php site_footer(); ?>
