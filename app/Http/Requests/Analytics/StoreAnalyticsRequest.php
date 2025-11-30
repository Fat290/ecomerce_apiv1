<?php

namespace App\Http\Requests\Analytics;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnalyticsRequest extends FormRequest
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
            'date' => ['required', 'date', 'unique:analytics,date'],
            'total_orders' => ['nullable', 'integer', 'min:0'],
            'total_revenue' => ['nullable', 'numeric', 'min:0'],
            'new_users' => ['nullable', 'integer', 'min:0'],
            'top_products' => ['nullable', 'array'],
            'top_products.*' => ['exists:products,id'],
        ];
    }
}
