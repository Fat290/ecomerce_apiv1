<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
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
            'seller_id' => ['required', 'exists:users,id', 'different:buyer_id'],
            'messages' => ['nullable', 'array'],
            'last_message' => ['nullable', 'string'],
        ];
    }
}
