<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.options' => ['nullable', 'array'],
            'variants.*.options.*' => ['string', 'max:150'],
            'variants.*.is_required' => ['nullable', 'boolean'],
            'variants.*.position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_array($this->variants)) {
            $normalized = array_map(function ($variant) {
                if (isset($variant['options']) && is_string($variant['options'])) {
                    $variant['options'] = array_values(array_filter(array_map('trim', explode(',', $variant['options']))));
                }
                return $variant;
            }, $this->variants);

            $this->merge(['variants' => $normalized]);
        }
    }
}
