<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
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
            'buyer_id' => ['sometimes', 'exists:users,id'],
            'shop_id' => ['sometimes', 'exists:shops,id'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.qty' => ['required_with:items', 'integer', 'min:1'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'confirmed', 'shipping', 'completed', 'cancelled'])],
            'shipping_address' => ['sometimes', 'array'],
            'shipping_address.street' => ['required_with:shipping_address', 'string'],
            'shipping_address.city' => ['required_with:shipping_address', 'string'],
            'shipping_address.state' => ['nullable', 'string'],
            'shipping_address.zip' => ['nullable', 'string'],
            'shipping_address.country' => ['required_with:shipping_address', 'string'],
        ];
    }
}
