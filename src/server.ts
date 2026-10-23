import express, { Request, Response, NextFunction } from 'express';
import session from 'express-session';
import path from 'node:path';
import crypto from 'node:crypto';
import bcrypt from 'bcryptjs';
import {
  getPages,
  getPageBySlug,
  getHomePage,
  getPageById,
  savePage,
  deletePage,
  getPosts,
  getPostBySlug,
  getPostById,
  savePost,
  deletePost,
  getMenus,
  getMenuById,
  saveMenu,
  deleteMenu,
  getHeroSlides,
  getHeroSlideById,
  saveHeroSlide,
  deleteHeroSlide,
  getSettings,
  getSetting,
  saveSettings,
  getUsers,
  getUserCount,
  getUserById,
  getUserByEmail,
  saveUser,
  deleteUser,
  getStats,
  formatDate,
  renderPlainContent,
  User,
  Page,
  Post,
  Menu,
  HeroSlide
} from './db.js';

// Extend express session
declare module 'express-session' {
  interface SessionData {
    user?: {
      id: number;
      name: string;
      email: string;
      role: 'admin' | 'editor';
    };
    csrfToken?: string;
    flash?: {
      message: string;
      type: 'success' | 'error';
    };
    login_attempts?: number;
    login_attempt_at?: number;
  }
}

const app = express();
const PORT = Number(process.env.PORT) || 3000;
const HOST = '0.0.0.0';

// View engine setup
app.set('views', path.resolve(process.cwd(), 'views'));
app.set('view engine', 'ejs');

// Body parsers
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Session setup
app.use(
  session({
    secret: process.env.SESSION_SECRET || 'solnovis-cms-secret-key-2026',
    resave: false,
    saveUninitialized: false,
    cookie: {
      httpOnly: true,
      secure: false, // development & http
      sameSite: 'lax',
      maxAge: 24 * 60 * 60 * 1000
    }
  }) as any
);

// Static assets
app.use(express.static(path.resolve(process.cwd(), 'public')));
app.use(express.static(process.cwd()));

// Global middleware for CSRF, flash, and helpers
app.use((req: Request, res: Response, next: NextFunction) => {
  if (!req.session.csrfToken) {
    req.session.csrfToken = crypto.randomBytes(32).toString('hex');
  }

  res.locals.csrfToken = req.session.csrfToken;
  res.locals.currentUser = req.session.user || null;
  res.locals.setting = getSetting;
  res.locals.formatDate = formatDate;
  res.locals.renderPlainContent = renderPlainContent;

  const flash = req.session.flash || null;
  res.locals.flash = flash;
  delete req.session.flash;

  next();
});

