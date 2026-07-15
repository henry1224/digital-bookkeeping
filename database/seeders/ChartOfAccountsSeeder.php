<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['1-0000', 'Aset', 'asset', null, false, 'balance_sheet'],
            ['1-1000', 'Kas dan Bank', 'asset', '1-0000', false, 'current_asset'],
            ['1-1100', 'Kas Outlet', 'asset', '1-1000', true, 'current_asset'],
            ['1-1200', 'Bank Operasional', 'asset', '1-1000', true, 'current_asset'],
            ['1-2000', 'Piutang', 'asset', '1-0000', false, 'current_asset'],
            ['1-2100', 'Piutang Usaha', 'asset', '1-2000', true, 'current_asset'],
            ['1-3000', 'Inventory', 'asset', '1-0000', false, 'current_asset'],
            ['1-3100', 'Inventory Bahan Baku', 'asset', '1-3000', true, 'current_asset'],
            ['1-3200', 'Inventory Barang Jadi', 'asset', '1-3000', true, 'current_asset'],
            ['2-0000', 'Liabilitas', 'liability', null, false, 'balance_sheet'],
            ['2-1000', 'Utang Usaha', 'liability', '2-0000', true, 'current_liability'],
            ['2-2000', 'Utang Pajak/PB1', 'liability', '2-0000', true, 'current_liability'],
            ['3-0000', 'Ekuitas', 'equity', null, false, 'balance_sheet'],
            ['3-1000', 'Modal Pemilik', 'equity', '3-0000', true, 'equity'],
            ['3-2000', 'Laba Ditahan', 'equity', '3-0000', true, 'equity'],
            ['4-0000', 'Pendapatan', 'revenue', null, false, 'profit_loss'],
            ['4-1000', 'Penjualan Makanan', 'revenue', '4-0000', true, 'revenue'],
            ['4-2000', 'Penjualan Minuman', 'revenue', '4-0000', true, 'revenue'],
            ['5-0000', 'HPP', 'cogs', null, false, 'profit_loss'],
            ['5-1000', 'HPP Bahan Baku', 'cogs', '5-0000', true, 'cogs'],
            ['6-0000', 'Beban', 'expense', null, false, 'profit_loss'],
            ['6-1000', 'Beban Operasional Outlet', 'expense', '6-0000', true, 'operating_expense'],
            ['6-2000', 'Beban Administrasi', 'expense', '6-0000', true, 'operating_expense'],
            ['6-3000', 'Selisih Stock/Waste', 'expense', '6-0000', true, 'operating_expense'],
        ];

        foreach ($rows as [$code, $name, $type, $parentCode, $isPostable, $reportGroup]) {
            Account::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'type' => $type,
                    'parent_id' => $parentCode ? Account::where('code', $parentCode)->value('id') : null,
                    'is_postable' => $isPostable,
                    'report_group' => $reportGroup,
                    'is_active' => true,
                ],
            );
        }
    }
}
