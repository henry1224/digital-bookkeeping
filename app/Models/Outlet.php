<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'outlet_type', 'timezone', 'is_active'])]
class Outlet extends Model
{
    use SoftDeletes;

    /** @return HasOne<OutletConfig, $this> */
    public function config(): HasOne
    {
        return $this->hasOne(OutletConfig::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
