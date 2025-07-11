<?php

namespace Tests\Support\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
// use CodeIgniter\Test\Filters\WithSecurity; // This was incorrect for actingAs
use Config\Database;
use Config\Services;

class BaseFeatureTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    // use WithSecurity; // FeatureTestTrait provides actingAs

    // Settings for DatabaseTestTrait
    // protected $refresh = true; // We will manage migrations manually in setUp
    protected $migrateOnce = false; // Ensure migrations can run per test if needed, though setUp manages it now.
    protected $namespace = 'App';   // Namespace for migrations and seeds
    protected $DBGroup = 'tests';

    protected function setUp(): void
    {
        // Set environment to testing
        if (ENVIRONMENT !== 'testing') {
            putenv('CI_ENVIRONMENT=testing');
            $_ENV['CI_ENVIRONMENT'] = 'testing';
        }

        // Call parent::setUp() to initialize traits, but DatabaseTestTrait's DB setup might be too late or problematic.
        // We will handle DB connection and migration explicitly.
        parent::setUp();

        // Explicitly connect to the 'tests' database group
        // $this->db is initialized by DatabaseTestTrait through parent::setUp() if $DBGroup is set.
        // Ensure it's the correct 'tests' group connection.
        if ($this->db === null || $this->db->getPrefix() !== config(Database::class)->tests['DBPrefix']) {
             $this->db = Database::connect($this->DBGroup);
             $this->forge = Database::forge($this->DBGroup); // Re-initialize forge with this connection
        }

        // Explicitly run migrations
        $migrations = Services::migrations(null, $this->db); // Use $this->db which is the 'tests' group connection
        $migrations->setNamespace('App'); // Crucial for finding App's migrations

        // Regress all migrations first to ensure a clean state for each test method.
        if (!$migrations->regress(0)) {
            $error = $migrations->errorString() ?? 'Unknown regression error';
            log_message('error', "Migration regression failed in BaseFeatureTestCase::setUp(): " . $error);
            // $this->fail("Migration regression failed: " . $error); // Failing here might hide other issues
        }

        // Run all migrations to the latest version
        if (!$migrations->latest()) {
            $error = $migrations->errorString() ?? 'Unknown migration error';
            log_message('error', "Migrations failed in BaseFeatureTestCase::setUp(): " . $error);
            $this->fail("Migrations failed: " . $error);
        }

        // Note: $this->seed property from DatabaseTestTrait would normally be called by parent::setUp() (via runSeeds).
        // If we want to ensure it runs *after* our explicit migration, we might need to call it manually here,
        // or ensure $this->seed is not set and all child classes call $this->seed('SpecificSeeder') themselves.
        // For now, let child classes handle their own specific seeding after this explicit migration.
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // DatabaseTestTrait's tearDownTraits will handle cleanup if $refresh is true.
    }
}
