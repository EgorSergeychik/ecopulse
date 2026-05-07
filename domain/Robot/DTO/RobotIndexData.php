<?php

namespace Domain\Robot\DTO;

use App\Support\Parents\ParentData;
use Illuminate\Http\Request;

class RobotIndexData extends ParentData
{
    public function __construct(
        public ?string $search = null,
        public int $limit = 10,
        public bool $is_paginated = true,

        public ?int $zone_id = null,
        public ?array $statuses = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $search = trim((string) $request->input('search', ''));

        return new self(
            search: $search !== '' ? $search : null,
            limit: max(1, $request->integer('per_page', 10)),
            is_paginated: $request->boolean('is_paginated', true),

            zone_id: $request->integer('zone_id') ?: null,
        );
    }
}
