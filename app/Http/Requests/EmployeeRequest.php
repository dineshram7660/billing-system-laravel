<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
            'employee_name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'card_number' => ['nullable', 'string', 'max:255'],
            'pf_number' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
            'designation_id' => ['required', 'exists:designation,id'],
        ];
    }
}
