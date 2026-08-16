<?php

namespace Tests\Feature\MasterData;

use App\Models\AuditLog;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\Role;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ItemGroupPageTest extends TestCase
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
        $this->get(route('master-data.item-groups.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('master-data.item-groups.index'))
            ->assertForbidden();
    }

    public function test_owner_can_view_search_filter_and_change_page_size(): void
    {
        $this->withoutVite();
        $owner = $this->owner();
        ItemGroup::where('code', 'RAW-MEAT')->update(['is_active' => false]);

        $this->actingAs($owner)
            ->get(route('master-data.item-groups.index', [
                'search' => 'RAW-MEAT',
                'status' => 'nonaktif',
                'per_page' => 25,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('master-data/ItemGroups')
                ->has('groups.data', 1)
                ->where('groups.data.0.code', 'RAW-MEAT')
                ->where('groups.per_page', 25)
                ->where('filters.per_page', '25')
                ->has('parentOptions')
                ->has('accountOptions')
            );
    }

    public function test_owner_can_create_group_with_normalized_code_and_audit_log(): void
    {
        $this->actingAs($this->owner())
            ->post(route('master-data.item-groups.store'), [
                'code' => ' raw-fish ',
                'name' => 'Ikan',
                'parent_id' => '',
                'inventory_account_code' => '1-3100',
                'revenue_account_code' => '',
                'is_active' => true,
            ])
            ->assertRedirect(route('master-data.item-groups.index'));

        $group = ItemGroup::where('code', 'RAW-FISH')->firstOrFail();
        $this->assertNull($group->revenue_account_code);
        $this->assertTrue(AuditLog::where('action', 'item-groups.create')->where('auditable_id', $group->id)->exists());
    }

    public function test_update_rejects_stale_version_and_self_parent(): void
    {
        $this->actingAs($this->owner());
        $group = ItemGroup::firstOrFail();

        $this->patch(route('master-data.item-groups.update', $group), [
            'code' => $group->code,
            'name' => $group->name,
            'parent_id' => $group->id,
            'inventory_account_code' => $group->inventory_account_code,
            'revenue_account_code' => $group->revenue_account_code,
            'is_active' => true,
            'updated_at' => now()->subDay()->toJSON(),
        ])->assertSessionHasErrors('parent_id');

        $group->update(['name' => 'Nama Baru']);
        $this->patch(route('master-data.item-groups.update', $group), [
            'code' => $group->code,
            'name' => 'Nama Lama',
            'parent_id' => null,
            'inventory_account_code' => $group->inventory_account_code,
            'revenue_account_code' => $group->revenue_account_code,
            'is_active' => true,
            'updated_at' => now()->subDay()->toJSON(),
        ])->assertStatus(409);
    }

    public function test_owner_can_update_toggle_and_delete_unused_group_with_audit_logs(): void
    {
        $this->actingAs($this->owner());
        $group = ItemGroup::where('code', 'RAW-VEG')->firstOrFail();

        $this->patch(route('master-data.item-groups.update', $group), [
            'code' => $group->code,
            'name' => 'Sayuran',
            'parent_id' => null,
            'inventory_account_code' => $group->inventory_account_code,
            'revenue_account_code' => null,
            'is_active' => true,
            'updated_at' => $group->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.item-groups.index'));

        $group->refresh();
        $this->patch(route('master-data.item-groups.toggle', $group), [
            'updated_at' => $group->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.item-groups.index'));

        $group->refresh();
        $this->delete(route('master-data.item-groups.destroy', $group), [
            'updated_at' => $group->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.item-groups.index'));

        $this->assertSoftDeleted($group);
        foreach (['update', 'toggle', 'delete'] as $action) {
            $this->assertTrue(AuditLog::where('action', "item-groups.{$action}")->where('auditable_id', $group->id)->exists());
        }
    }

    public function test_group_used_by_item_cannot_be_deleted(): void
    {
        $this->actingAs($this->owner());
        $group = ItemGroup::firstOrFail();
        $unit = UnitOfMeasure::firstOrFail();
        Item::create([
            'sku' => 'TEST-GROUP',
            'name' => 'Barang Penguji',
            'item_type' => 'raw_material',
            'item_group_id' => $group->id,
            'base_uom_id' => $unit->id,
            'standard_cost_amount' => '1000.00',
            'avg_cost_amount' => '1000.00',
            'is_active' => true,
        ]);

        $this->delete(route('master-data.item-groups.destroy', $group), [
            'updated_at' => $group->updated_at?->toJSON(),
        ])->assertSessionHasErrors('item_group');

        $this->assertNotSoftDeleted($group);
    }

    public function test_user_without_create_permission_cannot_create_group(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('master-data.item-groups.store'), [
                'code' => 'TEST',
                'name' => 'Test',
                'is_active' => true,
            ])
            ->assertForbidden();
    }
}
