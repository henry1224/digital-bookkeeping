<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outlet_id', 'pb1_enabled', 'pb1_rate', 'default_cash_account_id', 'default_bank_account_id'])]
class OutletConfig extends Model
{
    /** @return BelongsTo<Outlet, $this> */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pb1_enabled' => 'boolean',
            'pb1_rate' => 'decimal:4',
        ];
    }
}
