<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReservationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_map(fn (ReservationStatus $status) => $status->value, ReservationStatus::cases()))],
            'reservation_notes' => ['nullable', 'string', 'max:1000'],
            'cancel_reason' => ['nullable', 'string', 'max:1000', Rule::requiredIf(fn () => $this->input('status') === ReservationStatus::Cancelled->value)],
            'restaurant_table_id' => ['nullable', 'integer', 'exists:restaurant_tables,id'],
        ];
    }
}
