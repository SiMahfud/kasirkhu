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
- [ ] Pengelolaan Stok untuk ATK

### Transaksi Penjualan
- [ ] Input Transaksi Baru
    - [ ] Pilih Produk/Layanan
    - [ ] Atur Jumlah/Kuantitas
    - [ ] Perhitungan Subtotal Otomatis
    - [ ] Input Diskon (jika ada)
    - [ ] Perhitungan Total Otomatis
- [ ] Cetak Struk/Nota Transaksi
- [ ] Riwayat Transaksi
    - [ ] Lihat Detail Transaksi
    - [ ] Filter Riwayat Transaksi (berdasarkan tanggal, pelanggan, dll.)
- [ ] Pembatalan Transaksi (dengan otorisasi)

### Manajemen Pelanggan (Opsional)
- [ ] Tambah Pelanggan Baru
- [ ] Edit Data Pelanggan
- [ ] Lihat Daftar Pelanggan
- [ ] Riwayat Transaksi per Pelanggan

### Laporan
- [ ] Laporan Penjualan Harian
- [ ] Laporan Penjualan Mingguan
- [ ] Laporan Penjualan Bulanan
- [ ] Laporan Produk/Layanan Terlaris
- [ ] Laporan Stok ATK (jika ada fitur pengelolaan stok)

### Pengaturan
- [ ] Pengaturan Informasi Toko (Nama, Alamat, Kontak)
- [ ] Pengaturan Pengguna (Admin, Kasir)
    - [x] Login & Manajemen Sesi Pengguna
- [ ] Pengaturan Printer untuk Struk

### Fitur Tambahan Spesifik Toko Khumaira
- [ ] Perhitungan Biaya Fotokopi berdasarkan Jumlah Halaman dan Jenis Kertas
- [ ] Perhitungan Biaya Print berdasarkan Jumlah Halaman, Warna/Hitam Putih, dan Jenis Kertas
- [ ] Input Jasa Desain dengan Harga Fleksibel
- [ ] Input Jasa Edit Dokumen dengan Harga Fleksibel
- [ ] Perhitungan Biaya Cetak Banner berdasarkan Ukuran dan Bahan

## Teknologi yang Digunakan
- Framework: CodeIgniter
- Bahasa Pemrograman: PHP
- Database: MySQL (atau sesuai preferensi)
- Frontend: HTML, CSS, JavaScript (kemungkinan dengan Bootstrap atau Tailwind CSS)
