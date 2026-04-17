<?php

namespace Domain\Role\Resources;

use App\Support\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Role */
class SelectRoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->value,
            'name' => __('misc.roles.' . $this->value),
        ];
    }
}
