<?php

namespace Domain\User\Requests;

use App\Support\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => [
                'required',
                'string',
                'min:8',
                Password::required()->uncompromised(3)->symbols()->letters()->mixedCase(),
            ],
        ];
    }
}
