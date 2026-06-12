<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'organization' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where('is_system', false),
            ],
            // Ignore soft-deleted users: a deleted account that still owns this
            // email is restored & overwritten in UserRepository::create().
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization.exists' => 'Sila pilih organisasi yang sah.',
        ];
    }
}
