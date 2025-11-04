<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestaurantTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $tableId = $this->route('table')?->id ?? null;

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('restaurant_tables', 'name')->ignore($tableId)],
            'seats' => ['required', 'integer', 'min:1', 'max:20'],
            'area_name' => ['nullable', 'string', 'max:120'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
            'status' => ['required', Rule::in(['available', 'unavailable'])],
        ];
    }
}