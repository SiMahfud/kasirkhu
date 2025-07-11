<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use Tests\Support\Database\BaseFeatureTestCase;
use CodeIgniter\Test\FeatureTestTrait; // Explicitly add here for testing

class ReportFeatureTest extends BaseFeatureTestCase
{
    use FeatureTestTrait; // Explicitly use here for testing
    // Traits, $namespace, $DBGroup, $baseURL, migration handling inherited.

    protected UserModel $userModel;
    protected ProductModel $productModel;
    protected CategoryModel $categoryModel;
    protected TransactionModel $transactionModel;
    protected TransactionDetailModel $transactionDetailModel;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp(); // Handles migrations via BaseFeatureTestCase

        // Initialize models
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();

        // Seed necessary data after migrations
        $this->seed('AdminUserSeeder'); // Corrected from UserSeeder
        $this->seed('CategorySeeder');
        $this->seed('ProductSeeder');
        // SettingSeeder might not be directly relevant for reports but good for consistency
        $this->seed('SettingSeeder');

        $this->adminUser = $this->userModel->where('role', 'admin')->get()->getRow();
        if (!$this->adminUser) {
            $this->adminUser = $this->userModel->first(); // Fallback
        }
        if (!$this->adminUser) {
            $userId = $this->userModel->insert([
                'name' => 'Report Test Admin', 'username' => 'reportadmin' . random_int(1000,9999),
                'password' => password_hash('password123', PASSWORD_DEFAULT), 'role' => 'admin'
            ]);
            $this->adminUser = $this->userModel->find($userId);
        }
        $this->assertNotNull($this->adminUser, "Failed to get/create an admin user for report tests.");

