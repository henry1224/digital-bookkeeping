<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('items', 'sku')],
            'name' => ['required', 'string', 'max:150'],
            'item_type' => ['required', Rule::in(['raw_material', 'finished_good', 'menu', 'non_stock'])],
            'item_group_id' => ['required', Rule::exists('item_groups', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'base_uom_id' => ['required', Rule::exists('unit_of_measures', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'standard_cost_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999999.99'],
            'avg_cost_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999999.99'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => strtoupper(trim((string) $this->input('sku'))),
            'name' => trim((string) $this->input('name')),
        ]);
    }
}
