<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreAccountRequest;
use App\Http\Requests\MasterData\UpdateAccountRequest;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\ItemGroup;
use App\Models\JournalEntry;
use App\Models\OutletConfig;
use App\Models\PostingRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $status = $this->filter($request->query('status'), ['aktif', 'nonaktif']);
        $type = $this->filter($request->query('type'), ['asset', 'liability', 'equity', 'revenue', 'cogs', 'expense']);
        $postable = $this->filter($request->query('postable'), ['ya', 'tidak']);
        $reportGroup = $this->filter($request->query('report_group'), ['balance_sheet', 'current_asset', 'current_liability', 'equity', 'profit_loss', 'revenue', 'cogs', 'operating_expense']);
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;

        return Inertia::render('master-data/Accounts', [
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type,
                'postable' => $postable,
                'report_group' => $reportGroup,
                'per_page' => (string) $perPage,
            ],
            'accounts' => Account::query()
                ->with('parent:id,code,name')
                ->withCount('children')
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")))
                ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
                ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
                ->when($type !== 'semua', fn ($query) => $query->where('type', $type))
                ->when($postable === 'ya', fn ($query) => $query->where('is_postable', true))
                ->when($postable === 'tidak', fn ($query) => $query->where('is_postable', false))
                ->when($reportGroup !== 'semua', fn ($query) => $query->where('report_group', $reportGroup))
                ->orderBy('code')
                ->paginate($perPage)
                ->withQueryString(),
            'parentOptions' => Account::query()
                ->where('is_active', true)
                ->where('is_postable', false)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'type']),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $this->validateStructure($request->validated());

        DB::transaction(function () use ($request) {
            $account = Account::create($request->validated());
            $this->audit($request, 'accounts.create', $account, null, $account->getAttributes());
        });

        return to_route('master-data.accounts.index');
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        abort_unless($account->updated_at?->toJSON() === $request->validated('updated_at'), 409, 'Data akun sudah berubah. Muat ulang sebelum menyimpan.');
        $this->validateStructure($request->safe()->except('updated_at'), $account);

        DB::transaction(function () use ($request, $account) {
            $before = $account->getAttributes();
            $account->update($request->safe()->except('updated_at'));
            $this->audit($request, 'accounts.update', $account, $before, $account->getAttributes());
        });

        return to_route('master-data.accounts.index');
    }

    public function toggle(Request $request, Account $account): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($account->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data akun sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $account) {
            $before = $account->getAttributes();
            $account->update(['is_active' => ! $account->is_active]);
            $this->audit($request, 'accounts.toggle', $account, $before, $account->getAttributes());
        });

        return to_route('master-data.accounts.index');
    }

    public function destroy(Request $request, Account $account): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($account->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data akun sudah berubah. Muat ulang sebelum menghapus.');

        if ($this->isUsed($account)) {
            throw ValidationException::withMessages(['account' => 'Akun tidak dapat dihapus karena masih digunakan oleh data lain. Nonaktifkan akun jika tidak dipakai lagi.']);
        }

        DB::transaction(function () use ($request, $account) {
            $before = $account->getAttributes();
            $account->delete();
            $this->audit($request, 'accounts.delete', $account, $before, $account->getAttributes());
        });

        return to_route('master-data.accounts.index');
    }

    /** @param array<string, mixed> $data */
    private function validateStructure(array $data, ?Account $account = null): void
    {
        if (($data['is_postable'] ?? false) && $account?->children()->exists()) {
            throw ValidationException::withMessages(['is_postable' => 'Akun yang memiliki akun turunan tidak dapat digunakan langsung pada transaksi.']);
        }

        if (! ($data['parent_id'] ?? null)) {
            return;
        }

        $parent = Account::findOrFail($data['parent_id']);

        if ($parent->is_postable || $parent->type !== $data['type']) {
            throw ValidationException::withMessages(['parent_id' => 'Pilih akun induk dengan jenis yang sama dan bukan akun transaksi.']);
        }

        while ($account && $parent->parent_id) {
            if ($parent->parent_id === $account->id) {
                throw ValidationException::withMessages(['parent_id' => 'Akun turunan tidak dapat dijadikan induk.']);
            }

            $parent = Account::findOrFail($parent->parent_id);
        }
    }

    private function isUsed(Account $account): bool
    {
        return $account->children()->exists()
            || JournalEntry::where('account_id', $account->id)->exists()
            || PostingRule::where('debit_account_id', $account->id)->orWhere('credit_account_id', $account->id)->exists()
            || BankAccount::where('account_id', $account->id)->exists()
            || OutletConfig::where('default_cash_account_id', $account->id)->orWhere('default_bank_account_id', $account->id)->exists()
            || ItemGroup::where('inventory_account_code', $account->code)->orWhere('revenue_account_code', $account->code)->exists();
    }

    /** @param mixed $value @param array<int, string> $allowed */
    private function filter(mixed $value, array $allowed): string
    {
        return is_string($value) && in_array($value, $allowed, true) ? $value : 'semua';
    }

    /** @param array<string, mixed>|null $before @param array<string, mixed> $after */
    private function audit(Request $request, string $action, Account $account, ?array $before, array $after): void
    {
        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => Account::class,
            'auditable_id' => $account->id,
            'before_values' => $before,
            'after_values' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
