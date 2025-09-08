<?php

namespace Tests;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

abstract class TenantTestCase extends TestCase
{
    protected ?Tenant $tenant = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite') {
            config(['tenancy.database.suffix' => '.sqlite']);
        }

        $this->tenant = Tenant::factory()
            ->has(Domain::factory())
            ->create(); // triggers DB creation automatically

        tenancy()->initialize($this->tenant);
    }

    protected function tearDown(): void
    {
        tenancy()->end();

        // For some reason I have not figured out, the records stay in DB so we do this to force it.
        // May be something related to eloquent.
        Tenant::all()->each->delete();
        DB::table('tenants')->delete();
        DB::table('domains')->delete();

        parent::tearDown();
    }
}
