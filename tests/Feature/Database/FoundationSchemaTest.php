<?php

namespace Tests\Feature\Database;

use App\Models\Account;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\UnitOfMeasure;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_chart_of_accounts_seeder_is_idempotent(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);
        $this->seed(ChartOfAccountsSeeder::class);

        $this->assertSame(1, Account::where('code', '1-1100')->count());
        $this->assertSame('Kas Outlet', Account::where('code', '1-1100')->value('name'));
    }

    public function test_money_and_quantity_precision_are_stored_as_decimal_strings(): void
    {
        $group = ItemGroup::create([
            'code' => 'RAW-TEST',
            'name' => 'Raw Test',
            'is_active' => true,
        ]);
        $uom = UnitOfMeasure::create([
            'code' => 'KG',
            'name' => 'Kilogram',
            'base_code' => 'KG',
            'factor' => '1.000000',
            'is_active' => true,
        ]);

        $item = Item::create([
            'sku' => 'BEEF-SIRLOIN',
            'name' => 'Sirloin Beef',
            'item_type' => 'raw_material',
            'item_group_id' => $group->id,
            'base_uom_id' => $uom->id,
            'standard_cost_amount' => '250000.00',
            'avg_cost_amount' => '248500.00',
            'is_active' => true,
        ])->refresh();

        $this->assertSame('250000.00', $item->standard_cost_amount);
        $this->assertSame('248500.00', $item->avg_cost_amount);
        $this->assertSame('1.000000', $uom->refresh()->factor);
    }
}
