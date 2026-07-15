<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['journal_no', 'journal_date', 'source_type', 'source_id', 'status', 'total_debit_amount', 'total_credit_amount', 'posted_at', 'posted_by'])]
class Journal extends Model
{
    /** @return HasMany<JournalEntry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    protected function casts(): array
    {
        return [
            'journal_date' => 'date',
            'total_debit_amount' => 'decimal:2',
            'total_credit_amount' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }
}
