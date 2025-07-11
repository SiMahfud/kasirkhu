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

Pengembangan akan dibagi menjadi beberapa tahapan (sprint) untuk memastikan progres yang terukur dan memungkinkan adanya feedback berkelanjutan.

> **Catatan Penting tentang Pengujian (Testing):** Pengujian adalah bagian integral dari siklus pengembangan proyek ini. Setelah menyelesaikan setiap fitur atau perbaikan bug, kontributor **wajib** membuat atau memperbarui pengujian yang relevan untuk memverifikasi fungsionalitas dan mencegah regresi di masa depan.
>
> *   **Pengujian Controller (Feature Tests):** Gunakan `CodeIgniter\Test\FeatureTestTrait` untuk mensimulasikan permintaan HTTP ke aplikasi Anda. Ini memungkinkan verifikasi respons, seperti status HTTP, header, dan konten JSON atau HTML yang dikembalikan.
> *   **Pengujian Model (Database Tests):** Gunakan `CodeIgniter\Test\DatabaseTestTrait` untuk menguji logika bisnis yang berinteraksi langsung dengan database. *Trait* ini sangat berguna karena secara otomatis me-reset dan menjalankan migrasi pada database pengujian sebelum setiap tes, memastikan lingkungan yang bersih dan terisolasi.
>
> Seluruh rangkaian pengujian (test suite) harus berhasil dijalankan menggunakan perintah `composer test` sebelum kode digabungkan (merge). Ini adalah garda terdepan kita untuk menjaga kualitas dan stabilitas kode.

### Sprint 1: Inisialisasi Proyek dan Fitur Inti Produk/Layanan (Status: Selesai)
1.  **Setup Proyek CodeIgniter 4:** (Status: Selesai)
    *   Instalasi CodeIgniter 4 via Composer. (Selesai)
    *   Konfigurasi dasar (environment, database, base URL, app.php). (Selesai - diasumsikan dari fungsionalitas yang ada)
    *   Integrasi Bootstrap 5 (misalnya, melalui CDN atau download aset lokal). (Selesai - terlihat dari penggunaan class Bootstrap di views yang ada)
    *   Setup Git repository. (Selesai)
2.  **Desain Database Awal:** (Status: Selesai)
    *   Tabel `products` (id, name, code, category_id, price, unit, description, stock, created_at, updated_at). (Selesai - via Migrasi)
    *   Tabel `categories` (id, name, description, created_at, updated_at). (Selesai - via Migrasi)
    *   Tabel `users` (id, name, username, password, role, created_at, updated_at) - `role` bisa enum ('admin', 'cashier'). (Selesai - via Migrasi)
    *   Gunakan Migrations CodeIgniter untuk membuat skema database. (Selesai)
    *   Gunakan sqlite untuk sementara dalam pengembangan di environtmen. (Selesai - terbukti dari konfigurasi pengujian dan file `writable/khumaira.sqlite`)
3.  **Modul Manajemen Kategori (CRUD):** (Status: Selesai)
    *   Controller, Model, Views untuk Tambah, Lihat, Edit, Hapus Kategori. (Selesai)
4.  **Modul Manajemen Produk/Layanan (CRUD):** (Status: Selesai)
    *   Controller, Model, Views untuk Tambah, Lihat (dengan pagination, pencarian dasar), Edit, Hapus Produk/Layanan. (Selesai)
    *   Relasi ke tabel kategori. (Selesai)
5.  **Autentikasi Dasar:** (Status: Selesai)
    *   Halaman Login. (Selesai)
    *   Controller untuk proses login dan logout. (Selesai)
    *   Penggunaan Session CodeIgniter untuk manajemen status login. (Selesai)
    *   Filter untuk melindungi route yang memerlukan autentikasi. (Selesai)
6.  **Pengujian Fitur Sprint 1:** (Status: Selesai)
    *   Konfigurasi lingkungan pengujian (`phpunit.xml` disalin dan diubah untuk menggunakan SQLite in-memory, `composer.json` diubah untuk menjalankan `./vendor/bin/phpunit`). (Selesai)
    *   Dependensi pengujian (`php-sqlite3`) diinstal. (Selesai)
    *   Feature tests yang ada untuk Autentikasi, CRUD Kategori, dan CRUD Produk dijalankan. (Selesai)
    *   Sebagian besar tes (31/33) berhasil setelah perbaikan pada penanganan sesi di tes CRUD dan metode pengiriman data untuk update produk. (Selesai)
    *   Tes baru untuk fungsionalitas pencarian produk ditambahkan dan berhasil. (Selesai)
    *   Dua (2) tes di `AuthenticationTest` (`testLogoutWorks` dan `testProtectedPageRedirectsToLoginAfterLogout`) **telah diperbaiki**. Isu `TestResponse::getStatus()` yang mengembalikan `null` untuk rute `/logout` diatasi dengan melakukan `resetServices(true)` setelah panggilan logout dalam tes untuk memastikan state session yang bersih bagi asserstion berikutnya. Ini mengindikasikan bahwa masalahnya kemungkinan pada bagaimana test harness menangani state session setelah redirect dari logout. (Selesai)

