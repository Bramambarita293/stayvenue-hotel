<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

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
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->max(72), 'confirmed'],
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]*$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Nomor telepon hanya boleh berisi angka, +, -, spasi, dan kurung.',
        ];
    }
}
