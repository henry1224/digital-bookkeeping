<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitOfMeasureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('unit_of_measures', 'code')],
            'name' => ['required', 'string', 'max:100'],
            'base_code' => ['required', 'string', 'max:30', 'alpha_dash'],
            'factor' => ['required', 'decimal:0,6', 'gt:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'base_code' => strtoupper(trim((string) $this->input('base_code'))),
        ]);
    }
}
