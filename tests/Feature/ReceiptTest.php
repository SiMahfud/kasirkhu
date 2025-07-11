<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use App\Models\SettingModel;
use Tests\Support\Database\BaseFeatureTestCase;

class ReceiptTest extends BaseFeatureTestCase
{
    // Traits, $namespace, $DBGroup, $baseURL, and migration handling inherited from BaseFeatureTestCase.
    // protected $seed = 'DatabaseSeeder'; // Can be set if DatabaseSeeder is desired for all tests.

    protected UserModel $userModel;
    protected ProductModel $productModel;
    protected CategoryModel $categoryModel;
    protected TransactionModel $transactionModel;
    protected TransactionDetailModel $transactionDetailModel;
    protected SettingModel $settingModel;

    protected $loggedInUser; // Changed from $user to avoid confusion with UserModel instance
    protected $testTransaction; // Changed from $transaction

    protected function setUp(): void
    {
        parent::setUp(); // Handles migrations via BaseFeatureTestCase

        // Initialize models
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();
        $this->settingModel = new SettingModel();

        // Seed necessary data after migrations
        // BaseFeatureTestCase does not run seeders by default unless $this->seed is set there.
        // So, we run them here.
        $this->seed('AdminUserSeeder'); // Corrected from UserSeeder
        $this->seed('CategorySeeder');
        $this->seed('ProductSeeder');
        $this->seed('SettingSeeder');

        // Get a user to act as
        $this->loggedInUser = $this->userModel->where('role', 'admin')->get()->getRow();
        if (!$this->loggedInUser) {
            $this->loggedInUser = $this->userModel->first(); // Fallback
        }
        if (!$this->loggedInUser) {
            // Fallback if seeder didn't create a user or it's not findable
            $userId = $this->userModel->insert([
                'name' => 'Test Receipt User',
                'username' => 'receiptuser' . random_int(1000, 9999),
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'admin'
            ]);
            $this->loggedInUser = $this->userModel->find($userId);
        }
        $this->assertNotNull($this->loggedInUser, "Failed to get/create a user for tests.");


        // Create/fetch a specific product for this test
        $testProductName = 'Test Product for Receipt';
        $product = $this->productModel->where('name', $testProductName)->first();

        if (!$product) {
            $category = $this->categoryModel->first();
            if (!$category) {
                $catId = $this->categoryModel->insert(['name' => 'Receipt Test Cat']);
                $category = $this->categoryModel->find($catId);
            }
            $this->assertNotNull($category, "Failed to get/create a category for receipt product.");

            $productId = $this->productModel->insert([
                'name' => $testProductName, 'code' => 'RCPT001',
                'category_id' => $category->id, 'price' => 10000,
                'unit' => 'pcs', 'stock' => 100,
            ]);
            $product = $this->productModel->find($productId);
        }
        $this->assertNotNull($product, "Failed to get/create product named '{$testProductName}'.");


        $transactionData = [
            'user_id' => $this->loggedInUser->id,
            'customer_name' => 'Customer Test Receipt',
            'total_amount' => $product->price * 2,
            'discount' => 0,
            'final_amount' => $product->price * 2,
            'payment_method' => 'cash',
        ];
        $transactionId = $this->transactionModel->insert($transactionData);
        $this->assertTrue($transactionId !== false, "Failed to create transaction. Errors: " . implode(', ', $this->transactionModel->errors()));
        $this->testTransaction = $this->transactionModel->find($transactionId);
        $this->assertNotNull($this->testTransaction, "Failed to retrieve created transaction.");


        $detailSuccess = $this->transactionDetailModel->insert([
            'transaction_id' => $this->testTransaction->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_per_unit' => $product->price,
            'subtotal' => $product->price * 2,
        ]);
        // $this->assertTrue($detailSuccess, "Failed to create transaction detail.");
        if ($detailSuccess === false) {
            $errors = $this->transactionDetailModel->errors();
            $this->fail("Failed to create transaction detail. Errors: " . implode(', ', $errors));
        }
    }

    public function testAccessReceiptPageSuccessfully()
    {
        if (!$this->loggedInUser || !$this->testTransaction) {
            $this->markTestSkipped('User or transaction not set up correctly for test.');
        }
        $sessionData = [
            'user_id'    => $this->loggedInUser->id,
            'username'   => $this->loggedInUser->username,
            'name'       => $this->loggedInUser->name,
            'role'       => $this->loggedInUser->role,
            'isLoggedIn' => true,
        ];
        // Act as the logged-in user
        $result = $this->withSession($sessionData)
                       ->get('/transactions/' . $this->testTransaction->id . '/receipt');

        $result->assertStatus(200);
        $result->assertSee($this->testTransaction->transaction_code);

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
        $result->assertSee(number_format($this->testTransaction->final_amount, 0, ',', '.'));
        $result->assertSee('Cetak Struk'); // Print button
    }

    public function testReceiptPageForNonExistentTransaction()
    {
        if (!$this->loggedInUser) {
            $this->markTestSkipped('User not set up correctly for test.');
        }
        $nonExistentId = 999999;
        $sessionData = [
            'user_id'    => $this->loggedInUser->id,
            'username'   => $this->loggedInUser->username,
            'name'       => $this->loggedInUser->name,
            'role'       => $this->loggedInUser->role,
            'isLoggedIn' => true,
        ];
        $result = $this->withSession($sessionData)
                       ->get('/transactions/' . $nonExistentId . '/receipt');

        $result->assertStatus(302); // Expecting a redirect
        $result->assertRedirectTo('/transactions');
        // Check session flash data for error message
        $result->assertSessionHas('error', 'Transaction not found.');
    }
}
