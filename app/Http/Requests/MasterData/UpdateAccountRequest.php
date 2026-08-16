<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.update') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $account = $this->route('account');

        return [
            'code' => ['required', 'string', 'max:30', 'regex:/^[0-9A-Z-]+$/', Rule::unique('accounts', 'code')->ignore($account)],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(['asset', 'liability', 'equity', 'revenue', 'cogs', 'expense'])],
            'parent_id' => ['nullable', 'integer', Rule::notIn([$account?->id]), Rule::exists('accounts', 'id')->whereNull('deleted_at')],
            'is_postable' => ['required', 'boolean'],
            'report_group' => ['nullable', Rule::in(['balance_sheet', 'current_asset', 'current_liability', 'equity', 'profit_loss', 'revenue', 'cogs', 'operating_expense'])],
            'is_active' => ['required', 'boolean'],
            'updated_at' => ['required', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'parent_id' => $this->input('parent_id') ?: null,
            'report_group' => $this->input('report_group') ?: null,
        ]);
    }
}
