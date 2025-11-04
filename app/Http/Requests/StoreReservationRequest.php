<?php

namespace App\Http\Requests;

use App\Models\BookingClosure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceKeys = array_keys(config('reservations.visit_purposes', []));

        return [
            'service_type' => ['required', Rule::in($serviceKeys)],
            'number_of_people' => ['required', 'integer', 'min:1', 'max:12'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'reservation_time' => ['required', 'date_format:H:i'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:150'],
            'event' => ['nullable', 'string', 'max:150'],
            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string', 'max:120'],
            'message' => ['nullable', 'string', 'max:1000'],
            'marketing_opt_in' => ['sometimes', 'boolean'],
            'guest_id' => ['nullable', 'integer', 'exists:guests,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'reservation_time.date_format' => __('Please select a valid time.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'marketing_opt_in' => filter_var(
                $this->input('marketing_opt_in'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ?? false,
        ]);
    }

    public function ensureBookingIsAvailable(): void
    {
        $closure = BookingClosure::active()->first();

        if ($closure) {
            abort(409, __('Reservations are temporarily unavailable.'));
        }
    }
}
