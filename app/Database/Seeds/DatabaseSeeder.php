<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Helper to run other seeders
        // Note: Order might be important if there are dependencies

        // Always run UserSeeder first if other seeders depend on users (e.g. for created_by fields)
        // or if tests rely on specific users being present.
        $this->call('AuthGroupSeeder');
        $this->call('UserSeeder');
        $this->call('SettingSeeder'); // Should run early if other parts depend on settings.

        $this->call('CategorySeeder');
        $this->call('ProductSeeder');

        // Call other seeders here
        // Example: $this->call('TransactionSeeder'); // If you create sample transactions

        // echo "All specified seeders run successfully from DatabaseSeeder.\n";
    }
}
