<?php

namespace Tests\Feature\MasterData;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BankAccountPageTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'owner')->valueOrFail('id'));

        return $user;
    }

    private function bank(): BankAccount
    {
        return BankAccount::create(['code' => 'TEST-BANK', 'bank_name' => 'Bank Test', 'account_no' => '12345', 'account_name' => 'Perusahaan Test', 'account_id' => Account::where('code', '1-1200')->valueOrFail('id'), 'is_active' => true]);
    }

    public function test_permissions_and_page(): void
    {
        $this->get(route('master-data.bank-accounts.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('master-data.bank-accounts.index'))->assertForbidden();
        $this->withoutVite();
        $this->actingAs($this->owner())->get(route('master-data.bank-accounts.index'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('master-data/BankAccounts')->has('bankAccounts.data')->has('outlets')->has('accounts'));
    }

    public function test_owner_can_create_and_audit(): void
    {
        $this->actingAs($this->owner());
        $this->post(route('master-data.bank-accounts.store'), ['code' => ' bank-test ', 'bank_name' => 'Bank Test', 'account_no' => '12345', 'account_name' => 'Perusahaan Test', 'account_id' => Account::where('code', '1-1200')->valueOrFail('id'), 'is_active' => true])->assertRedirect(route('master-data.bank-accounts.index'));
        $bank = BankAccount::where('code', 'BANK-TEST')->firstOrFail();
        $this->assertTrue(AuditLog::where('action', 'bank-accounts.create')->where('auditable_id', $bank->id)->exists());
    }

    public function test_stale_update_is_rejected(): void
    {
        $this->actingAs($this->owner());
        $bank = $this->bank();
        $this->patch(route('master-data.bank-accounts.update', $bank), ['code' => $bank->code, 'bank_name' => $bank->bank_name, 'account_no' => $bank->account_no, 'account_name' => $bank->account_name, 'account_id' => $bank->account_id, 'is_active' => true, 'updated_at' => now()->subDay()->toJSON()])->assertStatus(409);
    }

    public function test_update_toggle_delete_and_seed(): void
    {
        $this->actingAs($this->owner());
        $bank = $this->bank();
        $payload = ['code' => $bank->code, 'bank_name' => 'Bank Baru', 'account_no' => $bank->account_no, 'account_name' => $bank->account_name, 'account_id' => $bank->account_id, 'is_active' => true, 'updated_at' => $bank->updated_at?->toJSON()];
        $this->patch(route('master-data.bank-accounts.update', $bank), $payload)->assertRedirect();
        $bank->refresh();
        $this->patch(route('master-data.bank-accounts.toggle', $bank), ['updated_at' => $bank->updated_at?->toJSON()])->assertRedirect();
        $bank->refresh();
        $this->delete(route('master-data.bank-accounts.destroy', $bank), ['updated_at' => $bank->updated_at?->toJSON()])->assertRedirect();
        $this->assertSoftDeleted($bank);
        $this->assertSame(3, BankAccount::where('code', 'like', 'BANK-%')->count());
    }
}
