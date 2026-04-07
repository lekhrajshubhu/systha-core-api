<?php

namespace Systha\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InquiryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_code' => 'required|string|exists:vendors,vendor_code',
            'selected_items' => 'required|array',

            'contact' => 'required|array',
            'contact.first_name' => 'required|string',
            'contact.last_name' => 'required|string',
            'contact.phone' => 'required|string',
            'contact.email' => 'required|email',

            'address' => 'required|array',
            'address.line_1' => 'required|string',
            'address.line_2' => 'required|string',
            'address.city' => 'required|string',
            'address.state' => 'required|string',
            'address.zip' => 'required|string',

            'note' => 'nullable|string',
        ];
    }
}
