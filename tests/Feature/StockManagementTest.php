<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\CategoryModel;

class StockManagementTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $seedOnce = false; // Seed for each test method for isolation
    // No global $seed property, call $this->seed() in setUp or specific tests

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure tables are clean before seeding
        $this->db->table('categories')->truncate();
        $this->db->table('products')->truncate();
        $this->db->table('users')->truncate();
        // Add other tables if they are affected or need cleaning

        $this->seed('UserSeeder'); // Creates an admin user typically
        $this->seed('CategorySeeder');
        $this->seed('ProductSeeder'); // Creates some products

        $userModel = new UserModel();
        $this->user = $userModel->where('role', 'admin')->first(); // Assuming admin can manage stock
        if (!$this->user) {
             $this->user = $userModel->first(); // Fallback if no admin role defined in seeder
        }
        if (!$this->user) {
             // If still no user, create one for the test
            $userModel->insert([
                'name' => 'Stock Test Admin',
                'username' => 'stockadmin',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'admin'
            ]);
            $this->user = $userModel->where('username', 'stockadmin')->first();
        }

        $productModel = new ProductModel();
        $this->product = $productModel->where('stock IS NOT NULL')->first(); // Get a product that has stock management

        if (!$this->product) {
            // Fallback: Create a product specifically for stock testing if ProductSeeder doesn't provide one
            $categoryModel = new CategoryModel();
            $cat = $categoryModel->first();
            if (!$cat) {
                $catId = $categoryModel->insert(['name' => 'Stock Test Category']);
                $cat = $categoryModel->find($catId);
            }

            $productData = [
                'name' => 'Stock Test Product',
                'code' => 'STCK001',
                'category_id' => $cat->id,
                'price' => 5000,
                'unit' => 'pcs',
                'stock' => 20, // Initial stock
            ];
            $productId = $productModel->insert($productData);
            $this->product = $productModel->find($productId);
        }
    }

    public function testAdminCanViewStockReport()
    {
        if (!$this->user) {
            $this->markTestSkipped('Admin user not available for testing stock report.');
        }

        $result = $this->actingAs($this->user)
                       ->get('/products/stock');

        $result->assertStatus(200);
        $result->assertSee('Laporan Stok Produk');
        if ($this->product) {
            $result->assertSee(esc($this->product->name));
            $result->assertSee('Sesuaikan'); // Button text for adjustment modal
        }
    }

    public function testAdjustStockAddQuantity()
    {
        if (!$this->user || !$this->product) {
            $this->markTestSkipped('Admin user or product not available for stock adjustment test.');
        }

        $initialStock = (int)$this->product->stock;
        $quantityToAdd = 5;
        $expectedStock = $initialStock + $quantityToAdd;

        $result = $this->actingAs($this->user)
                       ->post('/products/adjust-stock/' . $this->product->id, [
                           'adjustment_type' => 'add',
                           'quantity'        => $quantityToAdd,
                           'notes'           => 'Test add stock'
                       ]);

        $result->assertStatus(302); // Redirects back to stock report
        $result->assertRedirectTo('/products/stock');
        $result->assertSessionHas('message');

        // Verify stock in database
        $productModel = new ProductModel();
        $updatedProduct = $productModel->find($this->product->id);
        $this->assertEquals($expectedStock, $updatedProduct->stock);
    }

    public function testAdjustStockSubtractQuantity()
    {
        if (!$this->user || !$this->product || $this->product->stock < 5) {
            $this->markTestSkipped('Admin user or product with sufficient stock not available for stock subtraction test.');
        }

        $initialStock = (int)$this->product->stock;
        $quantityToSubtract = 3;
        $expectedStock = $initialStock - $quantityToSubtract;

        $result = $this->actingAs($this->user)
                       ->post('/products/adjust-stock/' . $this->product->id, [
                           'adjustment_type' => 'subtract',
                           'quantity'        => $quantityToSubtract,
                           'notes'           => 'Test subtract stock'
                       ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/products/stock');
        $result->assertSessionHas('message');

        $productModel = new ProductModel();
        $updatedProduct = $productModel->find($this->product->id);
        $this->assertEquals($expectedStock, $updatedProduct->stock);
    }

    public function testAdjustStockSetQuantity()
    {
        if (!$this->user || !$this->product) {
            $this->markTestSkipped('Admin user or product not available for stock set test.');
        }

        $newStockQuantity = 15;

        $result = $this->actingAs($this->user)
                       ->post('/products/adjust-stock/' . $this->product->id, [
                           'adjustment_type' => 'set',
                           'quantity'        => $newStockQuantity,
                           'notes'           => 'Test set stock'
                       ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/products/stock');
        $result->assertSessionHas('message');

        $productModel = new ProductModel();
        $updatedProduct = $productModel->find($this->product->id);
        $this->assertEquals($newStockQuantity, $updatedProduct->stock);
    }

    public function testAdjustStockCannotBeNegative()
    {
        if (!$this->user || !$this->product) {
            $this->markTestSkipped('Admin user or product not available for negative stock test.');
        }

        $initialStock = (int)$this->product->stock;
        $quantityToSubtract = $initialStock + 5; // Attempt to make stock negative

        $result = $this->actingAs($this->user)
                       ->post('/products/adjust-stock/' . $this->product->id, [
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
        $updatedProduct = $productModel->find($this->product->id);
        $this->assertEquals($initialStock, $updatedProduct->stock);
    }

    public function testAdjustStockValidationFail()
    {
        if (!$this->user || !$this->product) {
            $this->markTestSkipped('Admin user or product not available for validation failure test.');
        }

        $result = $this->actingAs($this->user)
                       ->post('/products/adjust-stock/' . $this->product->id, [
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
