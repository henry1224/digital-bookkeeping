<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['sku', 'name', 'item_type', 'item_group_id', 'base_uom_id', 'standard_cost_amount', 'avg_cost_amount', 'is_active'])]
class Item extends Model
{
    use SoftDeletes;

    /** @return BelongsTo<ItemGroup, $this> */
    public function itemGroup(): BelongsTo
    {
        return $this->belongsTo(ItemGroup::class);
    }

    /** @return BelongsTo<UnitOfMeasure, $this> */
    public function baseUom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'base_uom_id');
    }

    protected function casts(): array
    {
        return [
            'standard_cost_amount' => 'decimal:2',
            'avg_cost_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
