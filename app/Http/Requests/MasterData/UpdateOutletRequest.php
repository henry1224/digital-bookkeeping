<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master-data.update') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $outlet = $this->route('outlet');

        return [
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('outlets', 'code')->ignore($outlet)],
            'name' => ['required', 'string', 'max:150'],
            'outlet_type' => ['required', Rule::in(['outlet', 'central_kitchen'])],
            'timezone' => ['required', 'string', 'max:50', Rule::in(['Asia/Makassar'])],
            'is_active' => ['required', 'boolean'],
            'updated_at' => ['required', 'date'],
        ];
    }
}