// CSRF verification helper
function verifyCsrf(req: Request, res: Response, next: NextFunction) {
  const token = req.body.csrf_token || req.headers['x-csrf-token'];
  if (!token || token !== req.session.csrfToken) {
    res.status(400).send('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    return;
  }
  next();
}

// Authentication guards
function requireAuth(req: Request, res: Response, next: NextFunction) {
  if (!req.session.user) {
    res.redirect('/admin/login');
    return;
  }
  next();
}

function requireAdminRole(req: Request, res: Response, next: NextFunction) {
  if (!req.session.user) {
    res.redirect('/admin/login');
    return;
  }
  if (req.session.user.role !== 'admin') {
    res.status(403).send('Anda tidak memiliki izin untuk membuka halaman ini.');
    return;
  }
  next();
}

// ==================== PUBLIC ROUTES ====================

// Home Page: / and /index.php
const handleHome = (req: Request, res: Response) => {
  const home = getHomePage();
  const heroSlides = getHeroSlides(true);
  const recentPosts = getPosts(true).slice(0, 3);
  const menuItems = getMenus(true);

  const pageTitle = home ? home.title : getSetting('site_name', 'PT Solnovis Main Global');
  const description = home ? home.excerpt : getSetting('tagline', 'Komoditas Indonesia untuk dunia.');

  res.render('index', {
    home,
    heroSlides,
    recentPosts,
    menuItems,
    pageTitle,
    description
  });
};

app.get('/', handleHome);
app.get('/index.php', handleHome);

// Dynamic Page: /page/:slug and /page.php?slug=...
const handlePage = (req: Request, res: Response) => {
  const slug = String(req.params.slug || req.query.slug || '').trim();
  const menuItems = getMenus(true);

  if (!slug) {
    res.redirect('/');
    return;
  }

  const page = getPageBySlug(slug);

  if (!page || page.status !== 'published') {
    res.status(404).render('page', {
      page: {
        id: 0,
        title: 'Halaman tidak ditemukan',
        slug: '404',
        excerpt: 'Halaman yang Anda cari tidak tersedia atau belum diterbitkan.',
        content: 'Tautan mungkin telah berubah atau halaman belum diterbitkan.',
        status: 'published',
        is_home: 0,
        updated_at: ''
      },
      posts: [],
      menuItems
    });
    return;
  }

  if (page.is_home === 1) {
    res.redirect('/');
    return;
  }

  const posts = slug === 'product' ? getPosts(true) : [];

  res.render('page', {
    page,
    posts,
    menuItems
  });
};

app.get('/page/:slug', handlePage);
app.get('/page.php', handlePage);

// Posts Archive: /posts and /posts.php
const handlePosts = (req: Request, res: Response) => {
  const posts = getPosts(true);
  const menuItems = getMenus(true);
  res.render('posts', {
    posts,
    menuItems
  });
};

app.get('/posts', handlePosts);
app.get('/posts.php', handlePosts);

// Single Post: /post/:slug and /post.php?slug=...
const handleSinglePost = (req: Request, res: Response) => {
  const slug = String(req.params.slug || req.query.slug || '').trim();
  const menuItems = getMenus(true);

  const post = getPostBySlug(slug);

  if (!post || post.status !== 'published') {
    res.status(404).render('page', {
      page: {
        id: 0,
        title: 'Post tidak ditemukan',
        slug: '404',
        excerpt: 'Informasi ini tidak tersedia.',
        content: 'Kembali ke halaman jurnal untuk melihat post yang tersedia.',
        status: 'published',
        is_home: 0,
        updated_at: ''
      },
      posts: [],
      menuItems
    });
    return;
  }

  res.render('post', {
    post,
    menuItems
  });
};

app.get('/post/:slug', handleSinglePost);
app.get('/post.php', handleSinglePost);

// ==================== INSTALL / SETUP ROUTES ====================

const handleInstallGet = (req: Request, res: Response) => {
  if (getUserCount() > 0) {
    res.redirect('/admin/login');
    return;
  }
  res.render('install', { error: '', values: {} });
};

const handleInstallPost = [
  verifyCsrf,
  (req: Request, res: Response) => {
    if (getUserCount() > 0) {
      res.redirect('/admin/login');
      return;
    }

    const name = String(req.body.name || '').trim();
    const email = String(req.body.email || '').trim().toLowerCase();
    const password = String(req.body.password || '');

    if (!name || !email.includes('@')) {
      res.render('install', {
        error: 'Nama dan alamat email yang valid wajib diisi.',
        values: { name, email }
      });
      return;
    }

    if (password.length < 12) {
      res.render('install', {
        error: 'Kata sandi administrator harus terdiri dari minimal 12 karakter.',
        values: { name, email }
      });
      return;
    }

    try {
      const newUser = saveUser({ name, email, password, role: 'admin' });
      req.session.user = {
        id: newUser.id,
        name: newUser.name,
        email: newUser.email,
        role: newUser.role
      };
      res.redirect('/admin');
    } catch (err: any) {
      res.render('install', {
        error: err.message || 'Gagal membuat akun.',
        values: { name, email }
      });
    }
  }
];

app.get('/install', handleInstallGet);
app.get('/install.php', handleInstallGet);
app.post('/install', handleInstallPost);
app.post('/install.php', handleInstallPost);

// ==================== ADMIN AUTH ====================

const handleLoginGet = (req: Request, res: Response) => {
  if (req.session.user) {
    res.redirect('/admin');
    return;
  }
  if (getUserCount() === 0) {
    res.redirect('/install');
    return;
  }
  res.render('admin/login', { error: '', values: {} });
};

const handleLoginPost = [
  verifyCsrf,
  (req: Request, res: Response) => {
    const email = String(req.body.email || '').trim().toLowerCase();
    const password = String(req.body.password || '');

    const user = getUserByEmail(email);

    if (user && bcrypt.compareSync(password, user.password_hash)) {
      req.session.user = {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role
      };
      res.redirect('/admin');
    } else {
      res.render('admin/login', {
        error: 'Email atau kata sandi tidak cocok.',
        values: { email }
      });
    }
  }
];

app.get('/admin/login', handleLoginGet);
app.get('/admin/login.php', handleLoginGet);
app.post('/admin/login', handleLoginPost);
app.post('/admin/login.php', handleLoginPost);

const handleLogout = [
  verifyCsrf,
  (req: Request, res: Response) => {
    req.session.destroy(() => {
      res.redirect('/admin/login');
    });
  }
];

app.post('/admin/logout', handleLogout);
app.post('/admin/logout.php', handleLogout);

// ==================== ADMIN DASHBOARD ====================

const handleAdminIndex = (req: Request, res: Response) => {
  const counts = getStats();
  const recentPosts = getPosts(false).slice(0, 5);

  res.render('admin/index', {
    counts,
    recentPosts
  });
};

app.get('/admin', requireAuth, handleAdminIndex);
app.get('/admin/index.php', requireAuth, handleAdminIndex);

// ==================== ADMIN MANAGE ====================

const configMap: Record<string, { title: string; active: string }> = {
  posts: { title: 'Post', active: 'posts' },
  pages: { title: 'Halaman', active: 'pages' },
  menus: { title: 'Menu navigasi', active: 'menus' },
  hero_slides: { title: 'Slider Hero', active: 'sliders' },
  profiles: { title: 'Profiles admin', active: 'profiles' }
};

const handleManageGet = (req: Request, res: Response) => {
  const type = String(req.query.type || 'posts');
  const itemConfig = configMap[type];

  if (!itemConfig) {
    res.status(404).send('Jenis konten tidak ditemukan.');
    return;
  }

  if (type === 'profiles' && req.session.user?.role !== 'admin') {
    res.status(403).send('Hanya administrator yang dapat mengelola akun.');
    return;
  }

  let rows: any[] = [];
  if (type === 'posts') {
    rows = getPosts(false).map((p) => ({
      id: p.id,
      title: p.title,
      slug: p.slug,
      status: p.status,
      updated_at: p.updated_at
    }));
  } else if (type === 'pages') {
    rows = getPages().map((p) => ({
      id: p.id,
      title: p.title,
      slug: p.slug,
      status: p.status,
      is_home: p.is_home,
      updated_at: p.updated_at
    }));
  } else if (type === 'menus') {
    rows = getMenus(false).map((m) => ({
      id: m.id,
      title: m.label,
      slug: m.url,
      status: m.is_visible,
      sort_order: m.sort_order,
      is_cta: m.is_cta
    }));
  } else if (type === 'hero_slides') {
    rows = getHeroSlides(false).map((s) => ({
      id: s.id,
      title: s.title,
      slug: s.image_url,
      status: s.is_visible,
      sort_order: s.sort_order,
      kicker: s.kicker
    }));
  } else if (type === 'profiles') {
    rows = getUsers().map((u) => ({
      id: u.id,
      title: u.name,
      slug: u.email,
      status: u.role,
      updated_at: u.created_at
    }));
  }

  res.render('admin/manage', {
    type,
    itemConfig,
    rows,
    error: ''
  });
};

const handleManagePost = [
  verifyCsrf,
  (req: Request, res: Response) => {
    const type = String(req.query.type || '');
    const action = String(req.body.action || '');
    const id = Number(req.body.id) || 0;

    if (action === 'delete') {
      if (type === 'profiles') {
        if (req.session.user?.role !== 'admin') {
          res.status(403).send('Hanya administrator yang dapat mengelola akun.');
          return;
        }
        if (id === req.session.user?.id || getUserCount() <= 1) {
          req.session.flash = {
            message: 'Akun yang sedang digunakan atau satu-satunya admin tidak dapat dihapus.',
            type: 'error'
          };
          res.redirect(`/admin/manage?type=${encodeURIComponent(type)}`);
          return;
        }
        deleteUser(id);
      } else if (type === 'posts') {
        deletePost(id);
      } else if (type === 'pages') {
        deletePage(id);
      } else if (type === 'menus') {
        deleteMenu(id);
      } else if (type === 'hero_slides') {
        deleteHeroSlide(id);
      }

      req.session.flash = { message: 'Item berhasil dihapus.', type: 'success' };
    }

    res.redirect(`/admin/manage?type=${encodeURIComponent(type)}`);
  }
];

app.get('/admin/manage', requireAuth, handleManageGet);
app.get('/admin/manage.php', requireAuth, handleManageGet);
app.post('/admin/manage', requireAuth, handleManagePost);
app.post('/admin/manage.php', requireAuth, handleManagePost);

// ==================== ADMIN EDIT / CREATE ====================

const editConfigs: Record<string, { title: string; active: string }> = {
  posts: { title: 'Post', active: 'posts' },
  pages: { title: 'Halaman', active: 'pages' },
  menus: { title: 'Menu', active: 'menus' },
  hero_slides: { title: 'Slider Hero', active: 'sliders' },
  profiles: { title: 'Profile admin', active: 'profiles' }
};

const handleEditGet = (req: Request, res: Response) => {
  const type = String(req.query.type || '');
  const id = Number(req.query.id) || 0;
  const config = editConfigs[type];

  if (!config) {
    res.status(404).send('Jenis konten tidak ditemukan.');
    return;
  }

  if (type === 'profiles' && req.session.user?.role !== 'admin') {
    res.status(403).send('Hanya administrator yang dapat mengelola akun.');
    return;
  }

  let fields: any = {
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    status: 'draft',
    is_home: 0,
    label: '',
    url: '',
    sort_order: 0,
    is_cta: 0,
    is_visible: 1,
    name: '',
    email: '',
    role: 'editor',
    kicker: '',
    image_url: '',
    caption: '',
    cta_label: 'Hubungi kami',
    cta_url: 'page.php?slug=contact'
  };

  if (id > 0) {
    let existing: any = null;
    if (type === 'posts') existing = getPostById(id);
    else if (type === 'pages') existing = getPageById(id);
    else if (type === 'menus') existing = getMenuById(id);
    else if (type === 'hero_slides') existing = getHeroSlideById(id);
    else if (type === 'profiles') existing = getUserById(id);

    if (!existing) {
      res.status(404).send('Data tidak ditemukan.');
      return;
    }
    fields = { ...fields, ...existing };
  }

  const title = (id > 0 ? 'Edit ' : 'Tambah ') + config.title;

  res.render('admin/edit', {
    type,
    id,
    config,
    title,
    fields,
    error: ''
  });
};

const handleEditPost = [
  verifyCsrf,
  (req: Request, res: Response) => {
    const type = String(req.query.type || '');
    const id = Number(req.query.id) || 0;
    const config = editConfigs[type];

    if (!config) {
      res.status(404).send('Jenis konten tidak ditemukan.');
      return;
    }

    if (type === 'profiles' && req.session.user?.role !== 'admin') {
      res.status(403).send('Hanya administrator yang dapat mengelola akun.');
      return;
    }

    const body = req.body;
    let error = '';

    try {
      if (type === 'posts' || type === 'pages') {
        const title = String(body.title || '').trim();
        const slug = String(body.slug || '').trim();
        const excerpt = String(body.excerpt || '').trim();
        const content = String(body.content || '').trim();
        const status = body.status === 'published' ? 'published' : 'draft';

        if (!title) {
          throw new Error('Judul wajib diisi.');
        }

        if (type === 'pages') {
          const is_home = body.is_home ? 1 : 0;
          savePage({ title, slug, excerpt, content, status, is_home }, id);
        } else {
          savePost({ title, slug, excerpt, content, status }, id, req.session.user?.id || 1);
        }
      } else if (type === 'hero_slides') {
        const kicker = String(body.kicker || '').trim();
        const title = String(body.title || '').trim();
        const excerpt = String(body.excerpt || '').trim();
        const image_url = String(body.image_url || '').trim();
        const label = String(body.label || '').trim();
        const caption = String(body.caption || '').trim();
        const cta_label = String(body.cta_label || 'Hubungi kami').trim();
        const cta_url = String(body.cta_url || 'page.php?slug=contact').trim();
        const sort_order = Number(body.sort_order) || 0;
        const is_visible = body.is_visible ? 1 : 0;

        if (!title || !image_url) {
          throw new Error('Judul dan URL gambar wajib diisi.');
        }

        saveHeroSlide(
          {
            kicker,
            title,
            excerpt,
            image_url,
            label,
            caption,
            cta_label,
            cta_url,
            sort_order,
            is_visible
          },
          id
        );
      } else if (type === 'menus') {
        const label = String(body.label || '').trim();
        const url = String(body.url || '').trim();
        const sort_order = Number(body.sort_order) || 0;
        const is_cta = body.is_cta ? 1 : 0;
        const is_visible = body.is_visible ? 1 : 0;

        if (!label || !url) {
          throw new Error('Label dan URL menu wajib diisi.');
        }

        saveMenu({ label, url, sort_order, is_cta, is_visible }, id);
      } else if (type === 'profiles') {
        const name = String(body.name || '').trim();
        const email = String(body.email || '').trim().toLowerCase();
        const role = body.role === 'admin' ? 'admin' : 'editor';
        const password = String(body.password || '');

        if (!name || !email.includes('@')) {
          throw new Error('Nama dan email valid wajib diisi.');
        }

        if (id > 0 && id === req.session.user?.id && role !== 'admin') {
          throw new Error('Peran akun yang sedang digunakan tidak dapat diturunkan.');
        }

        saveUser({ name, email, role, password }, id);

        if (id === req.session.user?.id) {
          req.session.user.name = name;
          req.session.user.email = email;
        }
      }

      req.session.flash = { message: `${config.title} berhasil disimpan.`, type: 'success' };
      res.redirect(`/admin/manage?type=${encodeURIComponent(type)}`);
    } catch (err: any) {
      error = err.message || 'Terjadi kesalahan.';
      const title = (id > 0 ? 'Edit ' : 'Tambah ') + config.title;
      res.render('admin/edit', {
        type,
        id,
        config,
        title,
        fields: { ...body, id },
        error
      });
    }
  }
];

app.get('/admin/edit', requireAuth, handleEditGet);
app.get('/admin/edit.php', requireAuth, handleEditGet);
app.post('/admin/edit', requireAuth, handleEditPost);
app.post('/admin/edit.php', requireAuth, handleEditPost);

// ==================== ADMIN SETTINGS ====================

const handleSettingsGet = (req: Request, res: Response) => {
  const settings = getSettings();
  const keys = ['site_name', 'tagline', 'contact_email', 'contact_phone', 'location'];
  const labels: Record<string, string> = {
    site_name: 'Nama website/perusahaan',
    tagline: 'Tagline',
    contact_email: 'Email kontak publik',
    contact_phone: 'Telepon kontak publik',
    location: 'Lokasi'
  };

  res.render('admin/settings', {
    keys,
    labels,
    values: settings,
    error: ''
  });
};

const handleSettingsPost = [
  verifyCsrf,
  (req: Request, res: Response) => {
    const keys = ['site_name', 'tagline', 'contact_email', 'contact_phone', 'location'];
    const newValues: Record<string, string> = {};

    for (const key of keys) {
      newValues[key] = String(req.body[key] || '').trim();
    }

    saveSettings(newValues);

    req.session.flash = { message: 'Pengaturan website berhasil disimpan.', type: 'success' };
    res.redirect('/admin/settings');
  }
];

app.get('/admin/settings', requireAdminRole, handleSettingsGet);
app.get('/admin/settings.php', requireAdminRole, handleSettingsGet);
app.post('/admin/settings', requireAdminRole, handleSettingsPost);
app.post('/admin/settings.php', requireAdminRole, handleSettingsPost);

// Start server on 0.0.0.0:3000
app.listen(PORT, HOST, () => {
  console.log(`Solnovis CMS dev server running on http://${HOST}:${PORT}`);
});
