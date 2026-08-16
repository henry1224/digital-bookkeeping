<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'base_code', 'factor', 'is_active'])]
class UnitOfMeasure extends Model
{
    use SoftDeletes;

    /** @return HasMany<Item, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'base_uom_id');
    }

    protected function casts(): array
    {
        return [
            'factor' => 'decimal:6',
            'is_active' => 'boolean',
        ];
    }
}
