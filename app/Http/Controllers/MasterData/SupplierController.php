<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreSupplierRequest;
use App\Http\Requests\MasterData\UpdateSupplierRequest;
use App\Models\AuditLog;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $statusQuery = $request->query('status');
        $status = is_string($statusQuery) && in_array($statusQuery, ['aktif', 'nonaktif'], true) ? $statusQuery : 'semua';
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;

        return Inertia::render('master-data/Suppliers', [
            'filters' => ['search' => $search, 'status' => $status, 'per_page' => (string) $perPage],
            'suppliers' => Supplier::query()
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
                ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
                ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
                ->orderBy('code')
                ->paginate($perPage)
                ->withQueryString(),
        ]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $supplier = Supplier::create($request->validated());
            $this->audit($request, 'suppliers.create', $supplier, null, $supplier->getAttributes());
        });

        return to_route('master-data.suppliers.index');
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        abort_unless($supplier->updated_at?->toJSON() === $request->validated('updated_at'), 409, 'Data supplier sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $supplier) {
            $before = $supplier->getAttributes();
            $supplier->update($request->safe()->except('updated_at'));
            $this->audit($request, 'suppliers.update', $supplier, $before, $supplier->getAttributes());
        });

        return to_route('master-data.suppliers.index');
    }

    public function toggle(Request $request, Supplier $supplier): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($supplier->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data supplier sudah berubah. Muat ulang sebelum menyimpan.');

        DB::transaction(function () use ($request, $supplier) {
            $before = $supplier->getAttributes();
            $supplier->update(['is_active' => ! $supplier->is_active]);
            $this->audit($request, 'suppliers.toggle', $supplier, $before, $supplier->getAttributes());
        });

        return to_route('master-data.suppliers.index');
    }

    public function destroy(Request $request, Supplier $supplier): RedirectResponse
    {
        $request->validate(['updated_at' => ['required', 'date']]);
        abort_unless($request->user()?->can('master-data.update'), 403);
        abort_unless($supplier->updated_at?->toJSON() === $request->input('updated_at'), 409, 'Data supplier sudah berubah. Muat ulang sebelum menghapus.');

        DB::transaction(function () use ($request, $supplier) {
            $before = $supplier->getAttributes();
            $supplier->delete();
            $this->audit($request, 'suppliers.delete', $supplier, $before, $supplier->getAttributes());
        });

        return to_route('master-data.suppliers.index');
    }

    /** @param array<string, mixed>|null $before @param array<string, mixed> $after */
    private function audit(Request $request, string $action, Supplier $supplier, ?array $before, array $after): void
    {
        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => Supplier::class,
            'auditable_id' => $supplier->id,
            'before_values' => $before,
            'after_values' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
