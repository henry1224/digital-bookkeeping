<?php

namespace Tests\Feature\MasterData;

use App\Models\AuditLog;
use App\Models\Outlet;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OutletPageTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'owner')->valueOrFail('id'));

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('master-data.outlets.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('master-data.outlets.index'))
            ->assertForbidden();
    }

    public function test_owner_can_view_paginated_outlets(): void
    {
        $this->withoutVite();

        $this->actingAs($this->owner())
            ->get(route('master-data.outlets.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('master-data/Outlets')
                ->has('outlets.data', Outlet::count())
                ->where('filters.search', '')
            );
    }

    public function test_owner_can_filter_outlets_by_status_and_type(): void
    {
        $this->withoutVite();
        $owner = $this->owner();

        Outlet::create([
            'code' => 'BPN-X',
            'name' => 'Balikpapan X',
            'outlet_type' => 'outlet',
            'timezone' => 'Asia/Makassar',
            'is_active' => false,
        ]);

        $this->actingAs($owner)
            ->get(route('master-data.outlets.index', ['status' => 'nonaktif', 'type' => 'outlet']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('master-data/Outlets')
                ->has('outlets.data', 1)
                ->where('outlets.data.0.code', 'BPN-X')
                ->where('filters.status', 'nonaktif')
                ->where('filters.type', 'outlet')
            );
    }

    public function test_owner_can_create_outlet_with_audit_log(): void
    {
        $this->actingAs($this->owner())
            ->post(route('master-data.outlets.store'), [
                'code' => 'BPN-C',
                'name' => 'Balikpapan C',
                'outlet_type' => 'outlet',
                'timezone' => 'Asia/Makassar',
                'is_active' => true,
            ])
            ->assertRedirect(route('master-data.outlets.index'));

        $outlet = Outlet::where('code', 'BPN-C')->firstOrFail();

        $this->assertSame('Balikpapan C', $outlet->name);
        $this->assertTrue(AuditLog::where('action', 'outlets.create')->where('auditable_id', $outlet->id)->exists());
    }

    public function test_update_rejects_stale_outlet_version(): void
    {
        $this->actingAs($this->owner());

        $outlet = Outlet::firstOrFail();
        $outlet->update(['name' => 'Nama Baru']);

        $this->patch(route('master-data.outlets.update', $outlet), [
            'code' => $outlet->code,
            'name' => 'Nama Lama',
            'outlet_type' => $outlet->outlet_type,
            'timezone' => $outlet->timezone,
            'is_active' => true,
            'updated_at' => now()->subDay()->toJSON(),
        ])->assertStatus(409);
    }

    public function test_owner_can_toggle_outlet_status_with_audit_log(): void
    {
        $this->actingAs($this->owner());

        $outlet = Outlet::firstOrFail();

        $this->patch(route('master-data.outlets.toggle', $outlet), [
            'updated_at' => $outlet->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.outlets.index'));

        $this->assertFalse($outlet->refresh()->is_active);
        $this->assertTrue(AuditLog::where('action', 'outlets.toggle')->where('auditable_id', $outlet->id)->exists());
    }

    public function test_user_without_create_permission_cannot_create_outlet(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('master-data.outlets.store'), [
                'code' => 'BPN-C',
                'name' => 'Balikpapan C',
                'outlet_type' => 'outlet',
                'timezone' => 'Asia/Makassar',
                'is_active' => true,
            ])
            ->assertForbidden();
    }
}
