<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
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
            'expenses_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
