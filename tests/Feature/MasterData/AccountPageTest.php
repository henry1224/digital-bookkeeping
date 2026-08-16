<?php

namespace Tests\Feature\MasterData;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountPageTest extends TestCase
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
        $this->get(route('master-data.accounts.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('master-data.accounts.index'))
            ->assertForbidden();
    }

    public function test_owner_can_search_and_filter_accounts(): void
    {
        $this->withoutVite();

        $this->actingAs($this->owner())
            ->get(route('master-data.accounts.index', [
                'search' => 'Kas Outlet',
                'type' => 'asset',
                'report_group' => 'current_asset',
                'postable' => 'ya',
                'status' => 'aktif',
                'per_page' => 25,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('master-data/Accounts')
                ->has('accounts.data', 1)
                ->where('accounts.data.0.code', '1-1100')
                ->where('accounts.per_page', 25)
                ->where('filters.report_group', 'current_asset')
                ->has('parentOptions')
            );
    }

    public function test_owner_can_create_account_with_valid_parent_and_audit_log(): void
    {
        $this->actingAs($this->owner());
        $parent = Account::where('code', '1-1000')->firstOrFail();

        $this->post(route('master-data.accounts.store'), [
            'code' => ' 1-1300 ',
            'name' => 'Kas Kecil',
            'type' => 'asset',
            'parent_id' => $parent->id,
            'is_postable' => true,
            'report_group' => 'current_asset',
            'is_active' => true,
        ])->assertRedirect(route('master-data.accounts.index'));

        $account = Account::where('code', '1-1300')->firstOrFail();
        $this->assertSame($parent->id, $account->parent_id);
        $this->assertTrue(AuditLog::where('action', 'accounts.create')->where('auditable_id', $account->id)->exists());
    }

    public function test_parent_must_be_group_account_with_same_type(): void
    {
        $this->actingAs($this->owner());
        $postableParent = Account::where('code', '1-1100')->firstOrFail();

        $this->post(route('master-data.accounts.store'), [
            'code' => '1-1300',
            'name' => 'Kas Kecil',
            'type' => 'asset',
            'parent_id' => $postableParent->id,
            'is_postable' => true,
            'report_group' => 'current_asset',
            'is_active' => true,
        ])->assertSessionHasErrors('parent_id');

        $liabilityParent = Account::where('code', '2-0000')->firstOrFail();
        $this->post(route('master-data.accounts.store'), [
            'code' => '1-1300',
            'name' => 'Kas Kecil',
            'type' => 'asset',
            'parent_id' => $liabilityParent->id,
            'is_postable' => true,
            'report_group' => 'current_asset',
            'is_active' => true,
        ])->assertSessionHasErrors('parent_id');
    }

    public function test_account_with_children_cannot_be_changed_to_transaction_account(): void
    {
        $this->actingAs($this->owner());
        $account = Account::where('code', '1-1000')->firstOrFail();

        $this->patch(route('master-data.accounts.update', $account), [
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->type,
            'parent_id' => $account->parent_id,
            'is_postable' => true,
            'report_group' => $account->report_group,
            'is_active' => true,
            'updated_at' => $account->updated_at?->toJSON(),
        ])->assertSessionHasErrors('is_postable');
    }

    public function test_descendant_account_cannot_become_parent(): void
    {
        $this->actingAs($this->owner());
        $parent = Account::where('code', '1-0000')->firstOrFail();
        $descendant = Account::where('code', '1-1000')->firstOrFail();

        $this->patch(route('master-data.accounts.update', $parent), [
            'code' => $parent->code,
            'name' => $parent->name,
            'type' => $parent->type,
            'parent_id' => $descendant->id,
            'is_postable' => false,
            'report_group' => $parent->report_group,
            'is_active' => true,
            'updated_at' => $parent->updated_at?->toJSON(),
        ])->assertSessionHasErrors('parent_id');
    }

    public function test_update_rejects_stale_version(): void
    {
        $this->actingAs($this->owner());
        $account = Account::where('code', '1-1100')->firstOrFail();
        $account->update(['name' => 'Kas Outlet Baru']);

        $this->patch(route('master-data.accounts.update', $account), [
            'code' => $account->code,
            'name' => 'Kas Outlet Lama',
            'type' => $account->type,
            'parent_id' => $account->parent_id,
            'is_postable' => true,
            'report_group' => $account->report_group,
            'is_active' => true,
            'updated_at' => now()->subDay()->toJSON(),
        ])->assertStatus(409);
    }

    public function test_owner_can_update_toggle_and_delete_unused_account_with_audit_logs(): void
    {
        $this->actingAs($this->owner());
        $account = Account::create([
            'code' => '9-9000',
            'name' => 'Akun Penguji',
            'type' => 'expense',
            'is_postable' => true,
            'report_group' => 'operating_expense',
            'is_active' => true,
        ]);

        $this->patch(route('master-data.accounts.update', $account), [
            'code' => $account->code,
            'name' => 'Akun Penguji Baru',
            'type' => $account->type,
            'parent_id' => null,
            'is_postable' => true,
            'report_group' => $account->report_group,
            'is_active' => true,
            'updated_at' => $account->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.accounts.index'));

        $account->refresh();
        $this->patch(route('master-data.accounts.toggle', $account), [
            'updated_at' => $account->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.accounts.index'));

        $account->refresh();
        $this->delete(route('master-data.accounts.destroy', $account), [
            'updated_at' => $account->updated_at?->toJSON(),
        ])->assertRedirect(route('master-data.accounts.index'));

        $this->assertSoftDeleted($account);
        foreach (['update', 'toggle', 'delete'] as $action) {
            $this->assertTrue(AuditLog::where('action', "accounts.{$action}")->where('auditable_id', $account->id)->exists());
        }
    }

    public function test_used_account_cannot_be_deleted(): void
    {
        $this->actingAs($this->owner());
        $account = Account::where('code', '1-3100')->firstOrFail();

        $this->delete(route('master-data.accounts.destroy', $account), [
            'updated_at' => $account->updated_at?->toJSON(),
        ])->assertSessionHasErrors('account');

        $this->assertNotSoftDeleted($account);
    }

    public function test_user_without_create_permission_cannot_create_account(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('master-data.accounts.store'), [
                'code' => '9-9000',
                'name' => 'Akun Penguji',
                'type' => 'expense',
                'is_postable' => true,
                'is_active' => true,
            ])
            ->assertForbidden();
    }
}
