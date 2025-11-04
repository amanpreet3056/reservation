<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reservation_date' => ['required', 'date'],
            'reservation_time' => ['required', 'date_format:H:i'],
            'restaurant_table_id' => ['nullable', 'integer', 'exists:restaurant_tables,id'],
            'number_of_people' => ['required', 'integer', 'min:1', 'max:12'],
            'source' => ['required', Rule::in(array_keys(config('reservations.sources')))],
            'occasion' => ['nullable', Rule::in(array_keys(config('reservations.occasions')))],
            'reservation_notes' => ['nullable', 'string', 'max:1000'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'allergies' => ['nullable', 'array'],
            'allergies.*' => [Rule::in(config('reservations.allergies'))],
            'diets' => ['nullable', 'array'],
            'diets.*' => [Rule::in(config('reservations.diets'))],
        ];
    }
}