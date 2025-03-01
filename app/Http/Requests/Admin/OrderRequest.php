<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cart_id' => 'required|exists:carts,id',
            'code'=>'nullable|exists:codes,code',
            'name'=> 'required|string',
            'phoNum'=> 'required|string',
            'address'=> 'required|string',
            'details'=> 'nullable|string',
            // 'discount'=> 'nullable|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            // 'shippingCost'=> 'nullable|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'status'=> 'nullable|in:pending,approve,compeleted,canceled',
            'creationDate'=> 'nullable|date_format:Y-m-d H:i:s',
            // 'products' => 'required|array',
            // 'products.*.id' => 'required|exists:products,id',
            // 'products.*.quantity' => 'required|integer|min:1',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }
}
