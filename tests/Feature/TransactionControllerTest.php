<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use App\Entities\User;

class TransactionControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    // Note: $migrate must be true if using DatabaseTestTrait and expecting schema to be set up.
    // It defaults to true in CIUnitTestCase if DatabaseTestTrait is used.
    // protected $migrateOnce = true; // Ensure migrations run only once per test class for speed.
    // protected $refresh = true; // Alternative to $migrate, runs migrations before each test.

    protected $namespace   = 'App'; // Important for locating migrations and seeds

    protected ?User $loggedInUser = null; // Renamed for clarity
    protected CategoryModel $categoryModel;
    protected ProductModel $productModel;
    protected TransactionModel $transactionModel;
    protected TransactionDetailModel $transactionDetailModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure correct environment for testing
        $_ENV['CI_ENVIRONMENT'] = 'testing';
        putenv('CI_ENVIRONMENT=testing');

        // Initialize models (DatabaseTestTrait should handle DB connection for 'tests' group)
        $this->categoryModel = new CategoryModel();
        $this->productModel = new ProductModel();
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();

        // Create and login a user
        $userModel = new UserModel();
        $this->loggedInUser = new User([
            'name'     => 'Test Cashier',
            'username' => 'testcashier' . random_int(1000,9999), // Ensure unique username for reruns
            'password' => 'password123',
            'role'     => 'cashier',
        ]);
        // The UserModel's beforeInsert callback should hash the password.
        $userId = $userModel->insert($this->loggedInUser);
        $this->loggedInUser->id = $userId;

        // $this->actingAs($this->loggedInUser); // Commenting out due to persistent issues

        // Ensure session data is prepared
        $this->loggedInUserSessionData = [
            'user_id'    => $this->loggedInUser->id,
            'username'   => $this->loggedInUser->username,
            'name'       => $this->loggedInUser->name,
            'role'       => $this->loggedInUser->role,
            'isLoggedIn' => true,
        ];

        // Seed necessary data
        $this->seedTestData();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // DatabaseTestTrait handles cleaning up the database if $refresh = true or $migrate = true (default)
    }

    protected function seedTestData()
    {
        // Ensure category table is clean or use specific names
        $this->categoryModel->purgeDeleted(); // Clean up soft deleted if any
        $this->categoryModel->where('name', 'ATK Test Category')->delete();

        $categoryData = ['name' => 'ATK Test Category', 'description' => 'Test category for ATK products'];
        $categoryId = $this->categoryModel->insert($categoryData);
        $this->assertTrue($categoryId !== false && $categoryId > 0, "Failed to seed category. Errors: " . implode(', ', $this->categoryModel->errors()));


        // Clean up previous test products if necessary, or use unique codes
        $this->productModel->where('code', 'PENTEST01')->delete();
        $this->productModel->where('code', 'BOOKTEST02')->delete();
        $this->productModel->where('code', 'SRVTEST03')->delete();
        $this->productModel->purgeDeleted();


        $productsToSeed = [
            [
                'category_id' => $categoryId, 'code' => 'PENTEST01', 'name' => 'Pena Uji Coba',
                'price' => 5000, 'unit' => 'pcs', 'stock' => 100, 'description' => 'Pena untuk menulis data uji.',
            ],
            [
                'category_id' => $categoryId, 'code' => 'BOOKTEST02', 'name' => 'Buku Catatan Uji',
                'price' => 15000, 'unit' => 'pcs', 'stock' => 50, 'description' => 'Buku untuk mencatat hasil uji.',
            ],
            [
                'category_id' => $categoryId, 'code' => 'SRVTEST03', 'name' => 'Jasa Fotokopi Uji',
                'price' => 500, 'unit' => 'lembar', 'stock' => 0, 'description' => 'Jasa fotokopi. (Stock set to 0 for test)',
            ],
        ];

        foreach($productsToSeed as $productData) {
            $inserted = $this->productModel->insert($productData);
            $this->assertTrue($inserted !== false, "Failed to seed product {$productData['code']}. Errors: " . implode(', ', $this->productModel->errors()));
        }
    }

    public function testCanAccessNewTransactionPage()
    {
        $result = $this->withSession($this->loggedInUserSessionData)
                         ->get('/transactions/new');
        $result->assertOK(); // Alias for assertStatus(200)
        $result->assertSee('Buat Transaksi Baru');
        $result->assertSee('Pena Uji Coba');
    }

    public function testCreateTransactionSuccess()
    {
        $product1 = $this->productModel->where('code', 'PENTEST01')->first();
        $product2 = $this->productModel->where('code', 'BOOKTEST02')->first();
        $this->assertNotNull($product1, "Test product PENTEST01 not found.");
        $this->assertNotNull($product2, "Test product BOOKTEST02 not found.");

        $initialStock1 = $product1->stock;
        $initialStock2 = $product2->stock;

        $data = [
            'customer_name' => 'Pelanggan Sukses Uji',
            'payment_method' => 'cash',
            'discount' => 1000,
            'products' => [
                ['id' => $product1->id, 'quantity' => 2],
                ['id' => $product2->id, 'quantity' => 1],
            ],
        ];

        $result = $this->withSession($this->loggedInUserSessionData)
                         ->post('/transactions/create', $data);

        // Check for successful redirect and session message
        $result->assertRedirect();
        $result->assertSessionHas('message');
        $this->assertTrue(str_contains(session('message'), 'Transaction created successfully!'));

        // Verify transaction in database
        $transaction = $this->transactionModel
            ->where('customer_name', 'Pelanggan Sukses Uji')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($transaction, 'Transaction was not created or could not be found.');
        $this->assertEquals(25000.00, $transaction->total_amount);
        $this->assertEquals(1000.00, $transaction->discount);
        $this->assertEquals(24000.00, $transaction->final_amount);
        $this->assertEquals($this->loggedInUser->id, $transaction->user_id);

        // Verify transaction details
        $this->seeInDatabase('transaction_details', [
            'transaction_id' => $transaction->id, 'product_id' => $product1->id, 'quantity' => 2
        ]);
        $this->seeInDatabase('transaction_details', [
            'transaction_id' => $transaction->id, 'product_id' => $product2->id, 'quantity' => 1
        ]);

        // Verify stock reduction
        $this->seeInDatabase('products', ['id' => $product1->id, 'stock' => $initialStock1 - 2]);
        $this->seeInDatabase('products', ['id' => $product2->id, 'stock' => $initialStock2 - 1]);

        // Follow redirect to show page (optional, but good to check)
        $showResult = $this->get($result->getRedirectUrl());
        $showResult->assertOK();
        $showResult->assertSee($transaction->transaction_code);
    }

    public function testCreateTransactionFailNoProducts()
    {
        $data = ['customer_name' => 'Pelanggan Gagal Produk', 'payment_method' => 'cash', 'products' => []];
        $result = $this->withSession($this->loggedInUserSessionData)
                         ->post('/transactions/create', $data);
        $result->assertRedirect();
        $result->assertSessionHas('error'); // Checks if the key 'error' exists

        $sessionErrors = session('error'); // Use global session helper
        $this->assertIsArray($sessionErrors, "Session 'error' should be an array of validation errors for no products.");
        $this->assertArrayHasKey('products', $sessionErrors, "Validation errors for no products should contain 'products' key.");

        $this->dontSeeInDatabase('transactions', ['customer_name' => 'Pelanggan Gagal Produk']);
    }

    public function testCreateTransactionFailInsufficientStock()
    {
        $product1 = $this->productModel->where('code', 'PENTEST01')->first();
        $this->productModel->update($product1->id, ['stock' => 1]); // Set stock to 1

        $data = [
            'customer_name' => 'Pelanggan Stok Kurang Uji',
            'products' => [['id' => $product1->id, 'quantity' => 2]], // Request 2
        ];

        $result = $this->withSession($this->loggedInUserSessionData)
                         ->post('/transactions/create', $data);
        $result->assertRedirect();
        $result->assertSessionHas('error');

        $sessionErrorString = session('error'); // Use global session helper
        $this->assertIsString($sessionErrorString, "Session 'error' should be a string for insufficient stock exception.");
        $this->assertTrue(str_contains($sessionErrorString, 'Insufficient stock'), "Error message should mention insufficient stock. Got: " . $sessionErrorString);

        $this->dontSeeInDatabase('transactions', ['customer_name' => 'Pelanggan Stok Kurang Uji']);
        $this->seeInDatabase('products', ['id' => $product1->id, 'stock' => 1]); // Stock should remain 1
    }

    public function testCanAccessTransactionIndexPage()
    {
        // Seed a transaction to ensure the list isn't empty
        $this->transactionModel->insert([
            'user_id' => $this->loggedInUser->id, 'customer_name' => 'Index Test Customer',
            'total_amount' => 100.0, 'final_amount' => 100.0, 'payment_method' => 'cash'
        ]);

        $result = $this->withSession($this->loggedInUserSessionData)
                         ->get('/transactions');
        $result->assertOK();
        $result->assertSee('Riwayat Transaksi');
        $result->assertSee('Index Test Customer');
    }

    public function testCanAccessTransactionShowPage()
    {
        $product = $this->productModel->where('code', 'PENTEST01')->first();
        $transactionData = [
            'user_id' => $this->loggedInUser->id, 'customer_name' => 'Show Test Customer',
            'total_amount' => (float)$product->price, 'final_amount' => (float)$product->price, 'payment_method' => 'qris'
        ];
        $transactionId = $this->transactionModel->insert($transactionData);
        $this->assertTrue($transactionId !== false, "Failed to insert transaction for show page test. Errors: " . implode(', ', $this->transactionModel->errors()));
        $transaction = $this->transactionModel->find($transactionId);

        $this->transactionDetailModel->insert([
            'transaction_id' => $transactionId, 'product_id' => $product->id,
            'quantity' => 1, 'price_per_unit' => $product->price, 'subtotal' => $product->price
        ]);

        $result = $this->withSession($this->loggedInUserSessionData)
                         ->get('/transactions/' . $transactionId);
        $result->assertOK();
        $result->assertSee('Detail Transaksi');
        $result->assertSee($transaction->transaction_code);
        $result->assertSee('Show Test Customer');
        $result->assertSee($product->name);
    }

    public function testDeleteTransactionSoftDeletes()
    {
        $transactionId = $this->transactionModel->insert([
            'user_id' => $this->loggedInUser->id, 'customer_name' => 'Delete Test Customer',
            'total_amount' => 100.0, 'final_amount' => 100.0
        ]);
        $this->assertTrue($transactionId !== false, "Failed to insert transaction for delete test. Errors: " . implode(', ', $this->transactionModel->errors()));

        $result = $this->withSession($this->loggedInUserSessionData)
                         ->post('/transactions/delete/' . $transactionId);
        $result->assertRedirectTo('/transactions');
        $result->assertSessionHas('message', 'Transaction soft deleted successfully.');

        $this->assertNull($this->transactionModel->find($transactionId)); // Should not be found by normal find
        $this->assertNotNull($this->transactionModel->onlyDeleted()->find($transactionId)); // Should be found with onlyDeleted
    }
}
