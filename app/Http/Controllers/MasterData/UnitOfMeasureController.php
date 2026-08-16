<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreUnitOfMeasureRequest;
use App\Http\Requests\MasterData\UpdateUnitOfMeasureRequest;
use App\Models\AuditLog;
use App\Models\UnitOfMeasure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UnitOfMeasureController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $statusQuery = $request->query('status');
        $status = is_string($statusQuery) && in_array($statusQuery, ['aktif', 'nonaktif'], true) ? $statusQuery : 'semua';

        return Inertia::render('master-data/UnitOfMeasures', [
            'filters' => ['search' => $search, 'status' => $status],
            'units' => UnitOfMeasure::query()
                ->withCount('items')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('base_code', 'like', "%{$search}%");
                }))
                ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
                ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
                ->orderBy('code')
                ->paginate(10)
                ->withQueryString()
                ->through(fn (UnitOfMeasure $unit): array => [
                    'id' => $unit->id,
                    'code' => $unit->code,
                    'name' => $unit->name,
                    'base_code' => $unit->base_code,
                    'factor' => $unit->factor,
                    'is_active' => $unit->is_active,
                    'items_count' => $unit->items_count,
                    'updated_at' => $unit->updated_at?->toJSON(),
                ]),
        ]);
    }

    public function store(StoreUnitOfMeasureRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $unit = UnitOfMeasure::create($request->validated());

            $this->audit($request, 'unit-of-measures.create', $unit, null, $unit->getAttributes());
        });

        return to_route('master-data.uom.index');
    }

    public function update(UpdateUnitOfMeasureRequest $request, UnitOfMeasure $unitOfMeasure): RedirectResponse
    {
        abort_unless($unitOfMeasure->updated_at?->toJSON() === $request->validated('updated_at'), 409, 'Data satuan sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $unitOfMeasure) {
            $before = $unitOfMeasure->getAttributes();

            $unitOfMeasure->update($request->safe()->except('updated_at'));

            $this->audit($request, 'unit-of-measures.update', $unitOfMeasure, $before, $unitOfMeasure->getAttributes());
        });

        return to_route('master-data.uom.index');
    }

    public function toggle(Request $request, UnitOfMeasure $unitOfMeasure): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($unitOfMeasure->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data satuan sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $unitOfMeasure) {
            $before = $unitOfMeasure->getAttributes();

            $unitOfMeasure->update(['is_active' => ! $unitOfMeasure->is_active]);

            $this->audit($request, 'unit-of-measures.toggle', $unitOfMeasure, $before, $unitOfMeasure->getAttributes());
        });

        return to_route('master-data.uom.index');
    }

    public function destroy(Request $request, UnitOfMeasure $unitOfMeasure): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($unitOfMeasure->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data satuan sudah berubah. Muat ulang sebelum menghapus.');

        if ($unitOfMeasure->items()->exists()) {
            throw ValidationException::withMessages([
                'unit_of_measure' => 'Satuan masih dipakai oleh item dan tidak dapat dihapus.',
            ]);
        }

        DB::transaction(function () use ($request, $unitOfMeasure) {
            $before = $unitOfMeasure->getAttributes();

            $unitOfMeasure->delete();

            $this->audit($request, 'unit-of-measures.delete', $unitOfMeasure, $before, $unitOfMeasure->getAttributes());
        });

        return to_route('master-data.uom.index');
    }

    /** @param array<string, mixed>|null $before @param array<string, mixed> $after */
    private function audit(Request $request, string $action, UnitOfMeasure $unit, ?array $before, array $after): void
    {
        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => UnitOfMeasure::class,
            'auditable_id' => $unit->id,
            'before_values' => $before,
            'after_values' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
