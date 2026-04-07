<?php

namespace Systha\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add your auth logic if needed
    }

    public function rules(): array
    {
        return [
            'enq_id' => 'required|exists:quote_enqs,id',
            'expiry_date' => 'required|date_format:Y-m-d|after:today',
            'description' => 'nullable|string|max:2000',
            'quote_number' => 'required|string|unique:quotes,quote_number',
            
            'service_id' => 'required|array|min:1',
            'service_id.*' => 'required|integer|exists:services,id',

            'price' => 'required|array',
            'price.*' => 'required|numeric|min:0',

            'amend_charge' => 'required|array',
            'amend_charge.*' => 'nullable|numeric|min:0',

            'cancel_charge' => 'required|array',
            'cancel_charge.*' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Please select at least one service.',
            'service_id.*.exists' => 'One or more selected services are invalid.',
            'expiry_date.after' => 'Expiry date must be in the future.',
        ];
    }
}
