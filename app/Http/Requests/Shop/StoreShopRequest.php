<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShopRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'], // 2MB max
            'banner' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
            'description' => ['nullable', 'string'],
            'business_type_id' => ['required', 'string', Rule::exists('business_types', 'id')->where('is_active', true)],
            'join_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ];
    }
}
