<?php

namespace Tests\Feature\Database;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_rbac_seeder_is_idempotent_and_owner_gets_permissions(): void
    {
        $this->seed(RbacSeeder::class);
        $this->seed(RbacSeeder::class);

        $owner = Role::where('slug', 'owner')->firstOrFail();

        $this->assertSame(12, Role::count());
        $this->assertSame(1, Permission::where('slug', 'daily-sales.post')->count());
        $this->assertTrue($owner->permissions()->where('slug', 'daily-sales.post')->exists());
    }

    public function test_admin_user_has_owner_permission(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

        $this->assertTrue($admin->hasPermission('reports.export'));
    }
}
