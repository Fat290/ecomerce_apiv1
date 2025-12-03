<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingAddressRequest extends FormRequest
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
            'address' => ['required', 'array'],
            'address.street' => ['required', 'string', 'max:255'],
            'address.city' => ['required', 'string', 'max:255'],
            'address.state' => ['nullable', 'string', 'max:255'],
            'address.zip_code' => ['required', 'string', 'max:20'],
            'address.country' => ['required', 'string', 'max:255'],
            'address.is_default' => ['sometimes', 'boolean'],
            'index' => ['sometimes', 'nullable', 'integer', 'min:0'], // Optional: update specific address by index
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address.required' => 'Shipping address is required.',
            'address.street.required' => 'Street address is required.',
            'address.city.required' => 'City is required.',
            'address.zip_code.required' => 'Zip code is required.',
            'address.country.required' => 'Country is required.',
        ];
    }
}
