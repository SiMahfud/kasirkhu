<?php

namespace Tests\Feature;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel; // Use Shield's UserModel
use App\Models\ProductModel;
use App\Models\CategoryModel;
use Tests\Support\Database\BaseFeatureTestCase;

class StockManagementTest extends BaseFeatureTestCase
{
    // Traits, $namespace, $DBGroup, $baseURL, migration handling inherited.

    protected ShieldUserModel $userModel; // Use Shield's UserModel
    protected ProductModel $productModel;
    protected CategoryModel $categoryModel;

    protected $loggedInUser; // Changed from $user
    protected $testProduct;  // Changed from $product

    protected function setUp(): void
    {
        parent::setUp(); // Handles migrations

        // Initialize models
        $this->userModel = model(ShieldUserModel::class); // Use Shield's UserModel
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();

        // Seed necessary data
        $this->seed('AdminUserSeeder'); // Corrected from UserSeeder
        $this->seed('CategorySeeder'); // App\Database\Seeds\CategorySeeder
        $this->seed('ProductSeeder');  // App\Database\Seeds\ProductSeeder

        $this->loggedInUser = $this->userModel->findByCredentials(['email' => 'admin@example.com']);
        if (!$this->loggedInUser) {
            $this->loggedInUser = $this->userModel->where('username', 'admin')->first(); // Fallback to username
        }

        if (!$this->loggedInUser) {
            log_message('error', 'Admin user not found by username or email in StockManagementTest::setUp. Attempting to create a fallback user.');
            $tempUser = new \CodeIgniter\Shield\Entities\User([
                'username' => 'stock_test_admin' . random_int(1000,9999),
                'email'    => 'stock_test_admin' . random_int(1000,9999) . '@example.com',
                'password' => 'password123'
            ]);
            $this->userModel->save($tempUser);
            $this->loggedInUser = $this->userModel->findById($this->userModel->getInsertID());
            if($this->loggedInUser){
                 $this->loggedInUser->addGroup('admin'); // Add to admin group
            }
        }
        $this->assertNotNull($this->loggedInUser, "Failed to get/create an admin user for stock tests.");


        $this->testProduct = $this->productModel->where('stock IS NOT NULL')->orderBy('id', 'RANDOM')->get()->getRow();
        if (!$this->testProduct) {
            $category = $this->categoryModel->first() ?? $this->categoryModel->find($this->categoryModel->insert(['name' => 'Stock Test Category']));
            $productId = $this->productModel->insert([
                'name' => 'Stock Test Product', 'code' => 'STCK001',
                'category_id' => $category->id, 'price' => 5000,
                'unit' => 'pcs', 'stock' => 20,
            ]);
            $this->testProduct = $this->productModel->find($productId);
        }
        $this->assertNotNull($this->testProduct, "Failed to get/create a product for stock tests.");
    }

    public function testAdminCanViewStockReport()
    {
        if (!$this->loggedInUser) {
            $this->markTestSkipped('Admin user not available for testing stock report.');
        }
        $result = $this->actingAs($this->loggedInUser)
                       ->get('/products/stock');

        $result->assertStatus(200);
        $result->assertSee('Laporan Stok Produk');
        if ($this->testProduct) {
            $result->assertSee(esc($this->testProduct->name));
            $result->assertSee('Sesuaikan'); // Button text for adjustment modal
        }
    }

    public function testAdjustStockAddQuantity()
    {
        if (!$this->loggedInUser || !$this->testProduct) {
            $this->markTestSkipped('Admin user or product not available for stock adjustment test.');
        }

        $initialStock = (int)$this->testProduct->stock;
        $quantityToAdd = 5;
        $expectedStock = $initialStock + $quantityToAdd;

        $result = $this->actingAs($this->loggedInUser)
                       ->post('/products/adjust-stock/' . $this->testProduct->id, [
                           'adjustment_type' => 'add',
                           'quantity'        => $quantityToAdd,
                           'notes'           => 'Test add stock'
                       ]);

        $result->assertStatus(302); // Redirects back to stock report
        $result->assertRedirectTo('/products/stock');
        $result->assertSessionHas('message');

        // Verify stock in database
        $productModel = new ProductModel();
        $updatedProduct = $productModel->find($this->testProduct->id);
        $this->assertEquals($expectedStock, $updatedProduct->stock);
    }

