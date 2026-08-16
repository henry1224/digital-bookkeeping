<?php

namespace Database\Seeders;

use App\Models\Item;
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
            ['BEEF-SIRLOIN', 'Daging Sirloin', 'raw_material', 'RAW-MEAT', 'KG', 250000, 250000],
            ['BEEF-TENDERLOIN', 'Daging Tenderloin', 'raw_material', 'RAW-MEAT', 'KG', 285000, 285000],
            ['CHICKEN-BREAST', 'Dada Ayam', 'raw_material', 'RAW-MEAT', 'KG', 65000, 65000],
            ['SALMON-FILLET', 'Fillet Salmon', 'raw_material', 'RAW-MEAT', 'KG', 220000, 220000],
            ['POTATO', 'Kentang', 'raw_material', 'RAW-VEG', 'KG', 22000, 22000],
            ['CARROT', 'Wortel', 'raw_material', 'RAW-VEG', 'KG', 18000, 18000],
            ['BROCCOLI', 'Brokoli', 'raw_material', 'RAW-VEG', 'KG', 40000, 40000],
            ['LETTUCE', 'Selada', 'raw_material', 'RAW-VEG', 'KG', 35000, 35000],
            ['SALT', 'Garam', 'raw_material', 'RAW-GROCERY', 'KG', 12000, 12000],
            ['BLACK-PEPPER', 'Lada Hitam', 'raw_material', 'RAW-GROCERY', 'GR', 350, 350],
            ['COOKING-OIL', 'Minyak Goreng', 'raw_material', 'RAW-GROCERY', 'L', 22000, 22000],
            ['BUTTER', 'Mentega', 'raw_material', 'RAW-GROCERY', 'KG', 95000, 95000],
            ['STEAK-SIRLOIN', 'Sirloin Steak', 'menu', 'MENU-STEAK', 'PORSI', 85000, 85000],
            ['STEAK-TENDERLOIN', 'Tenderloin Steak', 'menu', 'MENU-STEAK', 'PORSI', 105000, 105000],
            ['GRILLED-CHICKEN', 'Grilled Chicken', 'menu', 'MENU-STEAK', 'PORSI', 45000, 45000],
            ['SALMON-STEAK', 'Salmon Steak', 'menu', 'MENU-STEAK', 'PORSI', 90000, 90000],
            ['MINERAL-WATER', 'Air Mineral', 'finished_good', 'MENU-BEV', 'PCS', 5000, 5000],
            ['ICED-TEA', 'Es Teh', 'menu', 'MENU-BEV', 'PORSI', 7000, 7000],
            ['ORANGE-JUICE', 'Jus Jeruk', 'menu', 'MENU-BEV', 'PORSI', 18000, 18000],
            ['CLEANING-SUPPLY', 'Perlengkapan Kebersihan', 'non_stock', 'RAW-GROCERY', 'PCS', 30000, 30000],
        ] as [$sku, $name, $type, $groupCode, $uomCode, $standardCost, $averageCost]) {
            Item::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'item_type' => $type,
                    'item_group_id' => ItemGroup::where('code', $groupCode)->valueOrFail('id'),
                    'base_uom_id' => UnitOfMeasure::where('code', $uomCode)->valueOrFail('id'),
                    'standard_cost_amount' => $standardCost,
                    'avg_cost_amount' => $averageCost,
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
