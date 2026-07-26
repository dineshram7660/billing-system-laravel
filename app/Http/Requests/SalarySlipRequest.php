<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalarySlipRequest extends FormRequest
{
    private const array MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $salarySlipId = $this->route('salary_slip')?->id;

        return [
            'employee_id' => [
                'required', 'integer', 'exists:employee,id',
                // Legacy allows only one slip per employee per
                // month/year — see add_edit_salary_slip.php.
                Rule::unique('salary_slip', 'employee_id')
                    ->where(fn ($query) => $query
                        ->where('salary_slip_month', $this->input('salary_slip_month'))
                        ->where('salary_slip_year', $this->input('salary_slip_year')))
                    ->ignore($salarySlipId),
            ],
            'salary_slip_month' => ['required', Rule::in(self::MONTHS)],
            'salary_slip_year' => ['required', 'integer', 'min:2017'],
            'day_work' => ['required', 'numeric', 'min:0'],
            'over_time' => ['required', 'numeric', 'min:0'],
            'pf_amount' => ['required', 'numeric', 'min:0'],
            'advance_payment' => ['required', 'numeric', 'min:0'],
            'professional_tax' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_id.unique' => 'A salary slip already exists for this employee for the selected month/year.',
        ];
    }
}
