<?php

namespace App\Http\Requests\Voucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVoucherRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:255', 'unique:vouchers,code'],
            'discount_type' => ['required', 'string', Rule::in(['percent', 'amount'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'shop_id' => ['required', 'exists:shops,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['nullable', 'string', Rule::in(['active', 'expired', 'disabled'])],
        ];
    }
}
