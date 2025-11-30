<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'type' => ['required', 'string', Rule::in(['purchase', 'withdraw', 'refund'])],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['success', 'pending', 'failed'])],
        ];
    }
}
