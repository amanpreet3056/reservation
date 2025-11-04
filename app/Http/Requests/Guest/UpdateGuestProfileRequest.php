<?php

namespace App\Http\Requests\Guest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuestProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('guest') !== null;
    }

    public function rules(): array
    {
        $guestId = $this->user('guest')?->id;

        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('guests', 'email')->ignore($guestId),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string', 'max:120'],
            'marketing_opt_in' => ['sometimes', 'boolean'],
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
}

