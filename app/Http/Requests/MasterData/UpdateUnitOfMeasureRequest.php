<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitOfMeasureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.update') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $unitOfMeasure = $this->route('unitOfMeasure');

        return [
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('unit_of_measures', 'code')->ignore($unitOfMeasure)],
            'name' => ['required', 'string', 'max:100'],
            'base_code' => ['required', 'string', 'max:30', 'alpha_dash'],
            'factor' => ['required', 'decimal:0,6', 'gt:0'],
            'is_active' => ['required', 'boolean'],
            'updated_at' => ['required', 'date'],
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
