<?php

namespace Tests\Feature;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel; // Use Shield's UserModel
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

    protected ShieldUserModel $userModel; // Use Shield's UserModel
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
        $this->userModel = model(ShieldUserModel::class); // Use Shield's UserModel
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

        // Get a user to act as (AdminUserSeeder creates 'admin' user with email 'admin@example.com')
        $this->loggedInUser = $this->userModel->where('username', 'admin')->first();

        if (!$this->loggedInUser) {
             // If seeder failed or 'admin' user is not found by username, try by email.
            $this->loggedInUser = $this->userModel->findByCredentials(['email' => 'admin@example.com']);
        }

        if (!$this->loggedInUser) {
            // This fallback is less ideal now as AdminUserSeeder should be robust.
            // Creating a user here without adding to group/permissions might not work for all tests.
            // For ReceiptTest, it might be okay if it just needs a logged-in user.
            log_message('error', 'Admin user not found by username or email in ReceiptTest::setUp. Attempting to create a fallback user.');
            $tempUser = new \CodeIgniter\Shield\Entities\User([
                'username' => 'receipt_test_user' . random_int(1000,9999),
                'email'    => 'receipt_test_user' . random_int(1000,9999) . '@example.com',
                'password' => 'password123'
            ]);
            $this->userModel->save($tempUser);
            $this->loggedInUser = $this->userModel->findById($this->userModel->getInsertID());
        }
        $this->assertNotNull($this->loggedInUser, "Failed to get/create a user for ReceiptTest tests.");


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
        // Act as the logged-in user (Shield user object)
        // The withSession method is less critical if using actingAs, but this test uses it.
        // Ensure the session data structure is what AuthFilter expects if it checks more than just isLoggedIn.
        // For Shield, typically just `logged_in` with user ID is enough for `auth()->user()` to work.
        $result = $this->actingAs($this->loggedInUser)
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
        $result = $this->actingAs($this->loggedInUser)
                       ->get('/transactions/' . $nonExistentId . '/receipt');

        $result->assertStatus(302); // Expecting a redirect
        $result->assertRedirectTo('/transactions');
        // Check session flash data for error message
        $result->assertSessionHas('error', 'Transaction not found.');
    }
}
