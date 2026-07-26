<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The legacy add/edit bill form has essentially no server-side validation
 * (see the migration research notes) — this is a deliberate improvement
 * over that, not a behavior-preserving port. Invoice number uniqueness is
 * still NOT enforced here, matching legacy behavior: historical data
 * already contains gaps/duplicates that would need auditing first.
 */
class BillRequest extends FormRequest
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
            'invoice_no' => ['nullable', 'integer'],
            'subject' => ['required', 'string', 'max:255'],
            'gst_no' => ['nullable', 'string', 'max:255'],
            'ref_no' => ['nullable', 'string', 'max:255'],
            'ref_date' => ['nullable', 'date'],
            'bill_date' => ['required', 'date'],
            'd_id' => ['nullable', 'exists:department,id'],
            'sir_name' => ['nullable', 'string', 'max:255'],
            'remark' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'bill_state' => ['nullable', 'string', 'max:255'],
            'gst_bill' => ['required', 'boolean'],
            'paid' => ['required', 'boolean'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_date' => ['nullable', 'date'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:product,id'],
            'items.*.service_no' => ['nullable', 'string', 'max:255'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.hsn_code' => ['nullable', 'string', 'max:255'],
            'items.*.per_unit' => ['nullable', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'numeric', 'min:0'],
            'items.*.total' => ['required', 'numeric', 'min:0'],
        ];
    }
}
