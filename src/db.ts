import fs from 'node:fs';
import path from 'node:path';
import bcrypt from 'bcryptjs';

export interface User {
  id: number;
  name: string;
  email: string;
  password_hash: string;
  role: 'admin' | 'editor';
  created_at: string;
}

export interface Page {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  content: string;
  status: 'draft' | 'published';
  is_home: number;
  updated_at: string;
}

export interface Post {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  content: string;
  status: 'draft' | 'published';
  author_id: number | null;
  created_at: string;
  updated_at: string;
}

export interface Menu {
  id: number;
  label: string;
  url: string;
  sort_order: number;
  is_cta: number;
  is_visible: number;
}

export interface HeroSlide {
  id: number;
  kicker: string;
  title: string;
  excerpt: string;
  image_url: string;
  label: string;
  caption: string;
  cta_label: string;
  cta_url: string;
  sort_order: number;
  is_visible: number;
  updated_at: string;
}

export interface Settings {
  site_name: string;
  tagline: string;
  contact_email: string;
  contact_phone: string;
  location: string;
  [key: string]: string;
}

interface DatabaseState {
  users: User[];
  pages: Page[];
  posts: Post[];
  menus: Menu[];
  hero_slides: HeroSlide[];
  settings: Settings;
  nextId: {
    users: number;
    pages: number;
    posts: number;
    menus: number;
    hero_slides: number;
  };
}

const STORAGE_DIR = path.resolve(process.cwd(), 'storage');
const STORAGE_FILE = path.join(STORAGE_DIR, 'cms-data.json');