        // Create some sample transactions for testing reports
        $this->createSampleTransactions();
    }

    protected function createSampleTransactions()
    {
        $cashier = $this->adminUser; // Use admin as cashier for simplicity

        $products = $this->productModel->limit(2)->findAll(); // Use findAll to get array of objects
        if (count($products) < 2) {
            $category = $this->categoryModel->first() ?? $this->categoryModel->find($this->categoryModel->insert(['name' => 'Report Cat']));
            $this->productModel->insert(['name'=>'Prod A Report','category_id'=> $category->id,'price'=>10000,'stock'=>10]);
            $this->productModel->insert(['name'=>'Prod B Report','category_id'=> $category->id,'price'=>20000,'stock'=>10]);
            $products = $this->productModel->limit(2)->findAll();
        }
        $this->assertCount(2, $products, "Need at least 2 products to create sample transactions.");

        $p1 = $products[0];
        $p2 = $products[1];

        // Transaction 1 (Today)
        $t1Data = ['user_id' => $cashier->id, 'total_amount' => ($p1->price * 2), 'final_amount' => ($p1->price * 2), 'created_at' => date('Y-m-d H:i:s')];
        $t1Id = $this->transactionModel->insert($t1Data);
        $this->transactionDetailModel->insert(['transaction_id' => $t1Id, 'product_id' => $p1->id, 'quantity' => 2, 'price_per_unit' => $p1->price, 'subtotal' => $p1->price * 2]);

        // Transaction 2 (Today, different product)
        $t2Data = ['user_id' => $cashier->id, 'total_amount' => ($p2->price * 1), 'final_amount' => ($p2->price * 1), 'created_at' => date('Y-m-d H:i:s')];
        $t2Id = $this->transactionModel->insert($t2Data);
        $this->transactionDetailModel->insert(['transaction_id' => $t2Id, 'product_id' => $p2->id, 'quantity' => 1, 'price_per_unit' => $p2->price, 'subtotal' => $p2->price * 1]);

        // Transaction 3 (Yesterday)
        $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
        $t3Data = ['user_id' => $cashier->id, 'total_amount' => ($p1->price * 3), 'final_amount' => ($p1->price * 3), 'created_at' => $yesterday];
        $t3Id = $this->transactionModel->insert($t3Data);
        $this->transactionDetailModel->insert(['transaction_id' => $t3Id, 'product_id' => $p1->id, 'quantity' => 3, 'price_per_unit' => $p1->price, 'subtotal' => $p1->price * 3]);
    }

    public function testAccessDailySalesReportDefaultDate()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $sessionData = [
            'user_id'    => $this->adminUser->id,
            'username'   => $this->adminUser->username,
            'name'       => $this->adminUser->name,
            'role'       => $this->adminUser->role,
            'isLoggedIn' => true,
        ];
        $result = $this->withSession($sessionData)->get('/reports/sales/daily');
        $result->assertStatus(200);
        $result->assertSee('Laporan Penjualan Harian');

        // Calculate expected total based on actual seeded/created products in createSampleTransactions
        $seededProducts = $this->productModel->limit(2)->findAll();
        $this->assertCount(2, $seededProducts, "Need at least 2 seeded products for default daily sales report assertion.");
        $p1Today = $seededProducts[0];
        $p2Today = $seededProducts[1];
        $expectedTotalToday = ($p1Today->price * 2) + ($p2Today->price * 1); // Based on createSampleTransactions logic
        $result->assertSee(number_format($expectedTotalToday, 0, ',', '.'));
        $result->assertSee('Total Transaksi: 2'); // For today
    }

    public function testAccessDailySalesReportWithDateFilter()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $sessionData = [
            'user_id'    => $this->adminUser->id,
            'username'   => $this->adminUser->username,
            'name'       => $this->adminUser->name,
            'role'       => $this->adminUser->role,
            'isLoggedIn' => true,
        ];
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $result = $this->withSession($sessionData)->get('/reports/sales/daily?from_date=' . $yesterday . '&to_date=' . $yesterday);

        $result->assertStatus(200);
        $result->assertSee('Laporan Penjualan Harian');

        // Calculate expected total based on actual seeded/created products in createSampleTransactions for yesterday
        $seededProducts = $this->productModel->limit(1)->findAll(); // Assuming p1 is used for yesterday's transaction
        $this->assertGreaterThanOrEqual(1, count($seededProducts), "Need at least 1 seeded product for yesterday's daily sales report assertion.");
        $p1Yesterday = $seededProducts[0];
        $expectedTotalYesterday = ($p1Yesterday->price * 3); // Based on createSampleTransactions logic
        $result->assertSee(number_format($expectedTotalYesterday, 0, ',', '.'));
        $result->assertSee('Total Transaksi: 1'); // For yesterday
    }

    public function testAccessDailySalesReportInvalidDateRange()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $sessionData = [
            'user_id'    => $this->adminUser->id,
            'username'   => $this->adminUser->username,
            'name'       => $this->adminUser->name,
            'role'       => $this->adminUser->role,
            'isLoggedIn' => true,
        ];
        // from_date after to_date
        $result = $this->withSession($sessionData)->get('/reports/sales/daily?from_date=' . $today . '&to_date=' . $yesterday);

        $result->assertStatus(302); // Should redirect
        $result->assertSessionHas('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
    }


    public function testAccessTopProductsReportDefaultDate()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $sessionData = [
            'user_id'    => $this->adminUser->id,
            'username'   => $this->adminUser->username,
            'name'       => $this->adminUser->name,
            'role'       => $this->adminUser->role,
            'isLoggedIn' => true,
        ];
        $result = $this->withSession($sessionData)->get('/reports/sales/top-products');
        $result->assertStatus(200);
        $result->assertSee('Top 10 Produk Terlaris'); // Default limit is 10

        // Product 1 was sold 2 (today) + 3 (yesterday) = 5 times
        // Product 2 was sold 1 (today) = 1 time
        // So Product 1 should be listed, and potentially Product 2 if data is within default range (this month)
        $productModel = new ProductModel();
        $p1 = $productModel->find(1); // Assuming ID 1 is Prod A

        $result->assertSee(esc($p1->name));
        $result->assertSee('5'); // Total quantity for P1
    }

    public function testAccessTopProductsReportWithDateFilter()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $today = date('Y-m-d');
        $sessionData = [
            'user_id'    => $this->adminUser->id,
            'username'   => $this->adminUser->username,
            'name'       => $this->adminUser->name,
            'role'       => $this->adminUser->role,
            'isLoggedIn' => true,
        ];
        // Filter for today only
        $result = $this->withSession($sessionData)->get('/reports/sales/top-products?from_date=' . $today . '&to_date=' . $today . '&limit=5');

        $result->assertStatus(200);
        $result->assertSee('Top 5 Produk Terlaris');

        $productModel = new ProductModel();
        $p1 = $productModel->find(1); // Prod A
        $p2 = $productModel->find(2); // Prod B

        // Today: P1 sold 2, P2 sold 1. P1 should be higher or listed first.
        $result->assertSee(esc($p1->name));
        $result->assertSee('2'); // P1 quantity for today
        $result->assertSee(esc($p2->name));
        $result->assertSee('1'); // P2 quantity for today
    }
}
