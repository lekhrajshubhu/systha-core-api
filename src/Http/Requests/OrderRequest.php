<?php

namespace Systha\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            "first_name" => 'required',
            "last_name" => 'required',
            "email" => 'required|email',
            "phone" => 'required|numeric|max:9999999999|min:999999999',
            "delivery_city" => 'required',
            "delivery_state" => 'required',
            "delivery_zip" => 'required|numeric',
            // "delivery_zip" => 'required|numeric|min:9999|max:99999',
            "delivery_addr1" => 'required',
            "delivery_date" => 'required|date',
            "delivery_time" => 'required',
            "pickup_city" => 'required',
            "pickup_state" => 'required',
            "pickup_zip" => 'required|numeric',
            // "pickup_zip" => 'required|numeric|min:9999|max:99999',
            "pickup_addr1" => 'required',
            "pickup_date" => 'required|date',
            "pickup_time" => 'required',

            "card" => 'required_without:customer_profile|numeric|min:999999999999999|max:9999999999999999',
            "code" => 'required_without:customer_profile|numeric|min:99|max:999',
            "expm" => 'required_without:customer_profile',
            "expy" => 'required_without:customer_profile',
            "name_per_card" => 'required_without:customer_profile',
            "zip" => 'required_without:customer_profile',
        ];
    }
}
