<?php
declare(strict_types=1);

function site_header(string $title, string $description, string $activeSlug = ''): void
{
    $menuItems = db()->query('SELECT label, url, is_cta FROM menus WHERE is_visible = 1 ORDER BY sort_order, id')->fetchAll();
    ?>
    <!doctype html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="<?= e($description) ?>">
        <meta name="theme-color" content="#102c27">
        <title><?= e($title) ?> | <?= e(setting('site_name', 'PT Solnovis Main Global')) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="script.js" defer></script>
    </head>
    <body>
        <header class="site-header">
            <a class="brand" href="index.php" aria-label="<?= e(setting('site_name', 'PT Solnovis Main Global')) ?>, beranda">
                <span class="brand-mark" aria-hidden="true">S</span>
                <span class="brand-name"><?= e(strtoupper(setting('site_name', 'PT Solnovis Main Global'))) ?></span>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Buka menu navigasi"><span></span><span></span></button>
            <nav class="primary-nav" id="primary-nav" aria-label="Navigasi utama">
                <?php foreach ($menuItems as $item):
                    $slug = '';
                    if (preg_match('/[?&]slug=([a-z0-9-]+)/', $item['url'], $match)) {
                        $slug = $match[1];
                    } elseif ($item['url'] === 'index.php') {
                        $slug = 'home';
                    }
                    $isActive = $slug !== '' && $slug === $activeSlug;
                    ?>
                    <a class="<?= (int) $item['is_cta'] === 1 ? 'nav-cta ' : '' ?><?= $isActive ? 'active' : '' ?>" href="<?= e($item['url']) ?>"<?= $isActive ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?><?= (int) $item['is_cta'] === 1 ? ' <span aria-hidden="true">↗</span>' : '' ?></a>
                <?php endforeach; ?>
            </nav>
        </header>
    <?php
}

function site_footer(): void
{
    ?>
        <footer class="site-footer">
            <a class="brand brand-footer" href="index.php"><span class="brand-mark" aria-hidden="true">S</span><span class="brand-name"><?= e(strtoupper(setting('site_name', 'PT Solnovis Main Global'))) ?></span></a>
            <p><?= e(setting('tagline', 'Komoditas Indonesia untuk dunia.')) ?></p>
            <span class="footer-copyright">© <?= date('Y') ?> <?= e(setting('site_name', 'PT Solnovis Main Global')) ?></span>
        </footer>
    </body>
    </html>
    <?php
}

function render_plain_content(string $content): void
{
    $paragraphs = preg_split('/\R\s*\R/', trim($content)) ?: [];
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);
        if ($paragraph !== '') {
            echo '<p>' . nl2br(e($paragraph), false) . '</p>';
        }
    }
}
