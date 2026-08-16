<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreOutletRequest;
use App\Http\Requests\MasterData\UpdateOutletRequest;
use App\Models\AuditLog;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OutletController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $statusQuery = $request->query('status');
        $typeQuery = $request->query('type');
        $status = is_string($statusQuery) && in_array($statusQuery, ['aktif', 'nonaktif'], true) ? $statusQuery : 'semua';
        $type = is_string($typeQuery) && in_array($typeQuery, ['outlet', 'central_kitchen'], true) ? $typeQuery : 'semua';

        return Inertia::render('master-data/Outlets', [
            'filters' => ['search' => $search, 'status' => $status, 'type' => $type],
            'outlets' => Outlet::query()
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                }))
                ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
                ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
                ->when($type !== 'semua', fn ($query) => $query->where('outlet_type', $type))
                ->orderBy('code')
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Outlet $outlet): array => [
                    'id' => $outlet->id,
                    'code' => $outlet->code,
                    'name' => $outlet->name,
                    'outlet_type' => $outlet->outlet_type,
                    'timezone' => $outlet->timezone,
                    'is_active' => $outlet->is_active,
                    'updated_at' => $outlet->updated_at?->toJSON(),
                ]),
        ]);
    }

    public function store(StoreOutletRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $outlet = Outlet::create($request->validated());

            $this->audit($request, 'outlets.create', $outlet, null, $outlet->getAttributes());
        });

        return to_route('master-data.outlets.index');
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet): RedirectResponse
    {
        abort_unless($outlet->updated_at?->toJSON() === $request->validated('updated_at'), 409, 'Data outlet sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $outlet) {
            $before = $outlet->getAttributes();

            $outlet->update($request->safe()->except('updated_at'));

            $this->audit($request, 'outlets.update', $outlet, $before, $outlet->getAttributes());
        });

        return to_route('master-data.outlets.index');
    }

    public function toggle(Request $request, Outlet $outlet): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($outlet->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data outlet sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $outlet) {
            $before = $outlet->getAttributes();

            $outlet->update(['is_active' => ! $outlet->is_active]);

            $this->audit($request, 'outlets.toggle', $outlet, $before, $outlet->getAttributes());
        });

        return to_route('master-data.outlets.index');
    }

    /** @param array<string, mixed>|null $before @param array<string, mixed> $after */
    private function audit(Request $request, string $action, Outlet $outlet, ?array $before, array $after): void
    {
        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => Outlet::class,
            'auditable_id' => $outlet->id,
            'before_values' => $before,
            'after_values' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
