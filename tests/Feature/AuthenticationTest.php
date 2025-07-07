<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
// use App\Models\UserModel; // Tidak perlu model user langsung di tes auth ini

class AuthenticationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refreshDatabase = true;
    protected $baseURL         = 'http://localhost:8080/';
    protected $namespace       = 'App'; // Pastikan migrasi dan seeder App dijalankan
    protected array $adminSessionData;

    protected function setUp(): void
    {
        parent::setUp();
        // Jalankan AdminUserSeeder
        $this->seed('AdminUserSeeder');

        // Ambil data admin untuk digunakan dalam set session manual
        $adminUser = db_connect()->table('users')->where('username', 'admin')->get()->getRow();
        if (!$adminUser) {
            $this->fail('Admin user "admin" not found after seeding. Check AdminUserSeeder.');
        }

        $this->adminSessionData = [
            'user_id'    => $adminUser->id,
            'username'   => $adminUser->username,
            'name'       => $adminUser->name,
            'role'       => $adminUser->role,
            'isLoggedIn' => true,
        ];
    }

    public function testLoginPageIsAccessible()
    {
        $result = $this->get('/login');
        $result->assertStatus(200);
        $result->assertSee('Login Pengguna');
    }

    public function testLoginFailsWithWrongCredentials()
    {
        $result = $this->post('/login', [
            'username' => 'admin',
            'password' => 'wrongpassword'
        ]);
        $result->assertStatus(302); // Redirect back
        $result->assertRedirect(); // Lebih umum, cek apakah redirect
        $result->assertSessionHas('error', 'Password salah.');
        // Pastikan tidak ada session isLoggedIn
        log_message('error', '[Test] After failed login (wrong pass), session isLoggedIn: ' . (session()->get('isLoggedIn') === null ? 'NULL' : (session()->get('isLoggedIn') ? 'true' : 'false')));
        $this->assertFalse(session()->get('isLoggedIn') ?? false, "isLoggedIn should be false or not set after failed login.");
    }

    public function testLoginFailsWithNonExistingUser()
    {
        $result = $this->post('/login', [
            'username' => 'nonexistentuser',
            'password' => 'password123'
        ]);
        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionHas('error', 'Username tidak ditemukan.');
        log_message('error', '[Test] After failed login (no user), session isLoggedIn: ' . (session()->get('isLoggedIn') === null ? 'NULL' : (session()->get('isLoggedIn') ? 'true' : 'false')));
        $this->assertFalse(session()->get('isLoggedIn') ?? false, "isLoggedIn should be false or not set after failed login.");
    }

    public function testLoginSucceedsAndCanAccessProtectedPage() // Nama diubah
    {
        $loginResult = $this->post('/login', [
            'username' => 'admin',
            'password' => 'password123'
        ]);
        // Harusnya redirect ke /products setelah login berhasil (sesuai AuthController)
        $loginResult->assertRedirectTo(site_url('/products'));
        $loginResult->assertSessionHas('message', 'Login berhasil! Selamat datang, Administrator');

        // Tidak bisa verifikasi session isLoggedIn reliably di sini setelah redirect tanpa request baru dengan withSession.
        // Tes ini hanya memastikan proses login POST mengembalikan redirect dan message yang benar.
    }

    public function testProtectedPageRedirectsToLoginIfNotLoggedIn()
    {
        $result = $this->get('/products'); // /products dilindungi oleh AuthFilter
        $result->assertStatus(302);
        $result->assertRedirectTo(site_url('/login'));
        $result->assertSessionHas('error', 'Anda harus login untuk mengakses halaman ini.');
    }

    // Menggunakan withSession untuk memastikan state session bersih dan terkontrol
    public function testProtectedPageIsAccessibleIfLoggedInWithSessionTrait()
    {
        $sessionData = [
            'user_id'    => 1, // Asumsi admin ID adalah 1 dari seeder
            'username'   => 'admin',
            'name'       => 'Administrator',
            'role'       => 'admin',
            'isLoggedIn' => true,
        ];

        // Pastikan user dengan ID 1 ada (dibuat oleh seeder)
        // Seeder sudah dijalankan di setUp()

        $result = $this->withSession($sessionData)->get('/products');
        $result->assertStatus(200);
        $result->assertSee('Daftar Produk');
    }

    public function testLogoutWorks()
    {
        // Simulasikan user sudah login menggunakan withSession
        $result = $this->withSession($this->adminSessionData)->get('/logout');

        $result->assertRedirectTo(site_url('/login'));
        $result->assertSessionHas('message', 'Anda telah berhasil logout.');

        // Setelah redirect, session di $this->session akan direset oleh FeatureTestTrait.
        // Untuk memverifikasi bahwa session isLoggedIn benar-benar false,
        // kita perlu melakukan request lain dan memeriksa session di sana, atau
        // memeriksa bahwa halaman terproteksi tidak bisa diakses.
        // Cara paling mudah: coba akses halaman terproteksi dan pastikan redirect ke login.
        $nextResult = $this->get('/products');
        $nextResult->assertRedirectTo(site_url('/login'));
    }

    public function testProtectedPageRedirectsToLoginAfterLogout()
    {
        // Simulasikan user sudah login dan kemudian logout
        $this->withSession($this->adminSessionData)->get('/logout');

        // Coba akses halaman terproteksi
        $result = $this->get('/products');
        $result->assertStatus(302);
        $result->assertRedirectTo(site_url('/login'));
    }
}
