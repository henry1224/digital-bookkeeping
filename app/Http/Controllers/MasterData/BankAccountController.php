<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreBankAccountRequest;
use App\Http\Requests\MasterData\UpdateBankAccountRequest;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $status = in_array($request->query('status'), ['aktif', 'nonaktif'], true) ? $request->query('status') : 'semua';
        $outletId = filter_var($request->query('outlet'), FILTER_VALIDATE_INT) ?: null;
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;

        return Inertia::render('master-data/BankAccounts', [
            'filters' => ['search' => $search, 'status' => $status, 'outlet' => $outletId ? (string) $outletId : 'semua', 'per_page' => (string) $perPage],
            'bankAccounts' => BankAccount::query()->with(['outlet:id,code,name', 'account:id,code,name'])
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('code', 'like', "%{$search}%")->orWhere('bank_name', 'like', "%{$search}%")->orWhere('account_no', 'like', "%{$search}%")->orWhere('account_name', 'like', "%{$search}%")))
                ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
                ->when($outletId, fn ($query) => $query->where('outlet_id', $outletId))->orderBy('code')->paginate($perPage)->withQueryString(),
            'outlets' => Outlet::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'accounts' => Account::query()->where('type', 'asset')->where('is_postable', true)->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        return $this->save($request, new BankAccount, 'create');
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        abort_unless($bankAccount->updated_at?->toJSON() === $request->validated('updated_at'), 409, 'Data rekening bank sudah berubah. Muat ulang sebelum menyimpan.');

        return $this->save($request, $bankAccount, 'update');
    }

    public function toggle(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($bankAccount->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data rekening bank sudah berubah. Muat ulang sebelum menyimpan.');
        DB::transaction(function () use ($request, $bankAccount) {
            $before = $bankAccount->getAttributes();
            $bankAccount->update(['is_active' => ! $bankAccount->is_active]);
            $this->audit($request, 'bank-accounts.toggle', $bankAccount, $before);
        });

        return to_route('master-data.bank-accounts.index');
    }

    public function destroy(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($bankAccount->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data rekening bank sudah berubah. Muat ulang sebelum menghapus.');
        DB::transaction(function () use ($request, $bankAccount) {
            $before = $bankAccount->getAttributes();
            $bankAccount->delete();
            $this->audit($request, 'bank-accounts.delete', $bankAccount, $before);
        });

        return to_route('master-data.bank-accounts.index');
    }

    private function save(StoreBankAccountRequest|UpdateBankAccountRequest $request, BankAccount $bankAccount, string $action): RedirectResponse
    {
        DB::transaction(function () use ($request, $bankAccount, $action) {
            $before = $bankAccount->exists ? $bankAccount->getAttributes() : null;
            $bankAccount->fill($request->safe()->except('updated_at'))->save();
            $this->audit($request, "bank-accounts.{$action}", $bankAccount, $before);
        });

        return to_route('master-data.bank-accounts.index');
    }

    private function audit(Request $request, string $action, BankAccount $bankAccount, ?array $before): void
    {
        AuditLog::create(['actor_id' => $request->user()?->id, 'action' => $action, 'auditable_type' => BankAccount::class, 'auditable_id' => $bankAccount->id, 'before_values' => $before, 'after_values' => $bankAccount->getAttributes(), 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);
    }
}
