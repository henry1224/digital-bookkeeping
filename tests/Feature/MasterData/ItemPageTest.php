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

class ItemPageTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'owner')->valueOrFail('id'));

        return $user;
    }

    private function item(array $attributes = []): Item
    {
        return Item::create([
            'sku' => 'TST-ITEM-001',
            'name' => 'Item Penguji',
            'item_type' => 'raw_material',
            'item_group_id' => ItemGroup::firstOrFail()->id,
            'base_uom_id' => UnitOfMeasure::firstOrFail()->id,
            'standard_cost_amount' => 25000,
            'avg_cost_amount' => 24000,
            'is_active' => true,
            ...$attributes,
        ]);
    }

    private function payload(Item $item, array $attributes = []): array
    {
        return [
            'sku' => $item->sku,
            'name' => $item->name,
            'item_type' => $item->item_type,
            'item_group_id' => $item->item_group_id,
            'base_uom_id' => $item->base_uom_id,
            'standard_cost_amount' => $item->standard_cost_amount,
            'avg_cost_amount' => $item->avg_cost_amount,
            'is_active' => $item->is_active,
            'updated_at' => $item->updated_at?->toJSON(),
            ...$attributes,
        ];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('master-data.items.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get(route('master-data.items.index'))->assertForbidden();
    }

    public function test_owner_can_search_and_filter_items(): void
    {
        $this->withoutVite();
        $owner = $this->owner();
        $item = $this->item(['is_active' => false]);

        $this->actingAs($owner)->get(route('master-data.items.index', [
            'search' => 'TST-ITEM', 'status' => 'nonaktif', 'type' => 'raw_material',
            'group' => $item->item_group_id, 'per_page' => 25,
        ]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('master-data/Items')
            ->has('items.data', 1)
            ->where('items.data.0.sku', 'TST-ITEM-001')
            ->where('items.per_page', 25)
            ->has('itemGroups')
            ->has('unitOfMeasures'));
    }

    public function test_owner_can_create_item_with_normalized_sku_and_audit_log(): void
    {
        $this->actingAs($this->owner());
        $group = ItemGroup::firstOrFail();
        $uom = UnitOfMeasure::firstOrFail();

        $this->post(route('master-data.items.store'), [
            'sku' => ' tst-item-002 ', 'name' => 'Item Dua', 'item_type' => 'finished_good',
            'item_group_id' => $group->id, 'base_uom_id' => $uom->id,
            'standard_cost_amount' => 10000, 'avg_cost_amount' => 9000, 'is_active' => true,
        ])->assertRedirect(route('master-data.items.index'));

        $item = Item::where('sku', 'TST-ITEM-002')->firstOrFail();
        $this->assertTrue(AuditLog::where('action', 'items.create')->where('auditable_id', $item->id)->exists());
    }

    public function test_validation_rejects_duplicate_sku_and_invalid_values(): void
    {
        $this->actingAs($this->owner());
        $item = $this->item();

        $this->post(route('master-data.items.store'), [
            'sku' => $item->sku, 'name' => '', 'item_type' => 'invalid',
            'item_group_id' => 999999, 'base_uom_id' => 999999,
            'standard_cost_amount' => -1, 'avg_cost_amount' => -1, 'is_active' => true,
        ])->assertSessionHasErrors(['sku', 'name', 'item_type', 'item_group_id', 'base_uom_id', 'standard_cost_amount', 'avg_cost_amount']);
    }

    public function test_update_rejects_stale_version(): void
    {
        $this->actingAs($this->owner());
        $item = $this->item();
        $item->update(['name' => 'Nama Baru']);

        $this->patch(route('master-data.items.update', $item), $this->payload($item, ['name' => 'Nama Lama', 'updated_at' => now()->subDay()->toJSON()]))
            ->assertStatus(409);
    }

    public function test_owner_can_update_toggle_and_soft_delete_with_audit_logs(): void
    {
        $this->actingAs($this->owner());
        $item = $this->item();

        $this->patch(route('master-data.items.update', $item), $this->payload($item, ['name' => 'Item Diperbarui']))
            ->assertRedirect(route('master-data.items.index'));
        $item->refresh();
        $this->patch(route('master-data.items.toggle', $item), ['updated_at' => $item->updated_at?->toJSON()])
            ->assertRedirect(route('master-data.items.index'));
        $item->refresh();
        $this->delete(route('master-data.items.destroy', $item), ['updated_at' => $item->updated_at?->toJSON()])
            ->assertRedirect(route('master-data.items.index'));

        $this->assertSoftDeleted($item);
        foreach (['update', 'toggle', 'delete'] as $action) {
            $this->assertTrue(AuditLog::where('action', "items.{$action}")->where('auditable_id', $item->id)->exists());
        }
    }

    public function test_user_without_create_permission_cannot_create_item(): void
    {
        $this->actingAs(User::factory()->create())->post(route('master-data.items.store'), [])->assertForbidden();
    }

    public function test_master_data_seeder_provides_twenty_items_and_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(20, Item::count());
        $this->assertSame('Daging Sirloin', Item::where('sku', 'BEEF-SIRLOIN')->value('name'));
    }
}
