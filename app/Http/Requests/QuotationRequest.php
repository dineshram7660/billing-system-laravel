<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
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
            'quotation_to' => ['nullable', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'particulars' => ['nullable', 'string'],
            'unit' => ['nullable', 'string', 'max:255'],
            'total' => ['required', 'numeric', 'min:0'],
            'bill_date' => ['required', 'date'],
        ];
    }
}
