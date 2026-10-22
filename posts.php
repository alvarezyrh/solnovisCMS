<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/site-layout.php';

$posts = db()->query('SELECT title, slug, excerpt, created_at FROM posts WHERE status = "published" ORDER BY created_at DESC, id DESC')->fetchAll();
site_header('Post', 'Artikel dan informasi terbaru dari Solnovis.', '');
?>
<main>
    <section class="page-hero section-wrap"><p class="eyebrow">Jurnal Solnovis</p><h1>Wawasan dan kabar<br><em>komoditas Indonesia.</em></h1><p class="page-lead">Informasi terbaru seputar produk, sumber lokal, dan perdagangan komoditas.</p></section>
    <section class="commodity-section section-wrap">
        <?php if ($posts): ?><div class="info-grid"><?php foreach ($posts as $post): ?>
            <article class="info-card"><span><?= e(date('d M Y', strtotime($post['created_at']))) ?></span><h3><?= e($post['title']) ?></h3><p><?= e($post['excerpt']) ?></p><a class="text-link" href="post.php?slug=<?= rawurlencode($post['slug']) ?>">Baca selengkapnya <span aria-hidden="true">→</span></a></article>
        <?php endforeach; ?></div><?php else: ?><div class="product-note"><span class="note-icon" aria-hidden="true">i</span><p>Belum ada post yang diterbitkan. Silakan kembali untuk membaca informasi terbaru.</p></div><?php endif; ?>
    </section>
</main>
<?php site_footer(); ?>
