<?php

namespace Domain\User\DTO;

use Illuminate\Foundation\Http\FormRequest;

class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $role,
        public ?string $password = null,
    ) {
    }

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            name: $request->name,
            email: $request->email,
            role: $request->role,
            password: $request->password,
        );
    }
}
