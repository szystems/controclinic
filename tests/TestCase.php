<?php

namespace Tests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(RolesAndPermissionsSeeder::class);
        // Clear stale tenant context between tests: TenantMiddleware binds
        // 'current_clinic' as a singleton during HTTP tests; if not cleared,
        // BelongsToClinic global scope uses the stale clinic ID in subsequent
        // Livewire tests that create different clinic instances.
        app()->forgetInstance('current_clinic');
    }
}
