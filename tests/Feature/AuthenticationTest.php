<?php

namespace Tests\Feature;

use Tests\Support\Database\BaseFeatureTestCase; // Import the base class
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel; // For fetching user

class AuthenticationTest extends BaseFeatureTestCase
{
    protected array $adminSessionData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed('App\Database\Seeds\AdminUserSeeder');

        $userModel = model(ShieldUserModel::class);
        $adminUser = $userModel->findByCredentials(['email' => 'admin@example.com']);

        if (!$adminUser) {
            $adminUser = $userModel->where('username', 'admin')->first();
        }

        if (!$adminUser) {
            $this->fail('Admin user "admin" (admin@example.com) not found after seeding. Check AdminUserSeeder.');
        }

        $adminFullName = $adminUser->username; // Default to username
        // Shield's User entity has getIdentities() method which returns array of identity objects
        $identities = $adminUser->getIdentities();
        foreach ($identities as $identity) {
            // In AdminUserSeeder, I set up 'Full Name' for the 'name' identity type.
            // However, the default 'email_password' identity also has a 'name' field that can be used.
            // Let's assume AdminUserSeeder sets a specific identity for 'Full Name' or we use a default.
            // The identity created by AdminUserSeeder for 'name' was:
            // [ 'type' => 'name', 'secret' => 'Administrator', 'name' => 'Full Name']
            // So we look for type 'name' or a specific named identity.
            // For simplicity, if 'name' identity was set by seeder:
            if ($identity->type === 'name' && $identity->name === 'Full Name') {
                $adminFullName = $identity->secret;
                break;
            }
        }

        $this->adminSessionData = [
            'logged_in'  => $adminUser->id,
            'isLoggedIn' => true,
            'username'   => $adminUser->username,
            'name'       => $adminFullName,
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
        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionHas('error', 'Password salah.');
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
        $this->assertFalse(session()->get('isLoggedIn') ?? false, "isLoggedIn should be false or not set after failed login.");
    }

    public function testLoginSucceedsAndCanAccessProtectedPage()
    {
        $loginResult = $this->post('/login', [
            'username' => 'admin',
            'password' => 'password123'
        ]);
        $loginResult->assertRedirectTo(site_url('/products'));
        $loginResult->assertSessionHas('message', 'Login berhasil! Selamat datang, Administrator');
    }

    public function testProtectedPageRedirectsToLoginIfNotLoggedIn()
    {
        $result = $this->get('/products');
        $result->assertStatus(302);
        $result->assertRedirectTo(site_url('/login'));
        $result->assertSessionHas('error', 'Anda harus login untuk mengakses halaman ini.');
    }

    public function testProtectedPageIsAccessibleIfLoggedInWithSessionTrait()
    {
        // This test uses a custom session structure that might no longer match how AuthController works with Shield.
        // Prefer actingAs($user) for Shield tests.
        // For this test to pass as-is, AuthController would need to set 'role' and 'name' in session.
        // Since we've moved to Shield, this test is less relevant or needs updating to Shield's session structure
        // or use actingAs. Given $this->adminSessionData is now more Shield-like, let's use that.
        // However, the original test had hardcoded 'user_id' => 1, 'name' => 'Administrator', 'role' => 'admin'.
        // This implies the old seeder structure.
        // Let's try using actingAs for this one.
        $userModel = model(ShieldUserModel::class);
        $adminUser = $userModel->findByCredentials(['email' => 'admin@example.com']) ?? $userModel->where('username', 'admin')->first();
        $this->assertNotNull($adminUser, "Admin user for actingAs not found.");

        $result = $this->actingAs($adminUser)->get('/products');
        $result->assertStatus(200);
        $result->assertSee('Daftar Produk');
    }

    public function testLogoutWorks()
    {
        $userModel = model(ShieldUserModel::class);
        $adminUser = $userModel->findByCredentials(['email' => 'admin@example.com']) ?? $userModel->where('username', 'admin')->first();
        $this->assertNotNull($adminUser, "Admin user for logout test not found.");

        $result = $this->actingAs($adminUser)->call('get', '/logout');

        $this->resetServices(true);

        $nextResult = $this->get('/products');
        $nextResult->assertStatus(302);
        $nextResult->assertRedirectTo(site_url('/login'));
    }

    public function testProtectedPageRedirectsToLoginAfterLogout()
    {
        $userModel = model(ShieldUserModel::class);
        $adminUser = $userModel->findByCredentials(['email' => 'admin@example.com']) ?? $userModel->where('username', 'admin')->first();
        $this->assertNotNull($adminUser, "Admin user for logout test not found.");

        $this->actingAs($adminUser)->call('get', '/logout');

        $this->resetServices(true);

        $result = $this->get('/products');

        $result->assertStatus(302);
        $result->assertRedirectTo(site_url('/login'));
        $result->assertSessionHas('error', 'Anda harus login untuk mengakses halaman ini.');
    }
}
