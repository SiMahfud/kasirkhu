# Rencana Pengembangan Aplikasi Kasir Toko Khumaira

Dokumen ini berisi rencana pengembangan untuk aplikasi kasir Toko Khumaira. Ditujukan untuk pengembang dan kontributor potensial jika proyek ini menjadi open source.

## 1. Stack Teknologi

*   **Framework Backend:** CodeIgniter 4 (versi terbaru yang stabil saat pengembangan dimulai).
*   **Bahasa Pemrograman:** PHP 7.4 atau lebih tinggi (sesuai kebutuhan CodeIgniter 4).
*   **Database:** MySQL 5.7 atau lebih tinggi / MariaDB 10.2 atau lebih tinggi.
*   **Framework Frontend:** Bootstrap 5 (versi terbaru yang stabil).
*   **JavaScript:** Vanilla JavaScript atau jQuery (jika diperlukan untuk plugin Bootstrap atau kemudahan DOM manipulation, namun usahakan minimal).
*   **Web Server:** Apache atau Nginx dengan konfigurasi yang sesuai untuk CodeIgniter 4.
*   **Version Control:** Git.
*   **Dependency Manager (PHP):** Composer.

## 2. Arsitektur Sistem

*   **Pola Desain:** Mengikuti pola Model-View-Controller (MVC) yang disediakan oleh CodeIgniter.
    *   **Models:** Bertanggung jawab untuk interaksi dengan database dan logika bisnis terkait data.
    *   **Views:** Bertanggung jawab untuk presentasi data kepada pengguna. Menggunakan Bootstrap 5 untuk styling dan layout.
    *   **Controllers:** Bertindak sebagai perantara antara Models dan Views, menangani request pengguna, memanggil logika bisnis dari Model, dan memilih View yang tepat untuk ditampilkan.
*   **Struktur Direktori:** Mengikuti struktur standar CodeIgniter 4 (`app/`, `public/`, `writable/`, `tests/`). Modul-modul fitur dapat diorganisir dalam subdirektori di dalam `app/Controllers/`, `app/Models/`, dan `app/Views/`.
*   **Routing:** Menggunakan sistem routing CodeIgniter 4 untuk memetakan URL ke controller dan method yang sesuai.
*   **Keamanan:**
    *   Menggunakan fitur keamanan bawaan CodeIgniter (CSRF protection, XSS filtering).
    *   Prepared statements untuk semua query database untuk mencegah SQL Injection.
    *   Validasi input di sisi server dan sisi klien.
    *   Password hashing menggunakan `password_hash()` PHP.

## 3. Tahapan Pengembangan (Sprint)

Pengembangan akan dibagi menjadi beberapa tahapan (sprint) untuk memastikan progres yang terukur dan memungkinkan adanya feedback berkelanjutan. **Catatan Penting:** Setelah menyelesaikan pengembangan atau modifikasi signifikan pada setiap komponen (Model, Controller, View, Migration, Seeder), **wajib** menjalankan atau membuat unit/feature test yang relevan menggunakan framework testing bawaan CodeIgniter (`php spark test`) untuk memastikan fungsionalitas dan mencegah regresi.

### Sprint 1: Inisialisasi Proyek dan Fitur Inti Produk/Layanan (Status: Belum dimulai)
1.  **Setup Proyek CodeIgniter 4:** (Status: Belum dimulai)
    *   Instalasi CodeIgniter 4 via Composer.
    *   Konfigurasi dasar (environment, database, base URL, app.php).
    *   Integrasi Bootstrap 5 (misalnya, melalui CDN atau download aset lokal).
    *   Setup Git repository.
2.  **Desain Database Awal:** (Status: Belum dimulai)
    *   Tabel `products` (id, name, code, category_id, price, unit, description, stock, created_at, updated_at).
    *   Tabel `categories` (id, name, description, created_at, updated_at).
    *   Tabel `users` (id, name, username, password, role, created_at, updated_at) - `role` bisa enum ('admin', 'cashier').
    *   Gunakan Migrations CodeIgniter untuk membuat skema database.
3.  **Modul Manajemen Kategori (CRUD):** (Status: Belum dimulai)
    *   Controller, Model, Views untuk Tambah, Lihat, Edit, Hapus Kategori.
