<?php

namespace Systha\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InspectionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // other fields
            'vendorCode' => 'required|string|exists:vendors,vendor_code',
            'note' => 'nullable|string',

            // contact info
            'contact' => 'required|array',
            'contact.first_name' => 'required|string|max:255',
            'contact.last_name' => 'required|string|max:255',
            'contact.phone' => 'required|string|max:50',
            'contact.email' => 'required|email|max:255',

            // service address
            'service_area' => 'required|array',
            'service_area.line_1' => 'required|string|max:255',
            'service_area.line_2' => 'required|string|max:255',
            'service_area.city' => 'required|string|max:255',
            'service_area.state' => 'required|string|max:255',
            'service_area.zip' => 'required|string|max:50',

            // photos
            'photos' => 'nullable|array',
            'photos.*' => 'file|image|max:5120',
            'photo_descriptions' => 'nullable|array',
            'photo_descriptions.*' => 'nullable|string|max:500',
        ];
    }
}
