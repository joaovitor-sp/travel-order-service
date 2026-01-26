<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListTravelOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:requested,approved,canceled'],
            'destination' => ['nullable', 'string', 'max:255'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'departure_date_from' => ['nullable', 'date'],
            'departure_date_to' => ['nullable', 'date', 'after_or_equal:departure_date_from'],
            'return_date_from' => ['nullable', 'date'],
            'return_date_to' => ['nullable', 'date', 'after_or_equal:return_date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