### Sprint 2: Fitur Inti Transaksi (Status: Selesai)
1.  **Desain Database Transaksi:** (Status: Selesai)
    *   Tabel `transactions` (id, transaction_code, user_id, customer_name (opsional), total_amount, discount, final_amount, payment_method, created_at, updated_at, deleted_at). (Selesai - via Migrations)
    *   Tabel `transaction_details` (id, transaction_id, product_id, quantity, price_per_unit, subtotal, created_at, updated_at, service_item_details). (Selesai - via Migrations, `service_item_details` ditambahkan di Sprint 3)
    *   Gunakan Migrations. (Selesai)
2.  **Modul Transaksi Penjualan:** (Status: Selesai)
    *   Antarmuka (View dengan Bootstrap) untuk input transaksi baru: (Selesai)
        *   Pemilihan produk/layanan (dropdown). (Selesai)
        *   Input jumlah/kuantitas. (Selesai)
        *   Perhitungan subtotal dan total otomatis (JavaScript dan backend). (Selesai)
        *   Input diskon. (Selesai)
    *   Controller untuk memproses dan menyimpan data transaksi ke tabel `transactions` dan `transaction_details`. (Selesai)
    *   Pengurangan stok produk ATK secara otomatis (implementasi dasar berdasarkan ketersediaan field `stock` dan tipe unit produk). (Diperbarui di Sprint 3)
3.  **Riwayat Transaksi Sederhana:** (Status: Selesai)
    *   Menampilkan daftar transaksi (dengan pagination). (Selesai)
    *   Menampilkan detail per transaksi (termasuk item-item yang dibeli). (Selesai)
4.  **Pengujian Fitur Sprint 2 (dan perbaikan berkelanjutan):** (Status: Selesai)
    *   Feature tests untuk `TransactionController` (akses halaman, pembuatan transaksi sukses/gagal, riwayat, detail, hapus) dibuat dan sebagian besar diperbaiki. (Selesai)
    *   Lingkungan pengujian distabilkan dengan `BaseFeatureTestCase` untuk memastikan migrasi berjalan konsisten. `DBPrefix` dikosongkan untuk `tests` group di `app/Config/Database.php` untuk menghindari isu dengan SQLite.
    *   Dua (2) tes di `AuthenticationTest` (`testLogoutWorks` dan `testProtectedPageRedirectsToLoginAfterLogout`) **telah diperbaiki** (lihat catatan di Sprint 1). (Selesai)
    *   `ReportFeatureTest` yang sebelumnya memiliki 2 kegagalan terkait asserstion nilai numerik **telah diperbaiki**. Isu utama adalah data transaksi 'kemarin' yang tidak tersimpan dengan tanggal yang benar dalam setup tes, serta asserstion yang terlalu rapuh terhadap struktur HTML. Perbaikan melibatkan update manual `created_at` pada data tes dan penyesuaian pada metode `assertSee`. (Selesai)

### Sprint 3: Penyempurnaan Transaksi dan Laporan Awal (Status: Selesai)
1.  **Pencetakan Struk/Nota:** (Status: Selesai)
    *   Desain template struk (`app/Views/transactions/receipt.php`) menggunakan HTML/CSS Bootstrap. (Selesai)
    *   Fungsi di `TransactionController::receipt()` untuk menghasilkan halaman struk yang siap cetak. (Selesai)
    *   Menampilkan informasi toko (dari `SettingModel`) di struk. (Selesai)
    *   Diuji melalui `ReceiptTest.php`. (Selesai)
2.  **Manajemen Stok ATK (Lanjutan):** (Status: Selesai)
    *   View (`products/stock_report.php`) untuk melihat sisa stok produk. (Selesai)
    *   Fitur sederhana di `ProductController::adjustStock()` untuk penyesuaian/penambahan stok manual (oleh Admin). (Selesai)
    *   Diuji melalui `StockManagementTest.php`. (Selesai)
3.  **Laporan Penjualan Dasar:** (Status: Selesai)
    *   Laporan penjualan harian (total omset, jumlah transaksi) di `ReportController::dailySales()` dan view `reports/daily_sales.php`. (Selesai)
    *   Laporan produk/layanan terlaris di `ReportController::topProducts()` dan view `reports/top_products.php`. (Selesai)
    *   Filter laporan berdasarkan rentang tanggal. (Selesai)
    *   Diuji melalui `ReportFeatureTest.php`. **Semua tes terkait laporan penjualan dasar kini berhasil.** Isu utama adalah data transaksi 'kemarin' yang tidak tersimpan dengan tanggal yang benar dalam setup tes, serta asserstion yang terlalu rapuh terhadap struktur HTML. Perbaikan melibatkan update manual `created_at` pada data tes dan penyesuaian pada metode `assertSee`. (Selesai)
