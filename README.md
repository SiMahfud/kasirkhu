# Aplikasi Kasir Toko Khumaira

Aplikasi kasir sederhana untuk Toko Khumaira yang melayani berbagai jasa seperti fotokopi, print, desain dan cetak banner, edit dan print dokumen (Excel, Word), serta penjualan alat tulis kantor (ATK).

## Fitur Aplikasi

Berikut adalah daftar fitur yang direncanakan untuk aplikasi ini.

**Legenda Status:**
*   `[ ]` : Belum dimulai
*   `[/]` : Sedang dikerjakan
*   `[x]` : Selesai

### Manajemen Produk/Layanan
- [x] Tambah Produk/Layanan Baru
- [x] Edit Produk/Layanan
- [x] Hapus Produk/Layanan
- [x] Lihat Daftar Produk/Layanan
- [x] Kategori Produk/Layanan (e.g., Jasa Fotokopi, Jasa Print, ATK)
- [x] Pencarian Produk/Layanan
- [x] Pengelolaan Stok untuk ATK (Lihat sisa stok, penyesuaian manual)

### Transaksi Penjualan
- [x] Input Transaksi Baru
    - [x] Pilih Produk/Layanan
    - [x] Atur Jumlah/Kuantitas
    - [x] Perhitungan Subtotal Otomatis
    - [x] Input Diskon (jika ada)
    - [x] Perhitungan Total Otomatis
- [x] Cetak Struk/Nota Transaksi
- [x] Riwayat Transaksi
    - [x] Lihat Detail Transaksi
    - [ ] Filter Riwayat Transaksi (berdasarkan tanggal, pelanggan, dll.)
- [x] Pembatalan Transaksi (dengan otorisasi) (Dasar: Soft Delete via UI)

### Manajemen Pelanggan (Opsional)
- [ ] Tambah Pelanggan Baru
- [ ] Edit Data Pelanggan
- [ ] Lihat Daftar Pelanggan
- [ ] Riwayat Transaksi per Pelanggan

### Laporan
- [/] Laporan Penjualan Harian (Fungsional, tes nilai spesifik perlu dicek)
- [ ] Laporan Penjualan Mingguan
- [ ] Laporan Penjualan Bulanan
- [/] Laporan Produk/Layanan Terlaris (Fungsional, tes nilai spesifik perlu dicek)
- [x] Laporan Stok ATK (Lihat sisa stok - bagian dari Pengelolaan Stok)

### Pengaturan
- [x] Pengaturan Informasi Toko (Nama, Alamat, Kontak, Pesan Struk)
- [x] Pengaturan Pengguna (Admin, Kasir) - CRUD Pengguna
    - [x] Login & Manajemen Sesi Pengguna
- [ ] Pengaturan Printer untuk Struk

### Fitur Tambahan Spesifik Toko Khumaira
- [/] Perhitungan Biaya Fotokopi berdasarkan Jumlah Halaman dan Jenis Kertas (Logika Controller ada, tes input data bermasalah)
- [/] Perhitungan Biaya Print berdasarkan Jumlah Halaman, Warna/Hitam Putih, dan Jenis Kertas (Logika Controller ada, tes input data bermasalah)
- [/] Input Jasa Desain dengan Harga Fleksibel (Logika Controller ada, tes input data bermasalah)
- [/] Input Jasa Edit Dokumen dengan Harga Fleksibel (Logika Controller ada, tes input data bermasalah)
- [/] Perhitungan Biaya Cetak Banner berdasarkan Ukuran dan Bahan (Logika Controller ada, tes input data bermasalah)

## Teknologi yang Digunakan
- Framework: CodeIgniter
- Bahasa Pemrograman: PHP
- Database: MySQL (atau sesuai preferensi)
- Frontend: HTML, CSS, JavaScript (kemungkinan dengan Bootstrap atau Tailwind CSS)
