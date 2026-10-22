<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';
define('DATABASE_PATH', getenv('SOLNOVIS_DB_PATH') ?: APP_ROOT . '/storage/cms.sqlite');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Strict',
        'path' => '/',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    if (!extension_loaded('pdo_sqlite')) {
        throw new RuntimeException('Ekstensi PHP pdo_sqlite wajib diaktifkan untuk menjalankan CMS ini.');
    }
    if (!is_dir(dirname(DATABASE_PATH)) && !mkdir(dirname(DATABASE_PATH), 0750, true) && !is_dir(dirname(DATABASE_PATH))) {
        throw new RuntimeException('Direktori penyimpanan CMS tidak dapat dibuat.');
    }

    $connection = new PDO('sqlite:' . DATABASE_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $connection->exec('PRAGMA foreign_keys = ON');
    initialize_database($connection);
    return $connection;
}

function initialize_database(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE COLLATE NOCASE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "admin" CHECK(role IN ("admin", "editor")),
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            excerpt TEXT NOT NULL DEFAULT "",
            content TEXT NOT NULL DEFAULT "",
            status TEXT NOT NULL DEFAULT "published" CHECK(status IN ("draft", "published")),
            is_home INTEGER NOT NULL DEFAULT 0,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        CREATE UNIQUE INDEX IF NOT EXISTS one_home_page ON pages(is_home) WHERE is_home = 1;
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            excerpt TEXT NOT NULL DEFAULT "",
            content TEXT NOT NULL DEFAULT "",
            status TEXT NOT NULL DEFAULT "draft" CHECK(status IN ("draft", "published")),
            author_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS menus (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            label TEXT NOT NULL,
            url TEXT NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_cta INTEGER NOT NULL DEFAULT 0,
            is_visible INTEGER NOT NULL DEFAULT 1
        );
        CREATE TABLE IF NOT EXISTS settings (
            setting_key TEXT PRIMARY KEY,
            setting_value TEXT NOT NULL DEFAULT ""
        );
        CREATE TABLE IF NOT EXISTS hero_slides (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kicker TEXT NOT NULL DEFAULT "",
            title TEXT NOT NULL,
            excerpt TEXT NOT NULL DEFAULT "",
            image_url TEXT NOT NULL,
            label TEXT NOT NULL DEFAULT "",
            caption TEXT NOT NULL DEFAULT "",
            cta_label TEXT NOT NULL DEFAULT "Hubungi kami",
            cta_url TEXT NOT NULL DEFAULT "page.php?slug=contact",
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_visible INTEGER NOT NULL DEFAULT 1,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $pageCount = (int) $pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn();
    if ($pageCount === 0) {
        $pages = [
            ['Menghubungkan hasil bumi Indonesia dengan dunia.', 'home', 'Kami menghadirkan rempah dan komoditas pertanian pilihan dari Jawa Barat untuk kebutuhan pasar global. Ceritakan kebutuhan produk, spesifikasi, dan tujuan pasar Anda untuk memulai percakapan.', 'Kami membangun koneksi antara sumber lokal dan kebutuhan pembeli, dengan proses yang mengutamakan komunikasi terbuka, pemahaman produk, dan kemitraan jangka panjang.', 1],
            ['Tentang Kami', 'about', 'Menghubungkan potensi lokal dengan peluang global.', 'PT Solnovis Main Global adalah perusahaan perdagangan ekspor yang berfokus pada rempah-rempah dan komoditas pertanian Indonesia, dengan jaringan sumber di Jawa Barat. Kami membangun koneksi antara pemasok lokal dan kebutuhan pembeli dengan komunikasi terbuka dan koordinasi yang jelas.', 0],
            ['Komoditas', 'product', 'Hasil bumi Indonesia, siap menjangkau pasar.', 'Kami berfokus pada rempah dan hasil pertanian dari jaringan sumber lokal. Spesifikasi, volume, kemasan, dan ketersediaan dapat dibicarakan sesuai kebutuhan pembeli.', 0],
            ['Layanan', 'services', 'Proses ekspor yang dimulai dengan komunikasi.', 'Kami membantu menghubungkan kebutuhan pembeli dengan sumber komoditas lokal dan mengoordinasikan proses perdagangan sesuai kesepakatan. Ruang lingkup kerja sama mencakup pencarian sumber, diskusi spesifikasi, koordinasi perdagangan, serta pembahasan kebutuhan pengiriman.', 0],
            ['Hubungi Kami', 'contact', 'Mari bicarakan kebutuhan komoditas Anda.', 'Ceritakan produk yang Anda cari, spesifikasi, perkiraan volume, dan negara tujuan. Tim kami akan meninjau informasi tersebut untuk pembahasan lebih lanjut.', 0],
        ];
        $insert = $pdo->prepare('INSERT INTO pages (title, slug, excerpt, content, is_home) VALUES (?, ?, ?, ?, ?)');
        foreach ($pages as $page) {
            $insert->execute($page);
        }
    }

    $menuCount = (int) $pdo->query('SELECT COUNT(*) FROM menus')->fetchColumn();
    if ($menuCount === 0) {
        $menuItems = [
            ['Beranda', 'index.php', 1, 0, 1],
            ['Tentang Kami', 'page.php?slug=about', 2, 0, 1],
            ['Komoditas', 'page.php?slug=product', 3, 0, 1],
            ['Layanan', 'page.php?slug=services', 4, 0, 1],
            ['Hubungi Kami', 'page.php?slug=contact', 5, 1, 1],
        ];
        $insert = $pdo->prepare('INSERT INTO menus (label, url, sort_order, is_cta, is_visible) VALUES (?, ?, ?, ?, ?)');
        foreach ($menuItems as $menuItem) {
            $insert->execute($menuItem);
        }
    }

    $defaults = [
        'site_name' => 'PT Solnovis Main Global',
        'tagline' => 'Komoditas Indonesia untuk dunia.',
        'contact_email' => '',
        'contact_phone' => '',
        'location' => 'Jawa Barat, Indonesia',
    ];
    $insert = $pdo->prepare('INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ($defaults as $key => $value) {
        $insert->execute([$key, $value]);
    }

    $slideCount = (int) $pdo->query('SELECT COUNT(*) FROM hero_slides')->fetchColumn();
    if ($slideCount === 0) {
        $slides = [
            ['Sumber pilihan Indonesia', 'Rempah pilihan, siap menjangkau pasar dunia.', 'Kami menghubungkan hasil bumi dari sumber terpercaya dengan kebutuhan pembeli global secara lebih jelas dan terukur.', 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=1200&q=85', 'INDONESIA', 'Dari tanah yang kaya, untuk kebutuhan yang berarti.', 'Jelajahi komoditas', 'page.php?slug=product', 1, 1],
            ['Kemitraan yang bertumbuh', 'Membangun nilai dari setiap hubungan.', 'Komunikasi terbuka, pemahaman spesifikasi, dan koordinasi yang rapi menjadi awal dari kemitraan jangka panjang.', 'https://images.unsplash.com/photo-1500076656116-558758c991c1?auto=format&fit=crop&w=1200&q=85', 'JAWA BARAT', 'Tumbuh bersama sumber lokal dan pasar global.', 'Kenali Solnovis', 'page.php?slug=about', 2, 1],
            ['Untuk kebutuhan pasar', 'Komoditas yang dipahami dari asalnya.', 'Kami membantu pembeli menemukan produk yang sesuai dengan kebutuhan volume, spesifikasi, dan tujuan pengiriman.', 'https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?auto=format&fit=crop&w=1200&q=85', 'SOURCE TO MARKET', 'Setiap detail penting dalam perdagangan.', 'Pelajari layanan', 'page.php?slug=services', 3, 1],
            ['Mari mulai percakapan', 'Ceritakan kebutuhan komoditas Anda.', 'Bagikan produk, spesifikasi, volume, dan negara tujuan. Tim kami siap membuka pembicaraan untuk langkah berikutnya.', 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=1200&q=85', 'GLOBAL MARKET', 'Satu percakapan dapat membuka peluang baru.', 'Hubungi kami', 'page.php?slug=contact', 4, 1],
        ];
        $insertSlide = $pdo->prepare('INSERT INTO hero_slides (kicker, title, excerpt, image_url, label, caption, cta_label, cta_url, sort_order, is_visible) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($slides as $slide) {
            $insertSlide->execute($slide);
        }
    }
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function setting(string $key, string $default = ''): string
{
    $query = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $query->execute([$key]);
    $value = $query->fetchColumn();
    return $value === false ? $default : (string) $value;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

function redirect(string $location): never
{
    header('Location: ' . $location, true, 303);
    exit;
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($message) ? $message : null;
}
