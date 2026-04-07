<?php

namespace Systha\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'     => 'nullable|integer|exists:addresses,id',
            'add1'    => 'required|string|max:255',
            'add2'    => 'nullable|string|max:255',
            'city'    => 'required|string|max:255',
            'county'  => 'nullable|string|max:255',
            'state'   => 'required|string|max:255',
            'zip'     => 'required|string|max:20',
            'country' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'lat'     => 'nullable|numeric',
            'lon'     => 'nullable|numeric',
        ];
    }
}
