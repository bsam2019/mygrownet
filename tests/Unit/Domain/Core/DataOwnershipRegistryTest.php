<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Services\DataOwnershipRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DataOwnershipRegistryTest extends TestCase
{
    private DataOwnershipRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new DataOwnershipRegistry();
    }

    #[Test]
    public function register_adds_table_ownership()
    {
        $this->registry->register('my_table', 'my-module', 'company_id');

        $this->assertEquals('my-module', $this->registry->owner('my_table'));
        $this->assertEquals('company_id', $this->registry->tenantColumn('my_table'));
    }

    #[Test]
    public function defaults_include_common_tables()
    {
        $this->assertNotNull($this->registry->owner('users'));
        $this->assertNotNull($this->registry->owner('organizations'));
        $this->assertNotNull($this->registry->owner('applications'));
    }

    #[Test]
    public function owner_returns_null_for_unknown()
    {
        $this->assertNull($this->registry->owner('unknown_table'));
    }

    #[Test]
    public function tenantColumn_returns_null_for_unknown()
    {
        $this->assertNull($this->registry->tenantColumn('unknown'));
    }

    #[Test]
    public function isTenantScoped_returns_true_for_registered()
    {
        $this->registry->register('my_table', 'test');
        $this->assertTrue($this->registry->isTenantScoped('my_table'));
    }

    #[Test]
    public function isTenantScoped_returns_false_for_unregistered()
    {
        $this->assertFalse($this->registry->isTenantScoped('unknown_table'));
    }

    #[Test]
    public function tablesOwnedBy_returns_owned_tables()
    {
        $tables = $this->registry->tablesOwnedBy('bms');

        $this->assertContains('companies', $tables);
        $this->assertContains('invoices', $tables);
        $this->assertContains('customers', $tables);
    }

    #[Test]
    public function tablesOwnedBy_returns_empty_for_unknown_module()
    {
        $this->assertEquals([], $this->registry->tablesOwnedBy('unknown'));
    }

    #[Test]
    public function all_returns_complete_registry()
    {
        $all = $this->registry->all();

        $this->assertArrayHasKey('users', $all);
        $this->assertArrayHasKey('organizations', $all);
        $this->assertArrayHasKey('sa_items', $all);
        $this->assertGreaterThan(20, count($all));
    }

    #[Test]
    public function default_tenant_column_is_organization_id()
    {
        $this->assertEquals('organization_id', $this->registry->tenantColumn('users'));
    }

    #[Test]
    public function organizations_has_empty_tenant_column()
    {
        $this->assertEquals('', $this->registry->tenantColumn('organizations'));
    }

    #[Test]
    public function register_overwrites_existing()
    {
        $this->registry->register('users', 'custom-module', 'custom_id');

        $this->assertEquals('custom-module', $this->registry->owner('users'));
        $this->assertEquals('custom_id', $this->registry->tenantColumn('users'));
    }
}
