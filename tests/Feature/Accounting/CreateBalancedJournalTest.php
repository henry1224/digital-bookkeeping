<?php

namespace Tests\Feature\Accounting;

use App\Actions\CreateBalancedJournal;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\User;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CreateBalancedJournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_balanced_posted_journal(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);
        $actor = User::factory()->create();

        $journal = app(CreateBalancedJournal::class)->handle(
            [
                'journal_no' => 'JV-0001',
                'journal_date' => '2026-07-15',
                'source_type' => 'manual',
            ],
            [
                ['account_id' => Account::where('code', '1-1100')->value('id'), 'debit_amount' => '150000.00'],
                ['account_id' => Account::where('code', '4-1000')->value('id'), 'credit_amount' => '150000.00'],
            ],
            $actor,
        );

        $this->assertDatabaseHas('journals', [
            'id' => $journal->id,
            'journal_no' => 'JV-0001',
            'total_debit_amount' => '150000.00',
            'total_credit_amount' => '150000.00',
            'posted_by' => $actor->id,
        ]);
        $this->assertSame(2, JournalEntry::whereBelongsTo($journal)->count());
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'action' => 'journal.created',
            'auditable_type' => Journal::class,
            'auditable_id' => $journal->id,
        ]);
    }

    public function test_it_rejects_unbalanced_journal(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);
        $actor = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        try {
            app(CreateBalancedJournal::class)->handle(
                [
                    'journal_no' => 'JV-0002',
                    'journal_date' => '2026-07-15',
                    'source_type' => 'manual',
                ],
                [
                    ['account_id' => Account::where('code', '1-1100')->value('id'), 'debit_amount' => '150000.00'],
                    ['account_id' => Account::where('code', '4-1000')->value('id'), 'credit_amount' => '140000.00'],
                ],
                $actor,
            );
        } finally {
            $this->assertSame(0, Journal::count());
            $this->assertSame(0, AuditLog::count());
        }
    }

    public function test_it_rejects_non_postable_account(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);
        $actor = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        app(CreateBalancedJournal::class)->handle(
            [
                'journal_no' => 'JV-0003',
                'journal_date' => '2026-07-15',
                'source_type' => 'manual',
            ],
            [
                ['account_id' => Account::where('code', '1-0000')->value('id'), 'debit_amount' => '150000.00'],
                ['account_id' => Account::where('code', '4-1000')->value('id'), 'credit_amount' => '150000.00'],
            ],
            $actor,
        );
    }

    public function test_it_rejects_debit_and_credit_on_same_line(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);
        $actor = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        app(CreateBalancedJournal::class)->handle(
            [
                'journal_no' => 'JV-0004',
                'journal_date' => '2026-07-15',
                'source_type' => 'manual',
            ],
            [
                ['account_id' => Account::where('code', '1-1100')->value('id'), 'debit_amount' => '150000.00', 'credit_amount' => '1.00'],
                ['account_id' => Account::where('code', '4-1000')->value('id'), 'credit_amount' => '150000.00'],
            ],
            $actor,
        );
    }
}
