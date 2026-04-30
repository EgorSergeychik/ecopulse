<?php

namespace Domain\Robot\DTO;

use Illuminate\Foundation\Http\FormRequest;

class RobotData
{
    public function __construct(
        public string $name,
        public ?string $mac_address,
        public int $zone_id,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $macAddress = trim((string) $request->mac_address);

        return new self(
            name: $request->name,
            mac_address: $macAddress !== '' ? $macAddress : null,
            zone_id: (int) $request->zone_id,
        );
    }
}
