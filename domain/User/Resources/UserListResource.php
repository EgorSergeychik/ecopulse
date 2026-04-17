<?php

namespace Domain\User\Resources;

use Domain\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => __('misc.roles.' . $this->role->value),
            'role_id' => $this->role->value,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
