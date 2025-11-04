<?php

namespace App\Http\Requests;

use App\Models\BookingClosure;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationStepOneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number_of_people' => ['required', 'integer', 'min:1', 'max:12'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'reservation_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'reservation_time.date_format' => __('Please select a valid time.'),
        ];
    }

    public function ensureBookingIsAvailable(): void
    {
        $closure = BookingClosure::active()->first();

        if ($closure) {
            abort(409, __('Reservations are temporarily unavailable.'));
        }
    }
}
