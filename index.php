<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/site-layout.php';

$home = db()->query('SELECT * FROM pages WHERE is_home = 1 AND status = "published" LIMIT 1')->fetch();
if (!$home) {
    $home = db()->query('SELECT * FROM pages WHERE status = "published" ORDER BY id LIMIT 1')->fetch();
}
$recentPosts = db()->query('SELECT title, slug, excerpt, created_at FROM posts WHERE status = "published" ORDER BY created_at DESC, id DESC LIMIT 3')->fetchAll();
$heroSlides = db()->query('SELECT * FROM hero_slides WHERE is_visible = 1 ORDER BY sort_order, id')->fetchAll();
$pageTitle = $home['title'] ?? setting('site_name', 'PT Solnovis Main Global');
$description = $home['excerpt'] ?? setting('tagline', 'Komoditas Indonesia untuk dunia.');

site_header($pageTitle, $description, 'home');
?>
<main>
    <?php if ($home): ?>
        <section class="hero hero-slider" aria-label="Cerita Solnovis">
            <div class="hero-slides">
                <?php foreach ($heroSlides as $slideIndex => $slide): ?>
                    <article class="hero-slide <?= $slideIndex === 0 ? 'is-active' : '' ?>" data-slide-index="<?= (int) $slideIndex ?>" aria-hidden="<?= $slideIndex === 0 ? 'false' : 'true' ?>">
                        <div class="hero-content">
                            <p class="eyebrow"><span class="eyebrow-line"></span> <?= e($slide['kicker']) ?></p>
                            <h1><?= e($slide['title']) ?></h1>
                            <p class="hero-copy"><?= e($slide['excerpt']) ?></p>
                            <div class="hero-actions">
                                <a class="button button-primary" href="<?= e($slide['cta_url']) ?>"><?= e($slide['cta_label']) ?> <span aria-hidden="true">→</span></a>
                                <a class="text-link" href="page.php?slug=about">Kenali Solnovis <span aria-hidden="true">↗</span></a>
                            </div>
                            <div class="hero-note"><span class="note-dot"></span> Berakar di Jawa Barat, terbuka untuk pasar dunia</div>
                            <div class="hero-highlights" aria-label="Fokus perusahaan">
                                <div class="hero-highlight"><strong>04</strong><span>Komoditas fokus</span></div>
                                <div class="hero-highlight"><strong>Lokal</strong><span>Jaringan sumber</span></div>
                                <div class="hero-highlight"><strong>Global</strong><span>Orientasi pasar</span></div>
                            </div>
                        </div>
                        <div class="hero-art" aria-label="<?= e($slide['title']) ?>" role="img">
                            <img class="hero-slide-image" src="<?= e($slide['image_url']) ?>" alt="<?= e($slide['title']) ?>">
                            <div class="hero-art-overlay"></div>
                            <div class="art-label"><span><?= e($slide['label']) ?></span><strong>Grown with purpose.</strong></div>
                            <div class="art-stamp">DARI<br>JAWA BARAT</div><div class="art-coordinate">LOCAL SOURCE · GLOBAL MARKET</div>
                            <p class="art-caption"><?= e($slide['caption']) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if (count($heroSlides) > 1): ?>
                <div class="hero-slider-controls" aria-label="Kontrol slider">
                    <button class="hero-slider-arrow hero-slider-prev" type="button" aria-label="Slide sebelumnya">←</button>
                    <div class="hero-slider-dots">
                        <?php foreach ($heroSlides as $slideIndex => $slide): ?><button class="hero-slider-dot <?= $slideIndex === 0 ? 'is-active' : '' ?>" type="button" data-slide-to="<?= (int) $slideIndex ?>" aria-label="Tampilkan slide <?= (int) $slideIndex + 1 ?>" aria-current="<?= $slideIndex === 0 ? 'true' : 'false' ?>"></button><?php endforeach; ?>
                    </div>
                    <button class="hero-slider-arrow hero-slider-next" type="button" aria-label="Slide berikutnya">→</button>
                </div>
            <?php endif; ?>
        </section>
        <section class="intro-section section-wrap">
            <div class="section-heading"><p class="eyebrow">Dari sumber yang tepat</p><h2><?= e($home['title']) ?><br><em>kemitraan yang bernilai.</em></h2></div>
            <div class="intro-copy"><?php render_plain_content($home['content']); ?><a class="text-link" href="page.php?slug=about">Tentang perusahaan kami <span aria-hidden="true">→</span></a></div>
        </section>
    <?php else: ?>
        <section class="page-hero section-wrap"><p class="eyebrow">Website sedang disiapkan</p><h1>Konten beranda belum tersedia.</h1><p class="page-lead">Publikasikan halaman dan tetapkan sebagai beranda melalui dashboard CMS.</p><a class="button button-primary" href="admin/login.php">Masuk ke CMS →</a></section>
    <?php endif; ?>

    <section class="commodity-section section-wrap">
        <div class="section-topline"><div><p class="eyebrow">Wawasan terbaru</p><h2>Catatan dari Solnovis.</h2></div><a class="text-link" href="posts.php">Semua post <span aria-hidden="true">→</span></a></div>
        <?php if ($recentPosts): ?>
            <div class="info-grid"><?php foreach ($recentPosts as $post): ?>
                <article class="info-card"><span><?= e(date('d M Y', strtotime($post['created_at']))) ?></span><h3><?= e($post['title']) ?></h3><p><?= e($post['excerpt']) ?></p><a class="text-link" href="post.php?slug=<?= rawurlencode($post['slug']) ?>">Baca selengkapnya <span aria-hidden="true">→</span></a></article>
            <?php endforeach; ?></div>
        <?php else: ?>
            <div class="value-strip"><p><strong>Kapulaga</strong>Rempah aromatik dari jaringan sumber Indonesia.</p><p><strong>Pala dan jahe</strong>Komoditas rempah untuk kebutuhan pengolahan dan perdagangan.</p><p><strong>Kemiri</strong>Hasil pertanian untuk beragam kebutuhan pengolahan pangan.</p></div>
        <?php endif; ?>
    </section>
    <section class="approach-section"><div class="approach-inner section-wrap">
        <div><p class="eyebrow eyebrow-light">Cara kami bekerja</p><h2>Dari sumber lokal<br>menuju kebutuhan global.</h2></div>
        <div class="approach-copy"><p>Kami memahami setiap pasar memiliki kebutuhan berbeda. Pembicaraan tentang spesifikasi, ketersediaan, dan kebutuhan pengiriman dimulai dengan komunikasi yang jelas.</p><a class="button button-light" href="page.php?slug=services">Pelajari layanan kami <span aria-hidden="true">→</span></a></div>
        <div class="approach-steps" aria-label="Tahapan kerja"><div><span>01</span><p>Pencarian sumber</p></div><div><span>02</span><p>Peninjauan kebutuhan</p></div><div><span>03</span><p>Koordinasi perdagangan</p></div><div><span>04</span><p>Pengiriman ke pasar tujuan</p></div></div>
    </div></section>
    <section class="closing-cta section-wrap"><p class="eyebrow">Mari mulai percakapan</p><h2>Punya kebutuhan komoditas<br>untuk pasar Anda?</h2><a class="button button-primary" href="page.php?slug=contact">Diskusikan kebutuhan Anda <span aria-hidden="true">→</span></a></section>
</main>
<?php site_footer(); ?>
