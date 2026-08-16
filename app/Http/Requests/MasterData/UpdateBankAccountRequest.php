<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'outlet_id' => ['nullable', Rule::exists('outlets', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('bank_accounts', 'code')->ignore($this->route('bankAccount'))],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_no' => ['required', 'string', 'max:100'],
            'account_name' => ['required', 'string', 'max:150'],
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('type', 'asset')->where('is_postable', true)->where('is_active', true)->whereNull('deleted_at')],
            'is_active' => ['required', 'boolean'],
            'updated_at' => ['required', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->input('code'))), 'outlet_id' => $this->filled('outlet_id') ? $this->input('outlet_id') : null]);
    }
}