4.  **Perhitungan Spesifik Toko Khumaira (Implementasi Awal):** (Status: Selesai)
    *   `TransactionController::create()` diperbarui untuk menangani:
        *   Jasa fotokopi/print: menggunakan `service_item_price` jika dikirim dari form, dan menyimpan detail seperti jumlah halaman, jenis kertas, warna di `service_item_details`. (Logika di controller ada)
        *   Jasa desain/edit/banner: menggunakan `manual_price` jika dikirim dari form atau jika harga produk dasar adalah 0, menyimpan deskripsi di `service_item_details`. (Logika di controller ada)
    *   Pengurangan stok disesuaikan agar hanya berlaku untuk unit produk yang dikelola stoknya (misalnya 'pcs', 'rim'), bukan untuk unit jasa ('lembar', 'project'). (Logika di controller ada)
    *   Tes baru ditambahkan di `TransactionControllerTest.php` (`testCreateTransactionWithFotokopiServicePrice`, `testCreateTransactionWithManualPriceService`) untuk fitur ini. (Selesai)
    *   Status tes: **Semua tes terkait perhitungan spesifik kini berhasil.** Isu utama adalah `service_item_details` tidak masuk dalam `allowedFields` di `TransactionDetailModel`, sehingga tidak tersimpan ke database. Setelah ditambahkan, tes berhasil. Juga, helper `form` dan `url` ditambahkan ke `TransactionController` untuk mengatasi error pada tes lain yang muncul setelah perubahan model. (Selesai)

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
*   **Coding Style:**
    *   Mengikuti standar PSR-12 (Extended Coding Style). Gunakan tools seperti PHP CS Fixer jika memungkinkan.
    *   Komentari kode yang kompleks atau tidak jelas.
    *   Gunakan nama variabel dan fungsi yang deskriptif dalam bahasa Inggris.
*   **Alur Kerja Git:**
    1.  Buat _fork_ dari repository utama.
    2.  Buat _branch_ baru dari `main` atau `develop` untuk setiap fitur atau perbaikan bug (misalnya, `feature/nama-fitur` atau `fix/bug-deskripsi`).
    3.  Commit perubahan secara berkala dengan pesan commit yang jelas dan deskriptif (misalnya, "feat: Add user login functionality").
    4.  Pastikan semua test lolos (`composer test`) sebelum membuat Pull Request.
    5.  Buat Pull Request ke branch `main` atau `develop` di repository utama. Jelaskan perubahan yang dibuat dan pastikan semua automated checks (jika ada) lolos.
*   **Issue Tracker:** Gunakan GitHub Issues untuk melaporkan bug atau mengusulkan fitur baru.

## 5. Pengujian (Testing)

Pengujian adalah pilar utama dalam pengembangan aplikasi ini untuk memastikan kualitas, stabilitas, dan kemudahan pemeliharaan. Semua kontribusi dalam bentuk fitur baru atau perbaikan bug harus menyertakan pengujian yang relevan. CodeIgniter 4 memiliki dukungan pengujian kelas satu menggunakan PHPUnit.

#### a. Konfigurasi Lingkungan Pengujian

Sebelum menjalankan pengujian, pastikan lingkungan Anda terkonfigurasi dengan benar.

1.  **Salin File Konfigurasi PHPUnit:** Salin file `phpunit.xml.dist` menjadi `phpunit.xml`. File `phpunit.xml` ini akan digunakan untuk konfigurasi lokal Anda dan tidak akan di-commit ke Git.
    ```bash
    cp phpunit.xml.dist phpunit.xml
    ```
2.  **Konfigurasi Database Pengujian:** Untuk menjaga integritas database pengembangan Anda, pengujian akan berjalan pada database terpisah. Buka file `phpunit.xml` dan atur variabel database di dalam bagian `<php>`. Sangat disarankan untuk menggunakan database **SQLite** yang berjalan di memori karena kecepatannya dan tidak memerlukan setup server database.

    ```xml
    <!-- phpunit.xml -->
    <php>
        <server name="app.baseURL" value="http://localhost:8080/"/>
        <env name="database.default.hostname" value="localhost"/>
        <env name="database.default.database" value="ci4_test_db"/> <!-- Atau nama database tes Anda -->
        <env name="database.default.username" value="root"/>
        <env name="database.default.password" value=""/>
        <env name="database.default.DBDriver" value="MySQLi"/>

        <!-- Contoh Konfigurasi untuk SQLite in-memory (Direkomendasikan) -->
        <!-- <env name="database.default.database" value=":memory:"/> -->
        <!-- <env name="database.default.DBDriver" value="SQLite3"/> -->
    </php>
    ```

