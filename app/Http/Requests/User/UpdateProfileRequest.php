<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $userId = auth()->id() ?? $this->user()?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20', Rule::unique('users')->ignore($userId)],
            'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'], // 2MB max
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            'address' => ['sometimes', 'nullable', 'array'],
            'address.*.street' => ['required_with:address', 'string'],
            'address.*.city' => ['required_with:address', 'string'],
            'address.*.state' => ['required_with:address', 'string'],
            'address.*.zip_code' => ['required_with:address', 'string'],
            'address.*.country' => ['required_with:address', 'string'],
        ];
    }
}
