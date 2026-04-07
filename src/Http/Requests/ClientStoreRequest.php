<?php

namespace Systha\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => 'nullable|image|max:2048',
            'fname'  => 'required|string|max:255',
            'lname'  => 'required|string|max:255',
            'email'  => 'nullable|email|max:255',
            'email2'  => 'nullable|email|max:255',
            'phone1' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
        ];
    }
}
