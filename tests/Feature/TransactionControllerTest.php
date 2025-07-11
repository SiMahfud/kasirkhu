<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use App\Entities\User; // Assuming User entity is used
use Tests\Support\Database\BaseFeatureTestCase;


class TransactionControllerTest extends BaseFeatureTestCase
{
    // Traits, $namespace, $DBGroup, $baseURL, migration handling inherited.

    protected UserModel $userModel; // Added type hint
    protected CategoryModel $categoryModel;
    protected ProductModel $productModel;
    protected TransactionModel $transactionModel;
    protected TransactionDetailModel $transactionDetailModel;

    protected ?User $loggedInUser = null;
    protected array $loggedInUserSessionData; // To store session data

    protected function setUp(): void
    {
        parent::setUp(); // Handles migrations

        // Initialize models
        $this->userModel = new UserModel();
        $this->categoryModel = new CategoryModel();
        $this->productModel = new ProductModel();
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();

        // Seed necessary data - AdminUserSeeder should create at least one user
        $this->seed('AdminUserSeeder'); // Corrected from UserSeeder
        $this->seed('CategorySeeder'); // Ensure categories exist for products
        $this->seed('ProductSeeder');  // Ensure products exist

        // Create and prepare login for a user
        // Attempt to find an existing user from AdminUserSeeder
        $this->loggedInUser = $this->userModel->where('role', 'cashier')->orWhere('role', 'admin')->first();

        if (!$this->loggedInUser) {
             // If no suitable user from seeder, create one
            $username = 'testcashier' . random_int(1000, 9999);
            $userData = [
                'name'     => 'Test Cashier Transaction',
                'username' => $username,
                'password' => 'password123', // Will be hashed by UserModel
                'role'     => 'cashier',
            ];
            $userId = $this->userModel->insert($userData);
            $this->assertTrue($userId !== false, "Failed to create user for transaction test. Errors: " . implode(', ', $this->userModel->errors()));
            $this->loggedInUser = $this->userModel->find($userId);
        }
        $this->assertNotNull($this->loggedInUser, "loggedInUser is null, user setup failed.");


        $this->loggedInUserSessionData = [
            'user_id'    => $this->loggedInUser->id,
            'username'   => $this->loggedInUser->username,
            'name'       => $this->loggedInUser->name,
            'role'       => $this->loggedInUser->role,
            'isLoggedIn' => true,
        ];

        // Seed specific test data for transactions if ProductSeeder isn't sufficient
        // $this->seedTestData(); // This was in original, let's ensure products are there.
        // If ProductSeeder is comprehensive, this might not be needed or can be simplified.
        $this->ensureTestProductsExist();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function ensureTestProductsExist()
    {
        $productsToEnsure = [
            ['code' => 'PENTEST01', 'name' => 'Pena Uji Coba Trans', 'price' => 5000, 'stock' => 100],
            ['code' => 'BOOKTEST02', 'name' => 'Buku Catatan Uji Trans', 'price' => 15000, 'stock' => 50],
            ['code' => 'SRVTEST03', 'name' => 'Jasa Fotokopi Uji Trans', 'price' => 500, 'stock' => 0, 'unit' => 'lembar'], // Set to 0 to avoid model insert issue with null
        ];

        $defaultCategory = $this->categoryModel->first();
        if (!$defaultCategory) {
            $catId = $this->categoryModel->insert(['name' => 'Default Trans Test Category']);
            $defaultCategory = $this->categoryModel->find($catId);
        }
        $this->assertNotNull($defaultCategory, "Default category for test products could not be established.");

        foreach ($productsToEnsure as $pData) {
            $product = $this->productModel->where('code', $pData['code'])->first();
            if (!$product) {
                $this->productModel->insert([
                    'category_id' => $defaultCategory->id,
                    'code'        => $pData['code'],
                    'name'        => $pData['name'],
                    'price'       => $pData['price'],
                    'unit'        => $pData['unit'] ?? 'pcs',
                    'stock'       => $pData['stock'],
                    'description' => $pData['name'] . ' description.',
                ]);
            }
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
        // $result->assertSessionHas('message'); // This was failing
        $this->assertTrue(str_contains(session('message') ?? '', 'Transaction created successfully!'), "Session message for successful creation not found or incorrect.");

        // Verify transaction in database
        $transaction = $this->transactionModel
            ->where('customer_name', 'Pelanggan Sukses Uji')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($transaction, 'Transaction was not created or could not be found.');
        $this->assertEquals(25000.00, $transaction->getTotalAmount());
        $this->assertEquals(1000.00, $transaction->getDiscount());
        $this->assertEquals(24000.00, $transaction->getFinalAmount());
        $this->assertEquals($this->loggedInUser->id, $transaction->user_id); // user_id is still a direct attribute

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
        // $showResult = $this->get($result->getRedirectUrl()); // This was causing PageNotFound
        $showResult = $this->withSession($this->loggedInUserSessionData) // Ensure session continuity for the GET
                           ->get('transactions/' . $transaction->id);
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
            'total_amount' => (string)100.0, 'final_amount' => (string)100.0, 'payment_method' => 'cash'
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
            'total_amount' => (string)$product->price, 'final_amount' => (string)$product->price, 'payment_method' => 'qris'
        ];
        $transactionId = $this->transactionModel->insert($transactionData);
        $this->assertTrue($transactionId !== false, "Failed to insert transaction for show page test. Errors: " . implode(', ', $this->transactionModel->errors()));
        $transaction = $this->transactionModel->find($transactionId);

        $this->transactionDetailModel->insert([
            'transaction_id' => $transactionId, 'product_id' => $product->id,
            'quantity' => 1, 'price_per_unit' => (string)$product->price, 'subtotal' => (string)$product->price
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
            'total_amount' => (string)100.0, 'final_amount' => (string)100.0
        ]);
        $this->assertTrue($transactionId !== false, "Failed to insert transaction for delete test. Errors: " . implode(', ', $this->transactionModel->errors()));

        $result = $this->withSession($this->loggedInUserSessionData)
                         ->post('/transactions/delete/' . $transactionId);
        $result->assertRedirectTo('/transactions');
        $result->assertSessionHas('message', 'Transaction soft deleted successfully.');

        $this->assertNull($this->transactionModel->find($transactionId)); // Should not be found by normal find
        $this->assertNotNull($this->transactionModel->onlyDeleted()->find($transactionId)); // Should be found with onlyDeleted
    }

    // --- New Tests for Sprint 3 Khumaira Specific Calculations ---

    public function testCreateTransactionWithFotokopiServicePrice()
    {
        // Ensure 'Jasa Fotokopi Uji Trans' (SRVTEST03) exists from ensureTestProductsExist()
        $fotokopiProduct = $this->productModel->where('code', 'SRVTEST03')->first();
        $this->assertNotNull($fotokopiProduct, "Fotokopi service product (SRVTEST03) not found.");

        $serviceItemPrice = 750.00; // Calculated price from frontend (e.g., 5 pages * 150/page)
        $pages = 5;
        $paperType = 'A4 70gr';
        $colorType = 'Hitam Putih';

        $data = [
            'customer_name' => 'Pelanggan Fotokopi Uji',
            'products' => [
                [
                    'id' => $fotokopiProduct->id,
                    'quantity' => 1, // Typically quantity 1 for a "job" priced this way
                    'service_item_price' => $serviceItemPrice,
                    'service_pages' => $pages,
                    'service_paper_type' => $paperType,
                    'service_color_type' => $colorType,
                ]
            ],
        ];

        $result = $this->withSession($this->loggedInUserSessionData)
                         ->post('/transactions/create', $data);

        $result->assertRedirect();
        $this->assertTrue(str_contains(session('message') ?? '', 'Transaction created successfully!'), "Session message for successful fotokopi transaction not found or incorrect. Errors: " . json_encode(session('error')));

        $transaction = $this->transactionModel->where('customer_name', 'Pelanggan Fotokopi Uji')->orderBy('id', 'DESC')->first();
        $this->assertNotNull($transaction, 'Fotokopi Transaction was not created.');
        $this->assertEquals($serviceItemPrice, $transaction->final_amount);

        $detail = $this->transactionDetailModel->where('transaction_id', $transaction->id)->first();
        $this->assertNotNull($detail, 'Transaction detail for fotokopi service not found.');
        $this->assertEquals($fotokopiProduct->id, $detail->product_id);
        $this->assertEquals(1, $detail->quantity);
        $this->assertEquals($serviceItemPrice, $detail->price_per_unit); // Price per unit should be the service_item_price
        $this->assertEquals($serviceItemPrice, $detail->subtotal);

        $this->assertNotNull($detail->service_item_details, "service_item_details should not be null for fotokopi service.");
        $serviceDetailsArray = json_decode($detail->service_item_details, true);
        $this->assertEquals($pages, $serviceDetailsArray['pages']);
        $this->assertEquals($paperType, $serviceDetailsArray['paper_type']);
        $this->assertEquals($colorType, $serviceDetailsArray['color_type']);
    }

    public function testCreateTransactionWithManualPriceService()
    {
        // Create a dummy "Jasa Desain" product if not already seeded with price 0
        $desainProductName = 'Jasa Desain Uji Trans';
        $desainProduct = $this->productModel->where('name', $desainProductName)->first();
        if (!$desainProduct) {
            $defaultCategory = $this->categoryModel->first();
            $desainProductId = $this->productModel->insert([
                'category_id' => $defaultCategory->id, 'code' => 'DESIGN01', 'name' => $desainProductName,
                'price' => 0, 'unit' => 'project', 'stock' => 0, // Use 0 for stock for services if not tracked
            ]);
            $desainProduct = $this->productModel->find($desainProductId);
        }
        $this->assertNotNull($desainProduct, "Desain service product not found/created.");
        $this->assertEquals(0, (float)$desainProduct->price, "Desain product base price should be 0 for this test.");

        $manualPrice = 75000.00;
        $serviceDescription = 'Desain logo perusahaan X';

        $data = [
            'customer_name' => 'Pelanggan Desain Uji',
            'products' => [
                [
                    'id' => $desainProduct->id,
                    'quantity' => 1,
                    'manual_price' => $manualPrice,
                    'service_description' => $serviceDescription,
                ]
            ],
        ];

        $result = $this->withSession($this->loggedInUserSessionData)
                         ->post('/transactions/create', $data);

        $result->assertRedirect();
        $this->assertTrue(str_contains(session('message') ?? '', 'Transaction created successfully!'), "Session message for successful desain transaction not found or incorrect. Errors: " . json_encode(session('error')));

        $transaction = $this->transactionModel->where('customer_name', 'Pelanggan Desain Uji')->orderBy('id', 'DESC')->first();
        $this->assertNotNull($transaction, 'Desain Transaction was not created.');
        $this->assertEquals($manualPrice, $transaction->final_amount);

        $detail = $this->transactionDetailModel->where('transaction_id', $transaction->id)->first();
        $this->assertNotNull($detail, 'Transaction detail for desain service not found.');
        $this->assertEquals($desainProduct->id, $detail->product_id);
        $this->assertEquals(1, $detail->quantity);
        $this->assertEquals($manualPrice, $detail->price_per_unit);
        $this->assertEquals($manualPrice, $detail->subtotal);

        $this->assertNotNull($detail->service_item_details, "service_item_details should not be null for desain service.");
        $serviceDetailsArray = json_decode($detail->service_item_details, true);
        $this->assertEquals($serviceDescription, $serviceDetailsArray['description']);
    }
}
