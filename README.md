# Solnovis CMS

Situs ini menggunakan PHP native dan SQLite. Semua route CMS tersedia di PHP, sedangkan file HTML lama diarahkan ke halaman dinamis melalui aturan Apache.

## Persyaratan

- PHP 8.1 atau lebih baru
- Ekstensi `pdo_sqlite`
- Apache dengan `mod_rewrite` dan izin `.htaccess`, atau web server yang mengarahkan URL lama ke route PHP yang sesuai

## Menjalankan secara lokal

Dari direktori proyek:

```sh
php -S 127.0.0.1:8000
```

Buka `http://127.0.0.1:8000/`. Pada kunjungan pertama, CMS menyiapkan database dan halaman awal. Buat akun administrator ketika diarahkan ke halaman setup, lalu masuk ke `/admin/login.php`.

Kata sandi administrator baru minimal 12 karakter. Tidak ada kredensial default yang disimpan di source code.

## Fitur dashboard

- **Post:** buat, edit, terbitkan, simpan draft, dan hapus artikel.
- **Halaman:** kelola halaman website, slug, ringkasan, isi, status, dan pilihan halaman beranda.
- **Menu:** ubah label, URL internal/HTTPS, urutan, visibilitas, dan gaya CTA.
- **Settings:** ubah nama website, tagline, email, telepon, dan lokasi.
- **Profiles:** kelola akun administrator/editor; kata sandi disimpan menggunakan `password_hash`.

Isi post dan halaman menerima teks biasa. Pisahkan paragraf dengan satu baris kosong; HTML yang dimasukkan tidak dieksekusi.

## Penyimpanan dan deployment

Database secara default dibuat di `storage/cms.sqlite`. Folder `storage` dilindungi oleh `.htaccess`; pastikan konfigurasi web server deployment juga menolak akses HTTP ke file database dan memberi PHP izin tulis ke folder tersebut. Untuk menyimpan database di luar document root, set environment variable `SOLNOVIS_DB_PATH` ke path absolut database SQLite. Untuk Nginx, buat aturan deny untuk `/storage/`.

Gunakan HTTPS di production. Salin/backup `storage/cms.sqlite` bersama konfigurasi server secara aman; jangan letakkan backup database di direktori publik. Jika PHP tidak memiliki `pdo_sqlite`, aktifkan ekstensi tersebut sebelum mengakses route CMS.
