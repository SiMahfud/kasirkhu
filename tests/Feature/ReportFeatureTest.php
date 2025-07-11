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

class ReportFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    // No global seed, will seed in methods or setup if specific data is common

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Clean tables before seeding
        $this->db->table('categories')->truncate();
        $this->db->table('products')->truncate();
        $this->db->table('users')->truncate();
        $this->db->table('transactions')->truncate();
        $this->db->table('transaction_details')->truncate();
        $this->db->table('settings')->truncate();

        // Seed necessary data
        $this->seed('UserSeeder'); // Assuming UserSeeder creates an admin
        $this->seed('CategorySeeder');
        $this->seed('ProductSeeder');
        $this->seed('SettingSeeder');

        $userModel = new UserModel();
        $this->adminUser = $userModel->where('role', 'admin')->first();
        if (!$this->adminUser) {
            $this->adminUser = $userModel->first(); // Fallback
        }
         if (!$this->adminUser) {
            $userModel->insert([
                'name' => 'Report Test Admin', 'username' => 'reportadmin',
                'password' => password_hash('password123', PASSWORD_DEFAULT), 'role' => 'admin'
            ]);
            $this->adminUser = $userModel->where('username', 'reportadmin')->first();
        }


        // Create some sample transactions for testing reports
        $this->createSampleTransactions();
    }

    protected function createSampleTransactions()
    {
        $userModel = new UserModel();
        $cashier = $this->adminUser; // Use admin as cashier for simplicity

        $productModel = new ProductModel();
        $products = $productModel->limit(2)->find();
        if (count($products) < 2) {
            // Create dummy products if seeder didn't provide enough
            $categoryModel = new CategoryModel();
            $catId = $categoryModel->first()->id ?? $categoryModel->insert(['name' => 'Report Cat']);
            $productModel->insert(['name'=>'Prod A Report','category_id'=>$catId,'price'=>10000,'stock'=>10]);
            $productModel->insert(['name'=>'Prod B Report','category_id'=>$catId,'price'=>20000,'stock'=>10]);
            $products = $productModel->limit(2)->find();
        }

        $p1 = $products[0];
        $p2 = $products[1];

        $transactionModel = new TransactionModel();
        $detailModel = new TransactionDetailModel();

        // Transaction 1 (Today)
        $t1Data = ['user_id' => $cashier->id, 'total_amount' => ($p1->price * 2), 'final_amount' => ($p1->price * 2), 'created_at' => date('Y-m-d H:i:s')];
        $t1Id = $transactionModel->insert($t1Data);
        $detailModel->insert(['transaction_id' => $t1Id, 'product_id' => $p1->id, 'quantity' => 2, 'price_per_unit' => $p1->price, 'subtotal' => $p1->price * 2]);

        // Transaction 2 (Today, different product)
        $t2Data = ['user_id' => $cashier->id, 'total_amount' => ($p2->price * 1), 'final_amount' => ($p2->price * 1), 'created_at' => date('Y-m-d H:i:s')];
        $t2Id = $transactionModel->insert($t2Data);
        $detailModel->insert(['transaction_id' => $t2Id, 'product_id' => $p2->id, 'quantity' => 1, 'price_per_unit' => $p2->price, 'subtotal' => $p2->price * 1]);


        // Transaction 3 (Yesterday)
        $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
        $t3Data = ['user_id' => $cashier->id, 'total_amount' => ($p1->price * 3), 'final_amount' => ($p1->price * 3), 'created_at' => $yesterday];
        $t3Id = $transactionModel->insert($t3Data);
        $detailModel->insert(['transaction_id' => $t3Id, 'product_id' => $p1->id, 'quantity' => 3, 'price_per_unit' => $p1->price, 'subtotal' => $p1->price * 3]);
    }

    public function testAccessDailySalesReportDefaultDate()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $result = $this->actingAs($this->adminUser)->get('/reports/sales/daily');
        $result->assertStatus(200);
        $result->assertSee('Laporan Penjualan Harian');
        // Check if today's transactions are shown (2 transactions, P1*2 and P2*1)
        $result->assertSee(number_format( (new ProductModel())->find(1)->price * 2 + (new ProductModel())->find(2)->price * 1, 0, ',', '.'));
        $result->assertSee('Total Transaksi: 2'); // For today
    }

    public function testAccessDailySalesReportWithDateFilter()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $result = $this->actingAs($this->adminUser)->get('/reports/sales/daily?from_date=' . $yesterday . '&to_date=' . $yesterday);

        $result->assertStatus(200);
        $result->assertSee('Laporan Penjualan Harian');
        // Check if yesterday's transaction is shown (P1*3)
        $result->assertSee(number_format((new ProductModel())->find(1)->price * 3, 0, ',', '.'));
        $result->assertSee('Total Transaksi: 1'); // For yesterday
    }

    public function testAccessDailySalesReportInvalidDateRange()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // from_date after to_date
        $result = $this->actingAs($this->adminUser)->get('/reports/sales/daily?from_date=' . $today . '&to_date=' . $yesterday);

        $result->assertStatus(302); // Should redirect
        $result->assertSessionHas('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
    }


    public function testAccessTopProductsReportDefaultDate()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $result = $this->actingAs($this->adminUser)->get('/reports/sales/top-products');
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
        // Filter for today only
        $result = $this->actingAs($this->adminUser)->get('/reports/sales/top-products?from_date=' . $today . '&to_date=' . $today . '&limit=5');

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
