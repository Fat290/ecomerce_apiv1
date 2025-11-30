<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'category_id' => ['sometimes', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // 5MB max per image
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'active', 'out_of_stock', 'hidden', 'archived'])],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'sold_count' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
