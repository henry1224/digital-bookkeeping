<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\PostingRule;
use Illuminate\Database\Seeder;

class PostingRuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['daily_sale_cash', '1-1100', '4-1000', null],
            ['daily_sale_pb1', '1-1100', '2-2000', 'pb1_enabled'],
            ['receiving', '1-3100', '2-1000', null],
            ['payment_execution', '2-1000', '1-1200', 'supplier_payment'],
            ['payment_execution', '6-1000', '1-1200', 'non_stock_expense'],
            ['stock_usage', '5-1000', '1-3100', null],
            ['stock_adjustment_in', '1-3100', '6-3000', null],
            ['stock_adjustment_out', '6-3000', '1-3100', null],
            ['manual_journal', null, null, null],
        ] as [$sourceType, $debitCode, $creditCode, $conditionKey]) {
            PostingRule::updateOrCreate(
                ['source_type' => $sourceType, 'condition_key' => $conditionKey],
                [
                    'debit_account_id' => $debitCode ? Account::where('code', $debitCode)->value('id') : null,
                    'credit_account_id' => $creditCode ? Account::where('code', $creditCode)->value('id') : null,
                    'is_active' => true,
                ],
            );
        }
    }
}
