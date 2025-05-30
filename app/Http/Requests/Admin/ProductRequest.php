<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProductRequest extends FormRequest
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
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'quantity'=> 'required|string',
            'priceBeforeDiscount'=> 'nullable|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'sellingPrice' => 'required|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'purchesPrice' => 'required|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'image.*'=>'nullable|image|mimes:jpg,jpeg,png,gif,svg',
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
