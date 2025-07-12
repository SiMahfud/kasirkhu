<?php

namespace Tests\Feature;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel; // Use Shield's UserModel
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

    protected ShieldUserModel $userModel; // Use Shield's UserModel
    protected ProductModel $productModel;
    protected CategoryModel $categoryModel;
    protected TransactionModel $transactionModel;
    protected TransactionDetailModel $transactionDetailModel;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp(); // Handles migrations via BaseFeatureTestCase

        // Initialize models
        $this->userModel = model(ShieldUserModel::class); // Use Shield's UserModel
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();

        // Seed necessary data after migrations
        $this->seed('AdminUserSeeder'); // Corrected from UserSeeder
        $this->seed('CategorySeeder');
        $this->seed('ProductSeeder');
        // SettingSeeder might not be directly relevant for reports but good for consistency
        $this->seed('SettingSeeder'); // This should be App\Database\Seeds\SettingSeeder

        $this->adminUser = $this->userModel->findByCredentials(['email' => 'admin@example.com']);
        if (!$this->adminUser) {
            $this->adminUser = $this->userModel->where('username', 'admin')->first(); // Fallback to username
        }

        if (!$this->adminUser) {
             log_message('error', 'Admin user not found by username or email in ReportFeatureTest::setUp. Attempting to create a fallback user.');
            $tempUser = new \CodeIgniter\Shield\Entities\User([
                'username' => 'report_test_admin' . random_int(1000,9999),
                'email'    => 'report_test_admin' . random_int(1000,9999) . '@example.com',
                'password' => 'password123'
            ]);
            $this->userModel->save($tempUser);
            $this->adminUser = $this->userModel->findById($this->userModel->getInsertID());
            if($this->adminUser){
                 $this->adminUser->addGroup('admin'); // Add to admin group
            }
        }
        $this->assertNotNull($this->adminUser, "Failed to get/create an admin user for report tests.");

        // Create some sample transactions for testing reports
        $this->createSampleTransactions();
    }

    protected function createSampleTransactions()
    {
        $cashier = $this->adminUser; // Use admin as cashier for simplicity

        $p1 = $this->productModel->where('code', 'ATK001')->first(); // Pulpen, 2500
        $p2 = $this->productModel->where('code', 'ATK002')->first(); // Buku, 3000

        $this->assertNotNull($p1, "Product ATK001 not found. Ensure ProductSeeder ran and created it.");
        $this->assertNotNull($p2, "Product ATK002 not found. Ensure ProductSeeder ran and created it.");

        // Transaction 1 (Today): 2 units of P1 (Pulpen) - Let model set timestamp
        $t1Data = ['user_id' => $cashier->id, 'total_amount' => ($p1->price * 2), 'final_amount' => ($p1->price * 2)];
        $t1Id = $this->transactionModel->insert($t1Data);
        $this->transactionDetailModel->insert(['transaction_id' => $t1Id, 'product_id' => $p1->id, 'quantity' => 2, 'price_per_unit' => $p1->price, 'subtotal' => $p1->price * 2]);

        // Transaction 2 (Today): 1 unit of P2 (Buku) - Let model set timestamp
        $t2Data = ['user_id' => $cashier->id, 'total_amount' => ($p2->price * 1), 'final_amount' => ($p2->price * 1)];
        $t2Id = $this->transactionModel->insert($t2Data);
        $this->transactionDetailModel->insert(['transaction_id' => $t2Id, 'product_id' => $p2->id, 'quantity' => 1, 'price_per_unit' => $p2->price, 'subtotal' => $p2->price * 1]);

        // Transaction 3 (Yesterday): 3 units of P1 (Pulpen)
        $yesterday_dt_string = date('Y-m-d H:i:s', strtotime('-1 day'));
        // Insert T3 first, its created_at will be NOW by model
        $t3Data = ['user_id' => $cashier->id, 'total_amount' => ($p1->price * 3), 'final_amount' => ($p1->price * 3)];
        $t3Id = $this->transactionModel->insert($t3Data);
        $this->assertIsNumeric($t3Id, "Failed to insert T3. Errors: " . json_encode($this->transactionModel->errors()));

        // Now, explicitly update its created_at to yesterday
        // Note: This will also update the updated_at field to NOW, which is fine for this test.
        $updateResult = $this->transactionModel->update($t3Id, ['created_at' => $yesterday_dt_string]);
        $this->assertTrue($updateResult, "Failed to update T3's created_at. Errors: " . json_encode($this->transactionModel->errors()));

        // Verify it actually updated in the DB immediately
        $t3_check = $this->transactionModel->find($t3Id);
        $this->assertEquals($yesterday_dt_string, $t3_check->created_at->format('Y-m-d H:i:s'), "T3 created_at not updated to yesterday correctly.");

        $this->transactionDetailModel->insert(['transaction_id' => $t3Id, 'product_id' => $p1->id, 'quantity' => 3, 'price_per_unit' => $p1->price, 'subtotal' => $p1->price * 3]);
    }

    public function testAccessDailySalesReportDefaultDate()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        // Log actual transactions from DB for today before calling the controller
        $today_start_db = date('Y-m-d 00:00:00');
        $today_end_db = date('Y-m-d 23:59:59');
        $todays_transactions_from_db = $this->transactionModel
            ->where('created_at >=', $today_start_db)
            ->where('created_at <=', $today_end_db)
            ->findAll();
        // Convert to array for simpler logging if objects are complex
        // $loggable_transactions = array_map(fn($tr) => $tr->toArray(), $todays_transactions_from_db);
        // log_message('error', '[TestDefaultDate] Actual DB Transactions for today BEFORE controller call: ' . json_encode($loggable_transactions));

        // This check is now implicitly confirmed if the controller's sum matches.
        // $actual_sum_from_db_query = 0;
        // foreach ($todays_transactions_from_db as $tr) {
        //     $actual_sum_from_db_query += (float)$tr->final_amount;
        // }
        // $p1_for_calc = $this->productModel->where('code', 'ATK001')->first();
        // $p2_for_calc = $this->productModel->where('code', 'ATK002')->first();
        // $hardcoded_expected_sum_today = ($p1_for_calc->price * 2) + ($p2_for_calc->price * 1);
        // if (abs($actual_sum_from_db_query - $hardcoded_expected_sum_today) > 0.001) { // Compare floats
        //     $this->fail("Mismatch: Sum of final_amount from DB for today is {$actual_sum_from_db_query}, but test expected {$hardcoded_expected_sum_today}. DB data: " . json_encode(array_map(fn($tr) => $tr->toArray(), $todays_transactions_from_db)));
        // }

        $result = $this->withSession($sessionData)->get('/reports/sales/daily');
        $result->assertStatus(200);
        $result->assertSee('Laporan Penjualan Harian');

        // Calculate expected total based on known products and quantities from createSampleTransactions
        $p1 = $this->productModel->where('code', 'ATK001')->first(); // Pulpen, 2500
        $p2 = $this->productModel->where('code', 'ATK002')->first(); // Buku, 3000
        $this->assertNotNull($p1);
        $this->assertNotNull($p2);

        $expectedTotalToday = ($p1->price * 2) + ($p2->price * 1); // 2 Pulpen, 1 Buku
        // log_message('error', '[TestDefaultDate] Expected Total Today: ' . $expectedTotalToday . ' from P1 price ' . $p1->price . ' and P2 price ' . $p2->price);
        $result->assertSee(number_format($expectedTotalToday, 0, ',', '.')); // Restore original assertion
        // $result->assertSee('DEBUG_TOTAL_SALES: ' . $expectedTotalToday);

        // $responseBody = $result->getBody();
        // $ringkasanPos = strpos($responseBody, 'Ringkasan Periode');
        // $excerpt = $ringkasanPos !== false ? substr($responseBody, $ringkasanPos, 600) : 'Ringkasan Periode not found in body: ' . $responseBody;
        // if (strlen($excerpt) > 1000) $excerpt = substr($excerpt, 0, 1000) . '... [TRUNCATED]';
        // $this->fail("Debug: Check response body for 'Total Transaksi: 2'. Body excerpt around 'Ringkasan Periode': " . $excerpt);

        $result->assertSee('Total Transaksi:');
        $result->assertSee('<strong class="fs-5">2</strong>'); // This relies on exact HTML structure
        // $result->assertSee('DEBUG_TX_COUNT: 2');
    }

    public function testAccessDailySalesReportWithDateFilter()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $result = $this->actingAs($this->adminUser)
                       ->get('/reports/sales/daily?from_date=' . $yesterday . '&to_date=' . $yesterday);

        $result->assertStatus(200);
        $result->assertSee('Laporan Penjualan Harian');

        // Calculate expected total based on known products for yesterday
        $p1 = $this->productModel->where('code', 'ATK001')->first(); // Pulpen, 2500
        $this->assertNotNull($p1);

        $expectedTotalYesterday = ($p1->price * 3); // 3 Pulpen yesterday
        log_message('error', '[TestDateFilter] Expected Total Yesterday: ' . $expectedTotalYesterday . ' from P1 price ' . $p1->price);
        $result->assertSee(number_format($expectedTotalYesterday, 0, ',', '.'));
        $result->assertSee('Total Transaksi:');
        $result->assertSee('<strong class="fs-5">1</strong>'); // This relies on exact HTML structure
        // $result->assertSee('DEBUG_TX_COUNT: 1');
    }

    public function testAccessDailySalesReportInvalidDateRange()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // from_date after to_date
        $result = $this->actingAs($this->adminUser)
                       ->get('/reports/sales/daily?from_date=' . $today . '&to_date=' . $yesterday);

        $result->assertStatus(302); // Should redirect
        $result->assertSessionHas('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
    }


    public function testAccessTopProductsReportDefaultDate()
    {
        if (!$this->adminUser) $this->markTestSkipped('Admin user not found for report testing.');

        $result = $this->actingAs($this->adminUser)
                       ->get('/reports/sales/top-products');
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
        $result = $this->actingAs($this->adminUser)
                       ->get('/reports/sales/top-products?from_date=' . $today . '&to_date=' . $today . '&limit=5');

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
