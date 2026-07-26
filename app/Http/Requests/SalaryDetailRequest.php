<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SalaryDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'par_day_amount' => ['required', 'numeric', 'min:0'],
            'per_day_extra' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
        ];
    }
}
