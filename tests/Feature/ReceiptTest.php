<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use App\Models\SettingModel; // Added SettingModel

class ReceiptTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false; // Ensure migrations run for each test for isolation
    protected $seed        = 'DatabaseSeeder'; // Assuming you have a seeder that sets up necessary data
    protected $basePath    = APPPATH . 'Database'; // Correct path for seeds if not default

    protected $user;
    protected $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        // Manually seed necessary data or use specific seeders if DatabaseSeeder is too broad or slow
        $this->db->table('categories')->truncate();
        $this->db->table('products')->truncate();
        $this->db->table('users')->truncate();
        $this->db->table('transactions')->truncate();
        $this->db->table('transaction_details')->truncate();
        $this->db->table('settings')->truncate(); // Truncate settings

        // It's better to call specific seeders if available
        $this->seed('UserSeeder');
        $this->seed('CategorySeeder');
        $this->seed('ProductSeeder');
        $this->seed('SettingSeeder'); // Seed the settings

        // Get a user to act as
        $userModel = new UserModel();
        $this->user = $userModel->first(); // Get the first user created by seeder

        if (!$this->user) {
            // Fallback if seeder didn't create a user or it's not findable this way
            $userModel->insert([
                'name' => 'Test User',
                'username' => 'testuser',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'admin'
            ]);
            $this->user = $userModel->first();
        }

        // Create a sample transaction to test receipt for
        $productModel = new ProductModel();
        $product = $productModel->first(); // Get a product

        if (!$product) {
             // Fallback if seeder didn't create a product
            $categoryModel = new CategoryModel();
            $catId = $categoryModel->insert(['name' => 'Test Kategori Produk']);

            $productModel->insert([
                'name' => 'Test Product for Receipt',
                'code' => 'RCPT001',
                'category_id' => $catId,
                'price' => 10000,
                'unit' => 'pcs',
                'stock' => 100,
            ]);
            $product = $productModel->first();
        }

        $transactionModel = new TransactionModel();
        $transactionData = [
            'user_id' => $this->user->id,
            'customer_name' => 'Customer Test Receipt',
            'total_amount' => $product->price * 2,
            'discount' => 0,
            'final_amount' => $product->price * 2,
            'payment_method' => 'cash',
            // transaction_code will be set by model callback
        ];
        $transactionId = $transactionModel->insert($transactionData);
        $this->transaction = $transactionModel->find($transactionId); // Fetch to get all fields including transaction_code

        $transactionDetailModel = new TransactionDetailModel();
        $transactionDetailModel->insert([
            'transaction_id' => $this->transaction->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_per_unit' => $product->price,
            'subtotal' => $product->price * 2,
        ]);
    }

    public function testAccessReceiptPageSuccessfully()
    {
        if (!$this->user || !$this->transaction) {
            $this->markTestSkipped('User or transaction not set up correctly for test.');
        }

        // Act as the logged-in user
        $result = $this->actingAs($this->user)
                       ->get('/transactions/' . $this->transaction->id . '/receipt');

        $result->assertStatus(200);
        $result->assertSee($this->transaction->transaction_code);

        // Check for store info from SettingSeeder
        $settingModel = new SettingModel();
        $storeName = $settingModel->getSetting('store_name');
        $storeAddress = $settingModel->getSetting('store_address');
        $receiptFooter = $settingModel->getSetting('receipt_footer_message');

        $result->assertSee(esc($storeName));
        $result->assertSee(esc($storeAddress));
        $result->assertSee(esc($receiptFooter));

        $result->assertSee('Customer Test Receipt');
        $result->assertSee('Test Product for Receipt'); // Product name
        $result->assertSee(number_format($this->transaction->final_amount, 0, ',', '.'));
        $result->assertSee('Cetak Struk'); // Print button
    }

    public function testReceiptPageForNonExistentTransaction()
    {
        if (!$this->user) {
            $this->markTestSkipped('User not set up correctly for test.');
        }
        $nonExistentId = 999999;
        $result = $this->actingAs($this->user)
                       ->get('/transactions/' . $nonExistentId . '/receipt');

        $result->assertStatus(302); // Expecting a redirect
        $result->assertRedirectTo('/transactions');
        // Check session flash data for error message
        $result->assertSessionHas('error', 'Transaction not found.');
    }
}