function getInitialState(): DatabaseState {
  const defaultAdminPasswordHash = bcrypt.hashSync('admin12345678', 10);
  const now = new Date().toISOString().replace('T', ' ').substring(0, 19);

  return {
    users: [
      {
        id: 1,
        name: 'Administrator',
        email: 'admin@solnovis.com',
        password_hash: defaultAdminPasswordHash,
        role: 'admin',
        created_at: now
      }
    ],
    pages: [
      {
        id: 1,
        title: 'Menghubungkan Rempah Berkualitas Indonesia dengan Pasar Dunia',
        slug: 'home',
        excerpt: 'PT Solnovis Main Global menghadirkan rempah pilihan dan komoditas pertanian unggulan dari Jawa Barat untuk pasar ekspor global dengan standar mutu ketat, sertifikasi karantina, dan keandalan logistik.',
        content: 'Kami membangun kemitraan erat dengan kelompok tani lokal di dataran tinggi Jawa Barat, mengintegrasikan proses sortasi modern, pengendalian kadar air, dan pengujian laboratorium terakreditasi untuk memastikan rempah Indonesia hadir di panggung perdagangan internasional dengan standar mutu terbaik.',
        status: 'published',
        is_home: 1,
        updated_at: now
      },
      {
        id: 2,
        title: 'Tentang PT Solnovis Main Global',
        slug: 'about',
        excerpt: 'Eksportir rempah terpercaya yang berakar pada kemitraan petani lokal dan berorientasi pada kepatuhan standar perdagangan internasional.',
        content: 'PT Solnovis Main Global adalah perusahaan perdagangan ekspor yang berfokus pada rempah-rempah berkualitas tinggi asal Indonesia dengan jaringan sentra petani binaan di Jawa Barat. Kami memadukan kearifan budidaya lokal dengan sistem manajemen mutu modern, perlakuan fumigasi standar internasional, serta kepatuhan penuh terhadap regulasi karantina ekspor.',
        status: 'published',
        is_home: 0,
        updated_at: now
      },
      {
        id: 3,
        title: 'Katalog Komoditas Ekspor',
        slug: 'product',
        excerpt: 'Koleksi rempah unggulan: Kapulaga Jawa, Biji Pala, Fuli, Jahe, Cengkeh Lalpari, dan Kemiri dengan spesifikasi teknis ekspor.',
        content: 'Setiap komoditas rempah yang kami pasok melalui inspeksi visual bertingkat, pembersihan kotoran (foreign matter < 1%), uji kadar air terjaga, dan pengujian kadar minyak atsiri serta aflatoksin sesuai regulasi negara tujuan ekspor di Asia, Timur Tengah, Eropa, dan Amerika.',
        status: 'published',
        is_home: 0,
        updated_at: now
      },
      {
        id: 4,
        title: 'Layanan Rantai Pasok Ekspor',
        slug: 'services',
        excerpt: 'Layanan terpadu mulai dari sourcing petani, sortasi & pengujian lab, fumigasi resmi, hingga pengapalan kontainer FCL & LCL.',
        content: 'Kami mengelola seluruh mata rantai ekspor secara transparan dan profesional: pengadaan langsung dari kelompok tani, pembersihan dan grading, pengemasan kustom (PP bag, kraft liner, vakum), pengurusan dokumen Certificate of Origin (COO), Phytosanitary Certificate, hingga pemuatan kontainer di Pelabuhan Tanjung Priok dan Patimban.',
        status: 'published',
        is_home: 0,
        updated_at: now
      },
      {
        id: 5,
        title: 'Hubungi Tim Perdagangan Ekspor',
        slug: 'contact',
        excerpt: 'Diskusikan spesifikasi kebutuhan, permintaan sampel produk, dan penawaran harga FOB/CIF bersama spesialis ekspor kami.',
        content: 'Tim perdagangan ekspor kami siap merespons kebutuhan komoditas Anda dalam waktu 24 jam kerja. Sampaikan komoditas yang dicari, estimasi volume, negara tujuan, serta ketentuan pengapalan yang diinginkan untuk memulai kemitraan dagang yang saling menguntungkan.',
        status: 'published',
        is_home: 0,
        updated_at: now
      }
    ],
    posts: [
      {
        id: 1,
        title: 'Kapulaga Jawa Barat: Kualitas dan Potensi Ekspor',
        slug: 'kapulaga-jawa-barat-potensi-ekspor',
        excerpt: 'Mengenal karakteristik kapulaga unggulan hasil panen petani lokal Jawa Barat untuk kebutuhan pasar internasional.',
        content: 'Kapulaga Jawa Barat dikenal memiliki aroma kuat dan kadar minyak atsiri yang baik. Kami menjalin kemitraan erat dengan petani untuk menjaga konsistensi mutu pascapanen.\n\nDengan sistem sortasi yang teliti, kami memastikan setiap pesanan ekspor memenuhi standar spesifikasi pasar tujuan.',
        status: 'published',
        author_id: 1,
        created_at: '2026-03-15 10:00:00',
        updated_at: '2026-03-15 10:00:00'
      },
      {
        id: 2,
        title: 'Pala dan Jahe: Menjaga Rantai Pasok Berkelanjutan',
        slug: 'pala-dan-jahe-rantai-pasok-berkelanjutan',
        excerpt: 'Komitmen kami dalam mengoordinasikan pasokan rempah pilihan langsung dari sentra perkebunan.',
        content: 'Kebutuhan rempah dunia terus meningkat seiring berkembangnya industri pangan dan kesehatan.\n\nSolnovis mendampingi proses dari peninjauan spesifikasi hingga pengiriman untuk memastikan kelancaran transaksi perdagangan.',
        status: 'published',
        author_id: 1,
        created_at: '2026-03-20 14:30:00',
        updated_at: '2026-03-20 14:30:00'
      }
    ],
    menus: [
      { id: 1, label: 'Beranda', url: 'index.php', sort_order: 1, is_cta: 0, is_visible: 1 },
      { id: 2, label: 'Tentang Kami', url: 'page.php?slug=about', sort_order: 2, is_cta: 0, is_visible: 1 },
      { id: 3, label: 'Komoditas', url: 'page.php?slug=product', sort_order: 3, is_cta: 0, is_visible: 1 },
      { id: 4, label: 'Layanan', url: 'page.php?slug=services', sort_order: 4, is_cta: 0, is_visible: 1 },
      { id: 5, label: 'Hubungi Kami', url: 'page.php?slug=contact', sort_order: 5, is_cta: 1, is_visible: 1 }
    ],
    hero_slides: [
      {
        id: 1,
        kicker: 'Sumber pilihan Indonesia',
        title: 'Rempah pilihan, siap menjangkau pasar dunia.',
        excerpt: 'Kami menghubungkan hasil bumi dari sumber terpercaya dengan kebutuhan pembeli global secara lebih jelas dan terukur.',
        image_url: 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=1200&q=85',
        label: 'INDONESIA',
        caption: 'Dari tanah yang kaya, untuk kebutuhan yang berarti.',
        cta_label: 'Jelajahi komoditas',
        cta_url: 'page.php?slug=product',
        sort_order: 1,
        is_visible: 1,
        updated_at: now
      },
      {
        id: 2,
        kicker: 'Kemitraan yang bertumbuh',
        title: 'Membangun nilai dari setiap hubungan.',
        excerpt: 'Komunikasi terbuka, pemahaman spesifikasi, dan koordinasi yang rapi menjadi awal dari kemitraan jangka panjang.',
        image_url: 'https://images.unsplash.com/photo-1500076656116-558758c991c1?auto=format&fit=crop&w=1200&q=85',
        label: 'JAWA BARAT',
        caption: 'Tumbuh bersama sumber lokal dan pasar global.',
        cta_label: 'Kenali Solnovis',
        cta_url: 'page.php?slug=about',
        sort_order: 2,
        is_visible: 1,
        updated_at: now
      },
      {
        id: 3,
        kicker: 'Untuk kebutuhan pasar',
        title: 'Komoditas yang dipahami dari asalnya.',
        excerpt: 'Kami membantu pembeli menemukan produk yang sesuai dengan kebutuhan volume, spesifikasi, dan tujuan pengiriman.',
        image_url: 'https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?auto=format&fit=crop&w=1200&q=85',
        label: 'SOURCE TO MARKET',
        caption: 'Setiap detail penting dalam perdagangan.',
        cta_label: 'Pelajari layanan',
        cta_url: 'page.php?slug=services',
        sort_order: 3,
        is_visible: 1,
        updated_at: now
      },
      {
        id: 4,
        kicker: 'Mari mulai percakapan',
        title: 'Ceritakan kebutuhan komoditas Anda.',
        excerpt: 'Bagikan produk, spesifikasi, volume, dan negara tujuan. Tim kami siap membuka pembicaraan untuk langkah berikutnya.',
        image_url: 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=1200&q=85',
        label: 'GLOBAL MARKET',
        caption: 'Satu percakapan dapat membuka peluang baru.',
        cta_label: 'Hubungi kami',
        cta_url: 'page.php?slug=contact',
        sort_order: 4,
        is_visible: 1,
        updated_at: now
      }
    ],
    settings: {
      site_name: 'PT Solnovis Main Global',
      tagline: 'Komoditas Indonesia untuk dunia.',
      contact_email: 'alvarezyest@gmail.com',
      contact_phone: '+62 812-3456-7890',
      location: 'Jawa Barat, Indonesia'
    },
    nextId: {
      users: 2,
      pages: 6,
      posts: 3,
      menus: 6,
      hero_slides: 5
    }
  };
}