4.  **Modul Manajemen Produk/Layanan (CRUD):** (Status: Belum dimulai)
    *   Controller, Model, Views untuk Tambah, Lihat (dengan pagination, pencarian dasar), Edit, Hapus Produk/Layanan.
    *   Relasi ke tabel kategori.
5.  **Autentikasi Dasar:** (Status: Belum dimulai)
    *   Halaman Login.
    *   Controller untuk proses login dan logout.
    *   Penggunaan Session CodeIgniter untuk manajemen status login.
    *   Filter untuk melindungi route yang memerlukan autentikasi.

### Sprint 2: Fitur Inti Transaksi (Status: Belum dimulai)
1.  **Desain Database Transaksi:** (Status: Belum dimulai)
    *   Tabel `transactions` (id, transaction_code, user_id, customer_name (opsional), total_amount, discount, final_amount, payment_method, created_at).
    *   Tabel `transaction_details` (id, transaction_id, product_id, quantity, price_per_unit, subtotal).
    *   Gunakan Migrations.
2.  **Modul Transaksi Penjualan:** (Status: Belum dimulai)
    *   Antarmuka (View dengan Bootstrap) untuk input transaksi baru:
        *   Pemilihan produk/layanan (misalnya, dropdown dengan pencarian atau autocomplete).
        *   Input jumlah/kuantitas.
        *   Perhitungan subtotal dan total otomatis (JavaScript dan backend).
        *   Input diskon.
    *   Controller untuk memproses dan menyimpan data transaksi ke tabel `transactions` dan `transaction_details`.
    *   Pengurangan stok produk ATK secara otomatis (jika produk memiliki flag 'is_stock_managed').
3.  **Riwayat Transaksi Sederhana:** (Status: Belum dimulai)
    *   Menampilkan daftar transaksi (dengan pagination).
    *   Menampilkan detail per transaksi (termasuk item-item yang dibeli).

### Sprint 3: Penyempurnaan Transaksi dan Laporan Awal (Status: Belum dimulai)
1.  **Pencetakan Struk/Nota:** (Status: Belum dimulai)
    *   Desain template struk (HTML/CSS untuk Bootstrap) yang bisa dicetak.
    *   Fungsi untuk menghasilkan halaman struk yang siap cetak (window.print() atau konversi ke PDF sederhana jika memungkinkan).
    *   Menampilkan informasi toko di struk.
2.  **Manajemen Stok ATK (Lanjutan):** (Status: Belum dimulai)
    *   View untuk melihat sisa stok produk.
    *   Fitur sederhana untuk penyesuaian/penambahan stok manual (oleh Admin).
3.  **Laporan Penjualan Dasar:** (Status: Belum dimulai)
    *   Laporan penjualan harian (total omset, jumlah transaksi).
    *   Laporan produk/layanan terlaris (berdasarkan kuantitas terjual dalam periode tertentu).
    *   Filter laporan berdasarkan rentang tanggal.
4.  **Perhitungan Spesifik Toko Khumaira (Implementasi Awal):** (Status: Belum dimulai)
    *   Untuk jasa fotokopi/print: form input jumlah halaman, jenis kertas, warna/hitam-putih. Harga dihitung berdasarkan parameter ini. Produk jasa ini bisa memiliki harga dasar 0, dan harga final dihitung di transaksi.
    *   Untuk jasa desain/edit/banner: input harga manual saat transaksi atau produk dengan harga fleksibel.

### Sprint 4: Pengaturan dan Pengguna (Status: Belum dimulai)
1.  **Modul Pengaturan Toko:** (Status: Belum dimulai)
    *   Form untuk Admin mengubah informasi toko (nama, alamat, kontak, pesan di struk). Simpan di tabel `settings` atau file konfigurasi.
2.  **Manajemen Pengguna (CRUD):** (Status: Belum dimulai)
    *   Antarmuka untuk Admin menambah, melihat, mengedit, menghapus pengguna (Kasir/Admin lain).
    *   Pengaturan role pengguna.
3.  **Pengaturan Printer (Panduan):** (Status: Belum dimulai)
    *   Dokumentasi singkat cara setup printer default di browser untuk pencetakan struk.

### Sprint 5: Fitur Tambahan dan Finalisasi (Status: Belum dimulai)
1.  **Manajemen Pelanggan (Opsional):** (Status: Belum dimulai)
    *   Tabel `customers` (id, name, phone, email, address).
    *   CRUD Pelanggan.
    *   Menghubungkan transaksi dengan pelanggan (opsional saat input transaksi).
