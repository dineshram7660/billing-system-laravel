<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IncomeRequest extends FormRequest
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
            'd_id' => ['nullable', 'exists:department,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'income_date' => ['required', 'date'],
        ];
    }
}
