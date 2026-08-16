<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreItemGroupRequest;
use App\Http\Requests\MasterData\UpdateItemGroupRequest;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\ItemGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ItemGroupController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $statusQuery = $request->query('status');
        $status = is_string($statusQuery) && in_array($statusQuery, ['aktif', 'nonaktif'], true) ? $statusQuery : 'semua';
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;

        return Inertia::render('master-data/ItemGroups', [
            'filters' => ['search' => $search, 'status' => $status, 'per_page' => (string) $perPage],
            'groups' => ItemGroup::query()
                ->with('parent:id,code,name')
                ->withCount(['items', 'children'])
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")))
                ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
                ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
                ->orderBy('code')
                ->paginate($perPage)
                ->withQueryString(),
            'parentOptions' => ItemGroup::query()->orderBy('name')->get(['id', 'code', 'name']),
            'accountOptions' => Account::query()->where('is_active', true)->where('is_postable', true)->orderBy('code')->get(['code', 'name']),
        ]);
    }

    public function store(StoreItemGroupRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $group = ItemGroup::create($request->validated());
            $this->audit($request, 'item-groups.create', $group, null, $group->getAttributes());
        });

        return to_route('master-data.item-groups.index');
    }

    public function update(UpdateItemGroupRequest $request, ItemGroup $itemGroup): RedirectResponse
    {
        abort_unless($itemGroup->updated_at?->toJSON() === $request->validated('updated_at'), 409, 'Kelompok item sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $itemGroup) {
            $before = $itemGroup->getAttributes();
            $itemGroup->update($request->safe()->except('updated_at'));
            $this->audit($request, 'item-groups.update', $itemGroup, $before, $itemGroup->getAttributes());
        });

        return to_route('master-data.item-groups.index');
    }

    public function toggle(Request $request, ItemGroup $itemGroup): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($itemGroup->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Kelompok item sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $itemGroup) {
            $before = $itemGroup->getAttributes();
            $itemGroup->update(['is_active' => ! $itemGroup->is_active]);
            $this->audit($request, 'item-groups.toggle', $itemGroup, $before, $itemGroup->getAttributes());
        });

        return to_route('master-data.item-groups.index');
    }

    public function destroy(Request $request, ItemGroup $itemGroup): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($itemGroup->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Kelompok item sudah berubah. Muat ulang sebelum menghapus.');

        if ($itemGroup->items()->exists()) {
            throw ValidationException::withMessages(['item_group' => 'Kelompok item tidak dapat dihapus karena masih digunakan oleh barang.']);
        }

        DB::transaction(function () use ($request, $itemGroup) {
            $before = $itemGroup->getAttributes();
            $itemGroup->delete();
            $this->audit($request, 'item-groups.delete', $itemGroup, $before, $itemGroup->getAttributes());
        });

        return to_route('master-data.item-groups.index');
    }

    /** @param array<string, mixed>|null $before @param array<string, mixed> $after */
    private function audit(Request $request, string $action, ItemGroup $itemGroup, ?array $before, array $after): void
    {
        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => ItemGroup::class,
            'auditable_id' => $itemGroup->id,
            'before_values' => $before,
            'after_values' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
