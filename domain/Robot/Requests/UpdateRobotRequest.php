<?php

namespace Domain\Robot\Requests;

use Domain\Robot\Models\Robot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRobotRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var Robot $robot */
        $robot = $this->route('robot');

        return [
            'name' => ['required', 'string', 'max:255'],
            'mac_address' => ['nullable', 'string', 'max:255', Rule::unique('robots', 'mac_address')->ignore($robot->id)],
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
        ];
    }
}
