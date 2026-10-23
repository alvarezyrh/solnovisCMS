# Solnovis CMS (Node.js Edition)

Situs web perusahaan dan Content Management System (CMS) untuk **PT Solnovis Main Global** (komoditas rempah dan hasil pertanian Indonesia untuk pasar global). Dimigrasikan ke Node.js runtime dengan Express dan EJS.

## Menjalankan Aplikasi

```sh
npm install
npm run dev
```

Server berjalan pada port `3000` (host `0.0.0.0`). Buka `http://localhost:3000/`.

## Akun Administrator

- **Email**: `admin@solnovis.com`
- **Kata Sandi**: `admin12345678`
- **URL Login**: `/admin/login`

## Fitur Aplikasi

- **Beranda Publik (`/`):** Hero slide carousel, profil perusahaan, komoditas utama, alur kerja ekspor, dan call-to-action kontak.
- **Halaman Dinamis (`/page/:slug`):** Tentang Kami, Komoditas, Layanan, dan Kontak dengan formulir pertanyaan ekspor.
- **Jurnal & Post (`/posts`, `/post/:slug`):** Artikel wawasan dan berita komoditas.
- **Dashboard Admin (`/admin`):**
  - Widget statistik jumlah konten (Post, Halaman, Menu, Profiles).
  - Grafik tren kunjungan web.
  - Widget jam digital real-time (Asia/Jakarta).
  - Kalender bulan berjalan.
  - Tabel ringkasan post terbaru.
- **Manajemen Konten (`/admin/manage`):** CRUD untuk Post, Halaman, Menu Navigasi, Slider Hero, dan Pengguna Admin/Editor.
- **Pengaturan Website (`/admin/settings`):** Konfigurasi nama perusahaan, tagline, email publik, telepon, dan alamat lokasi.
