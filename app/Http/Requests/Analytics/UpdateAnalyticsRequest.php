<?php

namespace App\Http\Requests\Analytics;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnalyticsRequest extends FormRequest
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
        $analyticsId = $this->route('analytics') ?? $this->route('id');

        return [
            'date' => ['sometimes', 'date', 'unique:analytics,date,' . $analyticsId],
            'total_orders' => ['nullable', 'integer', 'min:0'],
            'total_revenue' => ['nullable', 'numeric', 'min:0'],
            'new_users' => ['nullable', 'integer', 'min:0'],
            'top_products' => ['nullable', 'array'],
            'top_products.*' => ['exists:products,id'],
        ];
    }
}
