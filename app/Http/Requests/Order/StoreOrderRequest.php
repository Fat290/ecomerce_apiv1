<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
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
            'buyer_id' => ['required', 'exists:users,id'],
            'shop_id' => ['required', 'exists:shops,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'confirmed', 'shipping', 'completed', 'cancelled'])],
            'shipping_address' => ['required', 'array'],
            'shipping_address.street' => ['required', 'string'],
            'shipping_address.city' => ['required', 'string'],
            'shipping_address.state' => ['nullable', 'string'],
            'shipping_address.zip' => ['nullable', 'string'],
            'shipping_address.country' => ['required', 'string'],
        ];
    }
}
