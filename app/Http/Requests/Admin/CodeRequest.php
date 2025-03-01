<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CodeRequest extends FormRequest
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
            'code' => 'required|string',
            'discount' => 'required|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'type'=> 'nullable|in:percentage,pounds',
            'status'=> 'nullable|in:active,notActive',
        ];
    }
}
