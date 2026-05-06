<?php

namespace Domain\Telemetry\Events;

use Domain\Robot\Models\Robot;
use Domain\Robot\Resources\RobotZoneResource;
use Domain\Telemetry\Models\TelemetryLog;
use Domain\Telemetry\Resources\TelemetryLogListResource;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TelemetryLogStored implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly TelemetryLog $telemetryLog,
        public readonly Robot $robot,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('zone.'.$this->robot->zone_id);
    }

    public function broadcastAs(): string
    {
        return 'TelemetryLogStored';
    }

    public function broadcastWith(): array
    {
        return [
            'telemetry_log' => new TelemetryLogListResource($this->telemetryLog),
            'robot' => new RobotZoneResource($this->robot),
        ];
    }
}
