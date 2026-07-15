<?php

namespace App\Actions;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateBalancedJournal
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array<string, mixed>>  $entries
     */
    public function handle(array $attributes, array $entries, User $actor): Journal
    {
        if (count($entries) < 2) {
            throw new InvalidArgumentException('Journal must have at least two entries.');
        }

        $normalized = $this->normalizeEntries($entries);
        $totalDebit = array_sum(array_column($normalized, 'debit_cents'));
        $totalCredit = array_sum(array_column($normalized, 'credit_cents'));

        if ($totalDebit !== $totalCredit) {
            throw new InvalidArgumentException('Journal debit and credit must balance.');
        }

        return DB::transaction(function () use ($attributes, $normalized, $actor, $totalDebit, $totalCredit) {
            $journal = Journal::create([
                'journal_no' => $attributes['journal_no'],
                'journal_date' => $attributes['journal_date'],
                'source_type' => $attributes['source_type'],
                'source_id' => $attributes['source_id'] ?? null,
                'status' => $attributes['status'] ?? 'posted',
                'total_debit_amount' => $this->formatCents($totalDebit),
                'total_credit_amount' => $this->formatCents($totalCredit),
                'posted_at' => $attributes['posted_at'] ?? now(),
                'posted_by' => $actor->id,
            ]);

            foreach ($normalized as $entry) {
                $journal->entries()->create([
                    'account_id' => $entry['account_id'],
                    'debit_amount' => $this->formatCents($entry['debit_cents']),
                    'credit_amount' => $this->formatCents($entry['credit_cents']),
                    'description' => $entry['description'],
                ]);
            }

            AuditLog::create([
                'actor_id' => $actor->id,
                'action' => 'journal.created',
                'auditable_type' => Journal::class,
                'auditable_id' => $journal->id,
                'before_values' => null,
                'after_values' => ['journal_no' => $journal->journal_no, 'status' => $journal->status],
            ]);

            return $journal->load('entries');
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, array{account_id:int, debit_cents:int, credit_cents:int, description:?string}>
     */
    private function normalizeEntries(array $entries): array
    {
        return array_map(function (array $entry): array {
            $account = Account::query()
                ->whereKey($entry['account_id'] ?? null)
                ->where('is_active', true)
                ->where('is_postable', true)
                ->first();

            if (! $account) {
                throw new InvalidArgumentException('Journal account must be active and postable.');
            }

            $debit = $this->toCents($entry['debit_amount'] ?? '0');
            $credit = $this->toCents($entry['credit_amount'] ?? '0');

            if ($debit < 0 || $credit < 0) {
                throw new InvalidArgumentException('Journal amounts cannot be negative.');
            }

            if (($debit > 0 && $credit > 0) || ($debit === 0 && $credit === 0)) {
                throw new InvalidArgumentException('Journal entry must have either debit or credit.');
            }

            return [
                'account_id' => $account->id,
                'debit_cents' => $debit,
                'credit_cents' => $credit,
                'description' => $entry['description'] ?? null,
            ];
        }, $entries);
    }

    private function toCents(mixed $amount): int
    {
        $value = trim((string) $amount);

        if (! preg_match('/^-?\d+(\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException('Money amount must have at most two decimals.');
        }

        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '-');
        [$whole, $decimal] = array_pad(explode('.', $value, 2), 2, '0');
        $cents = ((int) $whole * 100) + (int) str_pad($decimal, 2, '0');

        return $negative ? -$cents : $cents;
    }

    private function formatCents(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return $sign.intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }
}
