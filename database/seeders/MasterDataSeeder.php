<?php

namespace Database\Seeders;

use App\Models\ItemGroup;
use App\Models\Outlet;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['BPN-A', 'Balikpapan A', 'outlet', true],
            ['BPN-B', 'Balikpapan B', 'outlet', false],
            ['CK-01', 'Dapur Pusat', 'central_kitchen', false],
        ] as [$code, $name, $type, $pb1Enabled]) {
            $outlet = Outlet::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'outlet_type' => $type, 'timezone' => 'Asia/Makassar', 'is_active' => true],
            );

            $outlet->config()->updateOrCreate(
                ['outlet_id' => $outlet->id],
                ['pb1_enabled' => $pb1Enabled, 'pb1_rate' => $pb1Enabled ? '10.0000' : '0.0000'],
            );
        }

        foreach ([
            ['KG', 'Kilogram', 'KG', '1.000000'],
            ['GR', 'Gram', 'KG', '0.001000'],
            ['L', 'Liter', 'L', '1.000000'],
            ['ML', 'Mililiter', 'L', '0.001000'],
            ['PCS', 'Pieces', 'PCS', '1.000000'],
            ['DUS', 'Dus', 'PCS', '1.000000'],
            ['PORSI', 'Porsi', 'PORSI', '1.000000'],
        ] as [$code, $name, $baseCode, $factor]) {
            UnitOfMeasure::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'base_code' => $baseCode, 'factor' => $factor, 'is_active' => true],
            );
        }

        foreach ([
            ['RAW-MEAT', 'Daging', '1-3100', null],
            ['RAW-VEG', 'Sayur', '1-3100', null],
            ['RAW-GROCERY', 'Grocery/Bumbu', '1-3100', null],
            ['MENU-STEAK', 'Menu Steak', null, '4-1000'],
            ['MENU-BEV', 'Menu Minuman', null, '4-2000'],
        ] as [$code, $name, $inventoryAccountCode, $revenueAccountCode]) {
            ItemGroup::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'inventory_account_code' => $inventoryAccountCode,
                    'revenue_account_code' => $revenueAccountCode,
                    'is_active' => true,
                ],
            );
        }
    }
}
