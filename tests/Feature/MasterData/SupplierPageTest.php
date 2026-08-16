<?php

namespace Tests\Feature\MasterData;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SupplierPageTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'owner')->valueOrFail('id'));

        return $user;
    }

    private function supplier(array $attributes = []): Supplier
    {
        return Supplier::create([
            'code' => 'TST-SUP-001',
            'name' => 'Supplier Utama',
            'phone' => '081234567890',
            'email' => 'supplier@example.com',
            'address' => 'Balikpapan',
            'is_active' => true,
            ...$attributes,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('master-data.suppliers.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('master-data.suppliers.index'))
            ->assertForbidden();
    }

    public function test_owner_can_search_filter_and_change_page_size(): void
    {
        $this->withoutVite();
        $owner = $this->owner();
        $this->supplier(['is_active' => false]);

        $this->actingAs($owner)
            ->get(route('master-data.suppliers.index', [
                'search' => 'supplier@example.com',
                'status' => 'nonaktif',
                'per_page' => 25,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('master-data/Suppliers')
                ->has('suppliers.data', 1)
                ->where('suppliers.data.0.code', 'TST-SUP-001')
                ->where('suppliers.per_page', 25)
                ->where('filters.per_page', '25')
            );
    }

    public function test_owner_can_create_supplier_with_normalized_values_and_audit_log(): void
    {
        $this->actingAs($this->owner())
            ->post(route('master-data.suppliers.store'), [
                'code' => ' tst-sup-002 ',
                'name' => 'Supplier Dua',
                'phone' => ' 081200000000 ',
                'email' => ' SALES@EXAMPLE.COM ',
                'address' => ' Samarinda ',
                'is_active' => true,
            ])
            ->assertRedirect(route('master-data.suppliers.index'));

        $supplier = Supplier::where('code', 'TST-SUP-002')->firstOrFail();
        $this->assertSame('sales@example.com', $supplier->email);
        $this->assertSame('081200000000', $supplier->phone);
        $this->assertTrue(AuditLog::where('action', 'suppliers.create')->where('auditable_id', $supplier->id)->exists());
    }

    public function test_validation_rejects_duplicate_code_and_invalid_email(): void
    {
        $this->actingAs($this->owner());
        $this->supplier();

        $this->post(route('master-data.suppliers.store'), [
            'code' => 'TST-SUP-001',
            'name' => 'Duplikat',
            'email' => 'bukan-email',
            'is_active' => true,
        ])->assertSessionHasErrors(['code', 'email']);
    }

    public function test_update_rejects_stale_version(): void
    {
        $this->actingAs($this->owner());
        $supplier = $this->supplier();
        $supplier->update(['name' => 'Nama Baru']);

        $this->patch(route('master-data.suppliers.update', $supplier), [
            'code' => $supplier->code,
            'name' => 'Nama Lama',
            'phone' => $supplier->phone,
            'email' => $supplier->email,
            'address' => $supplier->address,
            'is_active' => true,
            'updated_at' => now()->subDay()->toJSON(),
        ])->assertStatus(409);
    }

    public function test_owner_can_update_toggle_and_delete_supplier_with_audit_logs(): void
    {
        $this->actingAs($this->owner());
        $supplier = $this->supplier();

        $this->patch(route('master-data.suppliers.update', $supplier), [
            'code' => $supplier->code,
            'name' => 'Supplier Diperbarui',
            'phone' => null,
            'email' => null,
            'address' => null,
            'is_active' => true,
            'updated_at' => $supplier->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.suppliers.index'));

        $supplier->refresh();
        $this->patch(route('master-data.suppliers.toggle', $supplier), [
            'updated_at' => $supplier->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.suppliers.index'));

        $supplier->refresh();
        $this->delete(route('master-data.suppliers.destroy', $supplier), [
            'updated_at' => $supplier->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.suppliers.index'));

        $this->assertSoftDeleted($supplier);
        foreach (['update', 'toggle', 'delete'] as $action) {
            $this->assertTrue(AuditLog::where('action', "suppliers.{$action}")->where('auditable_id', $supplier->id)->exists());
        }
    }

    public function test_user_without_create_permission_cannot_create_supplier(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('master-data.suppliers.store'), [
                'code' => 'TST-SUP-003',
                'name' => 'Supplier Tiga',
                'is_active' => true,
            ])
            ->assertForbidden();
    }

    public function test_master_data_seeder_provides_twenty_suppliers(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(20, Supplier::where('code', 'like', 'SUP-%')->count());
    }
}