#### b. Menjalankan Pengujian

Untuk menjalankan seluruh rangkaian pengujian, gunakan perintah Composer dari direktori root proyek.

```bash
composer test
```

**Penting:** Selalu gunakan `composer test`. Perintah ini adalah alias yang telah dikonfigurasi dalam `composer.json` untuk menjalankan PHPUnit dengan bootstrap CodeIgniter. Menjalankannya memastikan semua layanan dan konfigurasi kerangka kerja dimuat dengan benar sebelum pengujian dimulai. Jangan menjalankan `vendor/bin/phpunit` secara langsung.

#### c. Membuat File Pengujian

CodeIgniter 4 menyediakan perintah Spark untuk mempercepat pembuatan file pengujian.

```bash
# Membuat file Feature Test
php spark make:test Feature/TransactionTest

# Membuat file Unit Test
php spark make:test Unit/PriceCalculatorTest
```

Perintah ini akan membuat file kerangka (boilerplate) di dalam direktori `tests/Feature` atau `tests/Unit`.

#### d. Menulis Pengujian

Berikut adalah panduan dan contoh untuk jenis pengujian yang paling umum di proyek ini.

##### Feature Tests (Untuk Controller dan Routes)
Gunakan `CodeIgniter\Test\FeatureTestTrait` untuk menguji endpoint dari aplikasi Anda seolah-olah diakses melalui browser.

```php
// tests/Feature/ProductControllerTest.php
namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class ProductControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait; // Gunakan jika endpoint berinteraksi dengan DB
    use FeatureTestTrait;

    // Otomatis dijalankan sebelum setiap tes di kelas ini
    protected function setUp(): void
    {
        parent::setUp();
        // Jalankan seeder jika perlu data awal
        // $this->seed('CategorySeeder');
        // $this->seed('ProductSeeder');
    }

    public function testCanViewProductListPage()
    {
        // Simulasikan request GET ke /products
        $result = $this->get('/products');

        // Lakukan assertions (pemeriksaan)
        $result->assertStatus(200);
        $result->assertSee('Daftar Produk');
        $result->assertSee('Nama Produk Uji'); // Cek apakah data dari seeder tampil
    }

    public function testAdminCanDeleteProduct()
    {
        // Simulasikan user admin login (jika menggunakan fitur `actAs`)
        // $admin = model('UserModel')->find(1);
        // $this->actingAs($admin);

        // Kirim request DELETE ke endpoint. Gantilah '1' dengan ID produk yang valid.
        $result = $this->delete('/products/1');

        $result->assertStatus(302); // Asumsi redirect setelah delete
        $result->assertRedirectTo('/products');

        // Pastikan data tidak lagi ada di database
        $this->dontSeeInDatabase('products', ['id' => 1]);
    }
}
```

##### Database Tests (Untuk Model)
Gunakan `CodeIgniter\Test\DatabaseTestTrait` untuk menguji metode dalam Model Anda.

```php
// tests/Models/ProductModelTest.php
namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\ProductModel;

class ProductModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    // Kita tidak ingin migrasi berjalan untuk setiap tes, cukup sekali per kelas.
    protected $migrate = true;
    // Tentukan seeder yang akan dijalankan sekali untuk kelas ini.
    protected $seed = 'ProductSeeder';

    public function testFindProductById()
    {
        $model = new ProductModel();
        $product = $model->find(1); // Asumsi ID 1 dibuat oleh ProductSeeder

        $this->assertIsObject($product);
        $this->assertEquals('Nama Produk Uji Dari Seeder', $product->name);
    }
}
```

## 6. Lisensi

Proyek ini akan dirilis di bawah lisensi **[NAMA LISENSI, misalnya MIT License atau GNU GPLv3]**. (Akan ditentukan kemudian jika proyek menjadi open source).

## 7. Prioritas Pengembangan
Fitur-fitur akan diprioritaskan berdasarkan kebutuhan inti operasional toko:
1.  Manajemen Produk/Layanan & Kategori.
2.  Autentikasi & Manajemen Pengguna Dasar.
3.  Transaksi Penjualan (termasuk perhitungan spesifik).
4.  Cetak Struk.
5.  Laporan Penjualan Dasar.

## Catatan Tambahan
*   Desain UI/UX akan mengutamakan fungsionalitas dan kemudahan penggunaan dengan komponen Bootstrap 5.
*   Rencana ini bersifat fleksibel dan dapat disesuaikan seiring berjalannya proyek dan berdasarkan feedback.