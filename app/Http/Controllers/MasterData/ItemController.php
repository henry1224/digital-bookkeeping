<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreItemRequest;
use App\Http\Requests\MasterData\UpdateItemRequest;
use App\Models\AuditLog;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\UnitOfMeasure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $status = $this->filter($request->query('status'), ['aktif', 'nonaktif']);
        $type = $this->filter($request->query('type'), ['raw_material', 'finished_good', 'menu', 'non_stock']);
        $groupId = filter_var($request->query('group'), FILTER_VALIDATE_INT) ?: null;
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;

        return Inertia::render('master-data/Items', [
            'filters' => ['search' => $search, 'status' => $status, 'type' => $type, 'group' => $groupId ? (string) $groupId : 'semua', 'per_page' => (string) $perPage],
            'items' => Item::query()
                ->with(['itemGroup:id,code,name', 'baseUom:id,code,name'])
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('sku', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")))
                ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
                ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
                ->when($type !== 'semua', fn ($query) => $query->where('item_type', $type))
                ->when($groupId, fn ($query) => $query->where('item_group_id', $groupId))
                ->orderBy('sku')
                ->paginate($perPage)
                ->withQueryString(),
            'itemGroups' => ItemGroup::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'unitOfMeasures' => UnitOfMeasure::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $item = Item::create($request->validated());
            $this->audit($request, 'items.create', $item, null, $item->getAttributes());
        });

        return to_route('master-data.items.index');
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        abort_unless($item->updated_at?->toJSON() === $request->validated('updated_at'), 409, 'Data item sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $item) {
            $before = $item->getAttributes();
            $item->update($request->safe()->except('updated_at'));
            $this->audit($request, 'items.update', $item, $before, $item->getAttributes());
        });

        return to_route('master-data.items.index');
    }

    public function toggle(Request $request, Item $item): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($item->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data item sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $item) {
            $before = $item->getAttributes();
            $item->update(['is_active' => ! $item->is_active]);
            $this->audit($request, 'items.toggle', $item, $before, $item->getAttributes());
        });

        return to_route('master-data.items.index');
    }

    public function destroy(Request $request, Item $item): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($item->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data item sudah berubah. Muat ulang sebelum menghapus.');

        DB::transaction(function () use ($request, $item) {
            $before = $item->getAttributes();
            $item->delete();
            $this->audit($request, 'items.delete', $item, $before, $item->getAttributes());
        });

        return to_route('master-data.items.index');
    }

    private function filter(mixed $value, array $allowed): string
    {
        return is_string($value) && in_array($value, $allowed, true) ? $value : 'semua';
    }

    /** @param array<string, mixed>|null $before @param array<string, mixed> $after */
    private function audit(Request $request, string $action, Item $item, ?array $before, array $after): void
    {
        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => Item::class,
            'auditable_id' => $item->id,
            'before_values' => $before,
            'after_values' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
