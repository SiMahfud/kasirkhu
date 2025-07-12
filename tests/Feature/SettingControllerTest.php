<?php

namespace Tests\Feature;

use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use App\Models\UserModel;
use App\Models\SettingModel;
use CodeIgniter\Shield\Test\AuthenticationTesting; // Add Shield's testing trait

class SettingControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting; // Use Shield's testing trait

    protected $migrate = true;
    protected $seed = 'App\Database\Seeds\DatabaseSeeder'; // Specify full namespace
    // protected $namespace = 'App'; // Remove to allow discovery of all migrations (including Shield's)

    protected $admin;
    protected $nonAdminUser;
    protected $settingModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingModel = new SettingModel();

        // Find the admin user (assuming seeder creates one with username 'admin' or role 'admin')
        // Adjust this logic based on your actual seeder / user setup for admin
        $userModel = new UserModel();
        $this->admin = $userModel->where('username', 'admin')->first();
        // If no specific admin user, try to find one with an 'admin' group/role.
        // This part is tricky without knowing exactly how Shield groups are seeded.
        // For now, we'll rely on the 'admin' username.
        // If your admin user is identified differently, this needs adjustment.

        $this->nonAdminUser = $userModel->where('username !=', 'admin')->first(); // Get any non-admin user

        // Fallback if admin not found by username, create one (ensure this matches your user structure and Shield setup)
        if (!$this->admin) {
            $userId = $userModel->insert([
                'name' => 'Test Admin',
                'username' => 'testadmin',
                'email' => 'testadmin@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
            ]);
            $this->admin = $userModel->find($userId);
            // IMPORTANT: Assign admin role/group if Shield is used
            // This typically involves using Shield's services, e.g., $this->admin->addGroup('admin');
            // For feature tests, actingAs might handle permissions if the user has them.
            // We are assuming 'admin.settings' permission is granted to the 'admin' group/role.
        }

        // Ensure a non-admin user exists for testing authorization
        if (!$this->nonAdminUser) {
            $userId = $userModel->insert([
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'testuser@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
            ]);
            $this->nonAdminUser = $userModel->find($userId);
        }
    }

    public function testAdminCanAccessSettingsPage()
    {
        if (!$this->admin) {
            $this->markTestSkipped('Admin user not found or configured for testing.');
        }

        $result = $this->actingAs($this->admin)
                         ->get('/admin/settings');

        $result->assertStatus(200);
        $result->assertSee('Store Settings');
        $result->assertSee('Store Name');
    }

    public function testNonAdminCannotAccessSettingsPage()
    {
        if (!$this->nonAdminUser) {
            $this->markTestSkipped('Non-admin user not found for testing.');
        }
        // Assuming non-admin users do not have 'admin.settings' permission
        $result = $this->actingAs($this->nonAdminUser)
                         ->get('/admin/settings');

        $result->assertStatus(302); // Redirect
        $result->assertRedirectTo(base_url()); // Or login page, depending on Auth filter
    }

    public function testGuestCannotAccessSettingsPage()
    {
        $result = $this->get('/admin/settings');
        $result->assertStatus(302); // Redirect
        $result->assertRedirectTo(base_url('login')); // Should redirect to login
    }

    public function testAdminCanUpdateSettings()
    {
        if (!$this->admin) {
            $this->markTestSkipped('Admin user not found or configured for testing.');
        }

        $postData = [
            'store_name' => 'My Awesome Store',
            'store_address' => '123 Main St, Anytown',
            'store_phone' => '555-1234',
            'receipt_footer_message' => 'Thanks for shopping!',
        ];

        $result = $this->actingAs($this->admin)
                         ->post('/admin/settings/update', $postData);

        $result->assertStatus(302); // Redirect after successful update
        $result->assertRedirectTo('/admin/settings');
        $result->assertSessionHas('message', 'Settings updated successfully.');

        // Verify settings were saved
        $this->assertEquals('My Awesome Store', $this->settingModel->getSetting('store_name'));
        $this->assertEquals('123 Main St, Anytown', $this->settingModel->getSetting('store_address'));
        $this->assertEquals('555-1234', $this->settingModel->getSetting('store_phone'));
        $this->assertEquals('Thanks for shopping!', $this->settingModel->getSetting('receipt_footer_message'));
    }

    public function testUpdateSettingsWithEmptyValues()
    {
        if (!$this->admin) {
            $this->markTestSkipped('Admin user not found or configured for testing.');
        }

        // First, set some initial values
        $this->settingModel->saveSetting('store_name', 'Initial Store');
        $this->settingModel->saveSetting('store_address', 'Initial Address');

        $postData = [
            'store_name' => '', // Empty value
            'store_address' => 'New Address',
            'store_phone' => '', // Empty value
            'receipt_footer_message' => 'New Footer',
        ];

        $result = $this->actingAs($this->admin)
                         ->post('/admin/settings/update', $postData);

        $result->assertStatus(302);
        $result->assertRedirectTo('/admin/settings');
        $result->assertSessionHas('message', 'Settings updated successfully.');

        $this->assertEquals('', $this->settingModel->getSetting('store_name'));
        $this->assertEquals('New Address', $this->settingModel->getSetting('store_address'));
        $this->assertEquals('', $this->settingModel->getSetting('store_phone'));
        $this->assertEquals('New Footer', $this->settingModel->getSetting('receipt_footer_message'));
    }

    // Add more tests for validation if specific rules were added (e.g. max_length)
    // For now, the controller rules are quite permissive ('permit_empty|string')
}
