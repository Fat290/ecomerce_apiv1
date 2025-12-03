<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
            'shipping_address' => ['required', 'array'],
            'shipping_address.street' => ['required', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:255'],
            'shipping_address.state' => ['nullable', 'string', 'max:255'],
            'shipping_address.zip_code' => ['nullable', 'string', 'max:20'],
            'shipping_address.country' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'max:255'],
            'shipping_voucher_id' => ['nullable', 'exists:vouchers,id'],
            'product_voucher_id' => ['nullable', 'exists:vouchers,id'],
            'shop_vouchers' => ['nullable', 'array'],
            'shop_vouchers.*.shop_id' => ['required_with:shop_vouchers', 'exists:shops,id'],
            'shop_vouchers.*.voucher_id' => ['required_with:shop_vouchers', 'exists:vouchers,id'],
        ];
    }
}
