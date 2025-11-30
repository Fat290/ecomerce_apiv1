<?php

namespace App\Http\Requests\Voucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVoucherRequest extends FormRequest
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
        $voucherId = $this->route('voucher') ?? $this->route('id');

        return [
            'code' => ['sometimes', 'string', 'max:255', Rule::unique('vouchers', 'code')->ignore($voucherId)],
            'discount_type' => ['sometimes', 'string', Rule::in(['percent', 'amount'])],
            'discount_value' => ['sometimes', 'numeric', 'min:0'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'shop_id' => ['sometimes', 'exists:shops,id'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'expired', 'disabled'])],
        ];
    }
}
