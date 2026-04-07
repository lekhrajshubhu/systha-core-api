<?php

namespace Systha\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust if needed for permission control
    }

    public function rules(): array
    {
        return [
            'description' => 'nullable|string',
            'images' => 'nullable|array',

            'preferred_date' => 'nullable|date',
            'preferred_time' => 'nullable',

            'contact' => 'required|array',
            'contact.fname' => 'required|string',
            'contact.lname' => 'required|string',
            'contact.email' => 'required|email',
            'contact.phone_no' => 'required|string',

            'address' => 'required|array',
            'address.add1' => 'required|string',
            'address.add2' => 'nullable|string',
            'address.city' => 'required|string',
            'address.state' => 'required|string',
            'address.zip' => 'required|string',
            'address.country' => 'nullable|string',

            'reviewable_history' => 'nullable|array',
            'service_selected' => 'required|array',
            'is_emergency' => 'nullable',
            
            'payment_method_id' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'stripe_customer_id' => 'nullable|string',
            'vendor_code' => 'nullable|string|exists:vendors,vendor_code'
        ];
    }
}
