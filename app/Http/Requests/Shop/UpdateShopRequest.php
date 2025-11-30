<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'], // 2MB max
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'status' => ['sometimes', 'string', 'in:pending,active,inactive'],
        ];
    }
}
