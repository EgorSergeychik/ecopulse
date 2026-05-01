<?php

namespace Domain\Incident\DTO;

use Illuminate\Http\Request;

class IncidentIndexData
{
    public function __construct(
        public ?string $search = null,
        public int $limit = 10,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $search = trim((string) $request->input('search', ''));

        return new self(
            search: $search !== '' ? $search : null,
            limit: max(1, $request->integer('per_page', 10)),
        );
    }
}
