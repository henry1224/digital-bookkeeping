<?php

namespace Database\Seeders;

use App\Models\ItemGroup;
use App\Models\Outlet;
use App\Models\Supplier;
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

        foreach ([
            'Segar Abadi',
            'Pangan Nusantara',
            'Borneo Protein',
            'Laut Makmur',
            'Sayur Sejahtera',
            'Tani Bersama',
            'Rempah Indonesia',
            'Sumber Bumbu',
            'Dairy Prima',
            'Beverage Mandiri',
            'Kemasan Utama',
            'Kertas Bersih',
            'Higienis Sentosa',
            'Peralatan Dapur',
            'Gas Energi',
            'Frozen Food Jaya',
            'Roti Harian',
            'Buah Tropis',
            'Sembako Berkah',
            'Distribusi Kaltim',
        ] as $index => $name) {
            $number = $index + 1;

            Supplier::updateOrCreate(
                ['code' => sprintf('SUP-%03d', $number)],
                [
                    'name' => $name,
                    'phone' => sprintf('0542-700%04d', $number),
                    'email' => sprintf('supplier%02d@example.test', $number),
                    'address' => 'Balikpapan, Kalimantan Timur',
                    'is_active' => true,
                ],
            );
        }
    }
}
