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
    // protected $namespace = 'App';   // Ensure this is null or commented out for DatabaseTestTrait to find all migrations.
    protected $DBGroup = 'tests'; // Ensure tests run on the 'tests' DB group.
    // protected $migrate = true; // DatabaseTestTrait default is true for $refresh.
    // Note: $namespace is intentionally not set here to allow discovery of all migrations.
    protected $migrate = false; // $refresh = true handles migration logic (regress then latest).
    protected $refresh = true; // This should handle regress and then latest for all namespaces.

    protected function setUp(): void
    {
        // Set environment to testing if not already set
        if (ENVIRONMENT !== 'testing') {
            putenv('CI_ENVIRONMENT=testing');
            $_ENV['CI_ENVIRONMENT'] = 'testing';
            Services::resetSingle('kint');
        }

        // Reset services, especially the migrator, to ensure it picks up all namespaces correctly.
        Services::reset(true);
        parent::setUp(); // This will now use a fresh migrator instance.
                         // With $refresh = true, it calls regress() then latest().
                         // With $this->namespace = null, it should discover all migrations.

        // Child classes will call their specific seeders after this parent::setUp().
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
