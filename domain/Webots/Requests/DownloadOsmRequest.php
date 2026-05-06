<?php

namespace Domain\Webots\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DownloadOsmRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'south' => ['required', 'numeric', 'between:-90,90'],
            'west'  => ['required', 'numeric', 'between:-180,180'],
            'north' => ['required', 'numeric', 'between:-90,90', 'gt:south'],
            'east'  => ['required', 'numeric', 'between:-180,180', 'gt:west'],
        ];
    }
}