    public function testAdjustStockSubtractQuantity()
    {
        if (!$this->loggedInUser || !$this->testProduct || $this->testProduct->stock < 5) {
            $this->markTestSkipped('Admin user or product with sufficient stock not available for stock subtraction test.');
        }

        $initialStock = (int)$this->testProduct->stock;
        $quantityToSubtract = 3;
        $expectedStock = $initialStock - $quantityToSubtract;

        $result = $this->actingAs($this->loggedInUser)
                       ->post('/products/adjust-stock/' . $this->testProduct->id, [
                           'adjustment_type' => 'subtract',
                           'quantity'        => $quantityToSubtract,
                           'notes'           => 'Test subtract stock'
                       ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/products/stock');
        $result->assertSessionHas('message');

        $productModel = new ProductModel();
        $updatedProduct = $productModel->find($this->testProduct->id);
        $this->assertEquals($expectedStock, $updatedProduct->stock);
    }

    public function testAdjustStockSetQuantity()
    {
        if (!$this->loggedInUser || !$this->testProduct) {
            $this->markTestSkipped('Admin user or product not available for stock set test.');
        }

        $newStockQuantity = 15;
        $result = $this->actingAs($this->loggedInUser)
                       ->post('/products/adjust-stock/' . $this->testProduct->id, [
                           'adjustment_type' => 'set',
                           'quantity'        => $newStockQuantity,
                           'notes'           => 'Test set stock'
                       ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/products/stock');
        $result->assertSessionHas('message');

        $productModel = new ProductModel();
        $updatedProduct = $productModel->find($this->testProduct->id);
        $this->assertEquals($newStockQuantity, $updatedProduct->stock);
    }

    public function testAdjustStockCannotBeNegative()
    {
        if (!$this->loggedInUser || !$this->testProduct) {
            $this->markTestSkipped('Admin user or product not available for negative stock test.');
        }

        $initialStock = (int)$this->testProduct->stock;
        $quantityToSubtract = $initialStock + 5; // Attempt to make stock negative

        $result = $this->actingAs($this->loggedInUser)
                       ->post('/products/adjust-stock/' . $this->testProduct->id, [
                           'adjustment_type' => 'subtract',
                           'quantity'        => $quantityToSubtract
                       ]);

        // Should redirect back (or stay on page with error)
        // Depending on implementation, it might be a redirect back or render with error.
        // The current controller redirects back with an error.
        $result->assertStatus(302);
        $result->assertSessionHas('error', 'Stok tidak boleh menjadi negatif.');

        // Stock should remain unchanged
        $productModel = new ProductModel();
        $updatedProduct = $productModel->find($this->testProduct->id);
        $this->assertEquals($initialStock, $updatedProduct->stock);
    }

    public function testAdjustStockValidationFail()
    {
        if (!$this->loggedInUser || !$this->testProduct) {
            $this->markTestSkipped('Admin user or product not available for validation failure test.');
        }
        $result = $this->actingAs($this->loggedInUser)
                       ->post('/products/adjust-stock/' . $this->testProduct->id, [
                           'adjustment_type' => 'invalid_type', // Invalid type
                           'quantity'        => 'abc' // Invalid quantity
                       ]);

        $result->assertStatus(302); // Redirects back on validation failure
        $result->assertSessionHas('error'); // Check for general error message or specific validation errors

        // Example check for specific error messages if your controller passes them detailed:
        // $errors = session('error'); // Assuming error is a string with listed errors
        // $this->assertStringContainsString('Jenis penyesuaian tidak valid.', $errors);
        // $this->assertStringContainsString('Kuantitas harus berupa angka.', $errors);
    }
}
