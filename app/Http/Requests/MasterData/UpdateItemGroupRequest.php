<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.update') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $itemGroup = $this->route('itemGroup');

        return [
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('item_groups', 'code')->ignore($itemGroup)],
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'integer', Rule::notIn([$itemGroup?->id]), Rule::exists('item_groups', 'id')->whereNull('deleted_at')],
            'inventory_account_code' => ['nullable', 'string', 'max:30', Rule::exists('accounts', 'code')->where('is_active', true)->whereNull('deleted_at')],
            'revenue_account_code' => ['nullable', 'string', 'max:30', Rule::exists('accounts', 'code')->where('is_active', true)->whereNull('deleted_at')],
            'is_active' => ['required', 'boolean'],
            'updated_at' => ['required', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'parent_id' => $this->input('parent_id') ?: null,
            'inventory_account_code' => $this->input('inventory_account_code') ?: null,
            'revenue_account_code' => $this->input('revenue_account_code') ?: null,
        ]);
    }
}