2.  **Laporan Lanjutan:** (Status: Belum dimulai)
    *   Laporan penjualan mingguan dan bulanan.
    *   Laporan laba rugi sederhana (jika harga pokok produk diinput).
3.  **Penyempurnaan UI/UX:** (Status: Belum dimulai)
    *   Review dan perbaikan alur pengguna.
    *   Pastikan responsivitas dengan Bootstrap.
    *   Validasi input yang lebih komprehensif (sisi klien dan server).
    *   Notifikasi/feedback pengguna yang lebih baik (misalnya menggunakan Toast Bootstrap).
4.  **Testing dan Bug Fixing:** (Status: Belum dimulai)
    *   Pengujian manual menyeluruh semua fitur.
    *   (Jika ada) Penulisan Unit Test dasar untuk logika bisnis kritis di Model.
    *   Perbaikan bug yang ditemukan.
5.  **Dokumentasi Pengguna (Sederhana):** (Status: Belum dimulai)
    *   Panduan singkat cara penggunaan fitur-fitur utama aplikasi untuk Admin dan Kasir.

## 4. Panduan Kontribusi (Jika Open Source)

*   **Setup Environment Lokal:**
    1.  Clone repository.
    2.  Install Composer dependencies (`composer install`).
    3.  Salin `env` menjadi `.env` dan sesuaikan konfigurasi (database, app URL).
    4.  Jalankan migrasi (`php spark migrate`).
    5.  Jalankan seeder jika ada (`php spark db:seed <NamaSeeder>`).
    6.  Jalankan development server (`php spark serve`).
    7.  **Testing:** Setelah membuat atau memodifikasi kode (Model, Controller, Library, dll.), jalankan test suite (`php spark test`). Jika Anda menambahkan fungsionalitas baru atau memperbaiki bug, diharapkan untuk menulis test case baru atau memperbarui yang sudah ada untuk mencakup perubahan tersebut. Familiarisasi diri Anda dengan panduan testing CodeIgniter 4.
*   **Coding Style:**
    *   Mengikuti standar PSR-12 (Extended Coding Style). Gunakan tools seperti PHP CS Fixer jika memungkinkan.
    *   Komentari kode yang kompleks atau tidak jelas.
    *   Gunakan nama variabel dan fungsi yang deskriptif dalam bahasa Inggris.
*   **Alur Kerja Git:**
    1.  Buat _fork_ dari repository utama.
    2.  Buat _branch_ baru dari `main` atau `develop` untuk setiap fitur atau perbaikan bug (misalnya, `feature/nama-fitur` atau `fix/bug-deskripsi`).
    3.  Commit perubahan secara berkala dengan pesan commit yang jelas dan deskriptif (misalnya, "feat: Add user login functionality").
    4.  Pastikan semua test lolos (`php spark test`) sebelum membuat Pull Request.
    5.  Buat Pull Request ke branch `main` atau `develop` di repository utama. Jelaskan perubahan yang dibuat dan pastikan semua automated checks (jika ada) lolos.
*   **Issue Tracker:** Gunakan GitHub Issues untuk melaporkan bug atau mengusulkan fitur baru.

## 5. Lisensi

Proyek ini akan dirilis di bawah lisensi **[NAMA LISENSI, misalnya MIT License atau GNU GPLv3]**. (Akan ditentukan kemudian jika proyek menjadi open source).

## 6. Prioritas Pengembangan
Fitur-fitur akan diprioritaskan berdasarkan kebutuhan inti operasional toko:
1.  Manajemen Produk/Layanan & Kategori.
2.  Autentikasi & Manajemen Pengguna Dasar.
3.  Transaksi Penjualan (termasuk perhitungan spesifik).
4.  Cetak Struk.
5.  Laporan Penjualan Dasar.

## Catatan Tambahan
*   Desain UI/UX akan mengutamakan fungsionalitas dan kemudahan penggunaan dengan komponen Bootstrap 5.
*   Rencana ini bersifat fleksibel dan dapat disesuaikan seiring berjalannya proyek dan berdasarkan feedback.

Rencana ini bersifat fleksibel dan dapat disesuaikan seiring berjalannya proyek dan berdasarkan feedback.
