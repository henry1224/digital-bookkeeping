<?php

namespace Tests\Feature\Database;

use App\Models\Outlet;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_is_assigned_to_all_seeded_outlets(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

        $this->assertSame(Outlet::count(), $admin->outlets()->count());
    }

    public function test_outlet_scoped_user_only_accesses_assigned_outlet(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'staff-outlet')->valueOrFail('id'));

        [$assigned, $other] = Outlet::orderBy('id')->take(2)->get();
        $user->outlets()->attach($assigned);

        $this->assertTrue($user->canAccessOutlet($assigned->id));
        $this->assertFalse($user->canAccessOutlet($other->id));
    }

    public function test_non_outlet_scoped_role_can_access_consolidated_outlets(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'management')->valueOrFail('id'));

        $this->assertTrue($user->canAccessOutlet(Outlet::firstOrFail()->id));
    }
}
