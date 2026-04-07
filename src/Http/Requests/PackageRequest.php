<?php

namespace Systha\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'=>'nullable|integer',
            'package_name' => 'required|string|max:255',
            'image' => 'nullable|image',
            'description' => 'nullable|string',
            'coupon_id' => 'nullable|integer|exists:coupons,id',
            'service_id' => 'required|array|min:1',
            'service_id.*' => 'required|integer|exists:services,id',
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',
            'sub_total' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'service_id.required' => 'At least one service must be selected.',
            'service_id.*.exists' => 'Selected service is invalid.',
            'price.*.numeric' => 'Each price must be a number.',
        ];
    }
}
