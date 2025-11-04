<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationStepTwoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:150'],
            'event' => ['nullable', 'string', 'max:150'],
            'visit_purpose' => ['required', Rule::in(array_keys(config('reservations.visit_purposes')))],
            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string', 'max:120'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
