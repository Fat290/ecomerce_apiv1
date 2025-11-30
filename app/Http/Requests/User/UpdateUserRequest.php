<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user') ?? $this->route('id');

        return [
            'role' => ['sometimes', 'string', Rule::in(['admin', 'seller', 'buyer'])],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($userId)],
            'avatar' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'banned', 'pending'])],
            'address' => ['nullable', 'array'],
        ];
    }
}
