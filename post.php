<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/site-layout.php';

$query = db()->prepare('SELECT title, slug, excerpt, content, created_at FROM posts WHERE slug = ? AND status = "published" LIMIT 1');
$query->execute([trim((string) ($_GET['slug'] ?? ''))]);
$post = $query->fetch();
if (!$post) {
    http_response_code(404);
    site_header('Post tidak ditemukan', setting('tagline'), '');
    ?><main><section class="page-hero section-wrap"><p class="eyebrow">404 · Post tidak ditemukan</p><h1>Informasi ini tidak tersedia.</h1><a class="button button-primary" href="posts.php">Kembali ke semua post →</a></section></main><?php
    site_footer();
    exit;
}
site_header($post['title'], $post['excerpt'], '');
?>
<main>
    <section class="page-hero section-wrap"><p class="eyebrow">Jurnal Solnovis · <?= e(date('d M Y', strtotime($post['created_at']))) ?></p><h1><?= e($post['title']) ?></h1><p class="page-lead"><?= e($post['excerpt']) ?></p></section>
    <section class="story-section section-wrap dynamic-page-content"><div class="story-art" aria-hidden="true"><span class="story-art-label">LOCAL<br>ROOTS<br><i>GLOBAL<br>REACH</i></span><span class="story-art-orbit"></span></div><div class="story-copy"><?php render_plain_content($post['content']); ?><a class="text-link" href="posts.php">Kembali ke jurnal <span aria-hidden="true">→</span></a></div></section>
</main>
<?php site_footer(); ?>
