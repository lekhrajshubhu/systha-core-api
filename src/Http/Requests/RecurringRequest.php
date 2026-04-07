<?php

namespace Systha\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecurringRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_code' => 'required|string|exists:vendors,vendor_code',
            'package_id' => 'required|integer|exists:packages,id',
            'plan_id' => 'required|integer|exists:package_types,id',

            'contact' => 'required|array',
            'contact.first_name' => 'required|string',
            'contact.last_name' => 'required|string',
            'contact.email' => 'nullable|email',
            'contact.email2' => 'nullable|email',
            'contact.phone' => 'nullable|string',
            'contact.phone2' => 'nullable|string',

            'address' => 'required|array',
            'address.line_1' => 'required|string',
            'address.line_2' => 'nullable|string',
            'address.city' => 'required|string',
            'address.state' => 'nullable|string',
            'address.zip' => 'nullable|string',
            'address.country' => 'nullable|string',
            'address.lat' => 'nullable|numeric',
            'address.lng' => 'nullable|numeric',

            'stripe' => 'required|array',
            'stripe.id' => 'required|string',
            'stripe.brand' => 'required|string',
            'stripe.last4' => 'required|string',
            'stripe.expMonth' => 'required|integer|min:1|max:12',
            'stripe.expYear' => 'required|integer',

            'note' => 'nullable|string',
        ];
    }
}