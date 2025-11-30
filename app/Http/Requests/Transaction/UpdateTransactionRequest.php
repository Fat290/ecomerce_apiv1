<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
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
            'user_id' => ['sometimes', 'exists:users,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'type' => ['sometimes', 'string', Rule::in(['purchase', 'withdraw', 'refund'])],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'method' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(['success', 'pending', 'failed'])],
        ];
    }
}
