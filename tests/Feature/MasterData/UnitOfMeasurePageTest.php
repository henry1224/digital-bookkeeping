<?php

namespace Tests\Feature\MasterData;

use App\Models\AuditLog;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\Permission;
use App\Models\Role;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UnitOfMeasurePageTest extends TestCase
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
        $this->get(route('master-data.uom.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('master-data.uom.index'))
            ->assertForbidden();
    }

    public function test_owner_can_view_search_and_filter_units(): void
    {
        $this->withoutVite();
        $owner = $this->owner();

        UnitOfMeasure::where('code', 'GR')->update(['is_active' => false]);

        $this->actingAs($owner)
            ->get(route('master-data.uom.index', ['search' => 'GR', 'status' => 'nonaktif', 'per_page' => 25]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('master-data/UnitOfMeasures')
                ->has('units.data', 1)
                ->where('units.data.0.code', 'GR')
                ->where('filters.search', 'GR')
                ->where('filters.status', 'nonaktif')
                ->where('filters.per_page', '25')
                ->where('units.per_page', 25)
            );
    }

    public function test_view_only_user_receives_only_view_permission(): void
    {
        $this->withoutVite();
        $this->seed(DatabaseSeeder::class);

        $role = Role::where('slug', 'auditor')->firstOrFail();
        $role->permissions()->sync([Permission::where('slug', 'master-data.view')->valueOrFail('id')]);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('master-data.uom.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.permissions', ['master-data.view'])
            );
    }

    public function test_owner_can_create_unit_with_normalized_codes_and_audit_log(): void
    {
        $this->actingAs($this->owner())
            ->post(route('master-data.uom.store'), [
                'code' => ' ons ',
                'name' => 'Ons',
                'base_code' => ' kg ',
                'factor' => '0.100000',
                'is_active' => true,
            ])
            ->assertRedirect(route('master-data.uom.index'));

        $unit = UnitOfMeasure::where('code', 'ONS')->firstOrFail();

        $this->assertSame('KG', $unit->base_code);
        $this->assertSame('0.100000', $unit->factor);
        $this->assertTrue(AuditLog::where('action', 'unit-of-measures.create')->where('auditable_id', $unit->id)->exists());
    }

    public function test_unit_validation_rejects_duplicate_code_and_invalid_factor(): void
    {
        $this->actingAs($this->owner())
            ->post(route('master-data.uom.store'), [
                'code' => 'KG',
                'name' => 'Duplikat Kilogram',
                'base_code' => 'KG',
                'factor' => '0',
                'is_active' => true,
            ])
            ->assertSessionHasErrors(['code', 'factor']);
    }

    public function test_update_rejects_stale_version(): void
    {
        $this->actingAs($this->owner());

        $unit = UnitOfMeasure::where('code', 'KG')->firstOrFail();
        $unit->update(['name' => 'Kilogram Baru']);

        $this->patch(route('master-data.uom.update', $unit), [
            'code' => $unit->code,
            'name' => 'Kilogram Lama',
            'base_code' => $unit->base_code,
            'factor' => $unit->factor,
            'is_active' => true,
            'updated_at' => now()->subDay()->toJSON(),
        ])->assertStatus(409);
    }

    public function test_owner_can_update_and_toggle_unit_with_audit_logs(): void
    {
        $this->actingAs($this->owner());

        $unit = UnitOfMeasure::where('code', 'DUS')->firstOrFail();

        $this->patch(route('master-data.uom.update', $unit), [
            'code' => $unit->code,
            'name' => 'Karton',
            'base_code' => $unit->base_code,
            'factor' => '12.000000',
            'is_active' => true,
            'updated_at' => $unit->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.uom.index'));

        $unit->refresh();

        $this->patch(route('master-data.uom.toggle', $unit), [
            'updated_at' => $unit->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.uom.index'));

        $this->assertSame('Karton', $unit->refresh()->name);
        $this->assertFalse($unit->is_active);
        $this->assertTrue(AuditLog::where('action', 'unit-of-measures.update')->where('auditable_id', $unit->id)->exists());
        $this->assertTrue(AuditLog::where('action', 'unit-of-measures.toggle')->where('auditable_id', $unit->id)->exists());
    }

    public function test_owner_can_soft_delete_unused_unit_with_audit_log(): void
    {
        $this->actingAs($this->owner());

        $unit = UnitOfMeasure::create([
            'code' => 'UNUSED',
            'name' => 'Satuan Kosong',
            'base_code' => 'UNUSED',
            'factor' => '1.000000',
            'is_active' => true,
        ]);

        $this->delete(route('master-data.uom.destroy', $unit), [
            'updated_at' => $unit->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.uom.index'));

        $this->assertSoftDeleted($unit);
        $this->assertTrue(AuditLog::where('action', 'unit-of-measures.delete')->where('auditable_id', $unit->id)->exists());
    }

    public function test_unit_used_by_item_cannot_be_deleted(): void
    {
        $this->actingAs($this->owner());

        $unit = UnitOfMeasure::where('code', 'KG')->firstOrFail();
        $group = ItemGroup::firstOrFail();

        Item::create([
            'sku' => 'TEST-UOM',
            'name' => 'Item Penguji UOM',
            'item_type' => 'raw_material',
            'item_group_id' => $group->id,
            'base_uom_id' => $unit->id,
            'standard_cost_amount' => '1000.00',
            'avg_cost_amount' => '1000.00',
            'is_active' => true,
        ]);

        $this->delete(route('master-data.uom.destroy', $unit), [
            'updated_at' => $unit->updated_at?->toJSON(),
        ])->assertSessionHasErrors('unit_of_measure');

        $this->assertNotSoftDeleted($unit);
    }

    public function test_user_without_create_permission_cannot_create_unit(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('master-data.uom.store'), [
                'code' => 'ONS',
                'name' => 'Ons',
                'base_code' => 'KG',
                'factor' => '0.100000',
                'is_active' => true,
            ])
            ->assertForbidden();
    }
}
