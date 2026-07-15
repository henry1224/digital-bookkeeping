<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['owner', 'Owner', false],
            ['management', 'Management', false],
            ['finance-manager', 'Finance Manager', false],
            ['finance', 'Finance', true],
            ['accounting', 'Accounting', false],
            ['costcontrol', 'Costcontrol', false],
            ['purchasing-manager', 'Purchasing Manager', false],
            ['warehouse-manager', 'Warehouse Manager', true],
            ['outlet-manager', 'Outlet Manager', true],
            ['staff-outlet', 'Staff Outlet', true],
            ['admin-it', 'Admin IT', false],
            ['auditor', 'Auditor/Read Only', false],
        ];

        foreach ($roles as [$slug, $name, $isOutletScoped]) {
            Role::updateOrCreate(['slug' => $slug], ['name' => $name, 'is_outlet_scoped' => $isOutletScoped]);
        }

        $permissions = collect(['dashboard', 'master-data', 'daily-sales', 'bank-book', 'payment-requests', 'purchase-orders', 'receiving', 'stock', 'journals', 'periods', 'reports', 'audit-logs'])
            ->flatMap(fn (string $module) => collect(['view', 'create', 'update', 'submit', 'approve', 'post', 'cancel', 'export', 'audit.view'])
                ->map(fn (string $action) => "{$module}.{$action}"));

        $permissions->each(fn (string $slug) => Permission::updateOrCreate(['slug' => $slug], ['name' => $slug]));

        Role::where('slug', 'owner')->firstOrFail()->permissions()->sync(Permission::pluck('id'));
    }
}