let dbState: DatabaseState = getInitialState();

function loadFromDisk(): void {
  try {
    if (fs.existsSync(STORAGE_FILE)) {
      const raw = fs.readFileSync(STORAGE_FILE, 'utf-8');
      const parsed = JSON.parse(raw);
      if (parsed && typeof parsed === 'object') {
        dbState = { ...getInitialState(), ...parsed };
      }
    }
  } catch (err) {
    console.warn('Could not read persistent storage file, using in-memory state:', err);
  }
}

function saveToDisk(): void {
  try {
    if (!fs.existsSync(STORAGE_DIR)) {
      fs.mkdirSync(STORAGE_DIR, { recursive: true });
    }
    fs.writeFileSync(STORAGE_FILE, JSON.stringify(dbState, null, 2), 'utf-8');
  } catch (err) {
    console.warn('Could not persist storage file:', err);
  }
}

loadFromDisk();

export function slugify(value: string): string {
  return value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export function escapeHtml(unsafe: unknown): string {
  if (unsafe === null || unsafe === undefined) return '';
  return String(unsafe)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

export function formatDate(dateString: string): string {
  try {
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return dateString;
    return new Intl.DateTimeFormat('id-ID', {
      day: '2-digit',
      month: 'short',
      year: 'numeric'
    }).format(date);
  } catch {
    return dateString;
  }
}

export function renderPlainContent(content: string): string {
  if (!content) return '';
  const paragraphs = content.trim().split(/\r?\n\s*\r?\n/);
  return paragraphs
    .map((p) => p.trim())
    .filter((p) => p.length > 0)
    .map((p) => `<p>${escapeHtml(p).replace(/\r?\n/g, '<br>')}</p>`)
    .join('');
}

// Pages
export function getPages(): Page[] {
  return [...dbState.pages].sort((a, b) => b.is_home - a.is_home || a.title.localeCompare(b.title));
}

export function getPageBySlug(slug: string): Page | undefined {
  return dbState.pages.find((p) => p.slug === slug);
}

export function getHomePage(): Page | undefined {
  const home = dbState.pages.find((p) => p.is_home === 1 && p.status === 'published');
  if (home) return home;
  return dbState.pages.find((p) => p.status === 'published');
}

export function getPageById(id: number): Page | undefined {
  return dbState.pages.find((p) => p.id === id);
}

export function savePage(pageData: Partial<Page>, id = 0): Page {
  const now = new Date().toISOString().replace('T', ' ').substring(0, 19);
  const slug = slugify(pageData.slug || pageData.title || 'halaman');

  // Check unique slug
  const duplicate = dbState.pages.find((p) => p.slug === slug && p.id !== id);
  if (duplicate) {
    throw new Error('Slug sudah digunakan. Pilih slug yang berbeda.');
  }

  // If is_home = 1, unset others
  if (pageData.is_home === 1) {
    for (const p of dbState.pages) {
      p.is_home = 0;
    }
  }

  if (id > 0) {
    const index = dbState.pages.findIndex((p) => p.id === id);
    if (index === -1) throw new Error('Halaman tidak ditemukan');
    const existing = dbState.pages[index];
    const updated: Page = {
      ...existing,
      ...pageData,
      id,
      slug,
      updated_at: now
    };
    dbState.pages[index] = updated;
    saveToDisk();
    return updated;
  }

  const newId = dbState.nextId.pages++;
  const newPage: Page = {
    id: newId,
    title: pageData.title || '',
    slug,
    excerpt: pageData.excerpt || '',
    content: pageData.content || '',
    status: pageData.status === 'published' ? 'published' : 'draft',
    is_home: pageData.is_home ? 1 : 0,
    updated_at: now
  };
  dbState.pages.push(newPage);
  saveToDisk();
  return newPage;
}

export function deletePage(id: number): void {
  dbState.pages = dbState.pages.filter((p) => p.id !== id);
  saveToDisk();
}

// Posts
export function getPosts(publishedOnly = false): Post[] {
  let list = dbState.posts;
  if (publishedOnly) {
    list = list.filter((p) => p.status === 'published');
  }
  return [...list].sort((a, b) => (b.created_at || '').localeCompare(a.created_at || '') || b.id - a.id);
}

export function getPostBySlug(slug: string): Post | undefined {
  return dbState.posts.find((p) => p.slug === slug);
}

export function getPostById(id: number): Post | undefined {
  return dbState.posts.find((p) => p.id === id);
}

export function savePost(postData: Partial<Post>, id = 0, authorId: number | null = null): Post {
  const now = new Date().toISOString().replace('T', ' ').substring(0, 19);
  const slug = slugify(postData.slug || postData.title || 'post');

  const duplicate = dbState.posts.find((p) => p.slug === slug && p.id !== id);
  if (duplicate) {
    throw new Error('Slug sudah digunakan. Pilih slug yang berbeda.');
  }

  if (id > 0) {
    const index = dbState.posts.findIndex((p) => p.id === id);
    if (index === -1) throw new Error('Post tidak ditemukan');
    const existing = dbState.posts[index];
    const updated: Post = {
      ...existing,
      ...postData,
      id,
      slug,
      updated_at: now
    };
    dbState.posts[index] = updated;
    saveToDisk();
    return updated;
  }

  const newId = dbState.nextId.posts++;
  const newPost: Post = {
    id: newId,
    title: postData.title || '',
    slug,
    excerpt: postData.excerpt || '',
    content: postData.content || '',
    status: postData.status === 'published' ? 'published' : 'draft',
    author_id: authorId,
    created_at: now,
    updated_at: now
  };
  dbState.posts.push(newPost);
  saveToDisk();
  return newPost;
}

export function deletePost(id: number): void {
  dbState.posts = dbState.posts.filter((p) => p.id !== id);
  saveToDisk();
}

// Menus
export function getMenus(visibleOnly = false): Menu[] {
  let list = dbState.menus;
  if (visibleOnly) {
    list = list.filter((m) => m.is_visible === 1);
  }
  return [...list].sort((a, b) => a.sort_order - b.sort_order || a.id - b.id);
}

export function getMenuById(id: number): Menu | undefined {
  return dbState.menus.find((m) => m.id === id);
}

export function saveMenu(menuData: Partial<Menu>, id = 0): Menu {
  if (id > 0) {
    const index = dbState.menus.findIndex((m) => m.id === id);
    if (index === -1) throw new Error('Menu tidak ditemukan');
    const updated: Menu = {
      ...dbState.menus[index],
      ...menuData,
      id
    };
    dbState.menus[index] = updated;
    saveToDisk();
    return updated;
  }

  const newId = dbState.nextId.menus++;
  const newMenu: Menu = {
    id: newId,
    label: menuData.label || '',
    url: menuData.url || '',
    sort_order: Number(menuData.sort_order) || 0,
    is_cta: menuData.is_cta ? 1 : 0,
    is_visible: menuData.is_visible !== undefined ? (menuData.is_visible ? 1 : 0) : 1
  };
  dbState.menus.push(newMenu);
  saveToDisk();
  return newMenu;
}

export function deleteMenu(id: number): void {
  dbState.menus = dbState.menus.filter((m) => m.id !== id);
  saveToDisk();
}

// Hero Slides
export function getHeroSlides(visibleOnly = false): HeroSlide[] {
  let list = dbState.hero_slides;
  if (visibleOnly) {
    list = list.filter((s) => s.is_visible === 1);
  }
  return [...list].sort((a, b) => a.sort_order - b.sort_order || a.id - b.id);
}

export function getHeroSlideById(id: number): HeroSlide | undefined {
  return dbState.hero_slides.find((s) => s.id === id);
}

export function saveHeroSlide(slideData: Partial<HeroSlide>, id = 0): HeroSlide {
  const now = new Date().toISOString().replace('T', ' ').substring(0, 19);
  if (id > 0) {
    const index = dbState.hero_slides.findIndex((s) => s.id === id);
    if (index === -1) throw new Error('Slide tidak ditemukan');
    const updated: HeroSlide = {
      ...dbState.hero_slides[index],
      ...slideData,
      id,
      updated_at: now
    };
    dbState.hero_slides[index] = updated;
    saveToDisk();
    return updated;
  }

  const newId = dbState.nextId.hero_slides++;
  const newSlide: HeroSlide = {
    id: newId,
    kicker: slideData.kicker || '',
    title: slideData.title || '',
    excerpt: slideData.excerpt || '',
    image_url: slideData.image_url || '',
    label: slideData.label || '',
    caption: slideData.caption || '',
    cta_label: slideData.cta_label || 'Hubungi kami',
    cta_url: slideData.cta_url || 'page.php?slug=contact',
    sort_order: Number(slideData.sort_order) || 0,
    is_visible: slideData.is_visible !== undefined ? (slideData.is_visible ? 1 : 0) : 1,
    updated_at: now
  };
  dbState.hero_slides.push(newSlide);
  saveToDisk();
  return newSlide;
}

export function deleteHeroSlide(id: number): void {
  dbState.hero_slides = dbState.hero_slides.filter((s) => s.id !== id);
  saveToDisk();
}

// Settings
export function getSettings(): Settings {
  return { ...dbState.settings };
}

export function getSetting(key: string, defaultValue = ''): string {
  return dbState.settings[key] ?? defaultValue;
}

export function saveSettings(newSettings: Record<string, string>): void {
  for (const [key, value] of Object.entries(newSettings)) {
    if (typeof value === 'string') {
      dbState.settings[key] = value;
    }
  }
  saveToDisk();
}

// Users
export function getUsers(): User[] {
  return [...dbState.users].sort((a, b) => a.name.localeCompare(b.name));
}

export function getUserCount(): number {
  return dbState.users.length;
}

export function getUserById(id: number): User | undefined {
  return dbState.users.find((u) => u.id === id);
}

export function getUserByEmail(email: string): User | undefined {
  return dbState.users.find((u) => u.email.toLowerCase() === email.toLowerCase());
}

export function saveUser(userData: { name: string; email: string; password?: string; role: 'admin' | 'editor' }, id = 0): User {
  const now = new Date().toISOString().replace('T', ' ').substring(0, 19);
  const email = userData.email.toLowerCase().trim();

  const duplicate = dbState.users.find((u) => u.email.toLowerCase() === email && u.id !== id);
  if (duplicate) {
    throw new Error('Email sudah digunakan. Gunakan email lain.');
  }

  if (id > 0) {
    const index = dbState.users.findIndex((u) => u.id === id);
    if (index === -1) throw new Error('Pengguna tidak ditemukan');
    const existing = dbState.users[index];
    const password_hash = userData.password && userData.password.length >= 12
      ? bcrypt.hashSync(userData.password, 10)
      : existing.password_hash;

    const updated: User = {
      ...existing,
      name: userData.name.trim(),
      email,
      password_hash,
      role: userData.role
    };
    dbState.users[index] = updated;
    saveToDisk();
    return updated;
  }

  if (!userData.password || userData.password.length < 12) {
    throw new Error('Kata sandi minimal 12 karakter.');
  }

  const newId = dbState.nextId.users++;
  const newUser: User = {
    id: newId,
    name: userData.name.trim(),
    email,
    password_hash: bcrypt.hashSync(userData.password, 10),
    role: userData.role,
    created_at: now
  };
  dbState.users.push(newUser);
  saveToDisk();
  return newUser;
}

export function deleteUser(id: number): void {
  dbState.users = dbState.users.filter((u) => u.id !== id);
  saveToDisk();
}

export function getStats() {
  return {
    posts: dbState.posts.length,
    pages: dbState.pages.length,
    menus: dbState.menus.length,
    users: dbState.users.length
  };
}
