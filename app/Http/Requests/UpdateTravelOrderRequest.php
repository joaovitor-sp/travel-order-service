<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class UpdateTravelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination' => ['sometimes', 'required', 'string', 'max:255'],
            'departure_date' => ['sometimes', 'required', 'date'],
            'return_date' => ['sometimes', 'required', 'date', 'after_or_equal:departure_date'],
            'status' => ['prohibited'],
        ];
    }
}