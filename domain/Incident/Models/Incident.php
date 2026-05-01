<?php

namespace Domain\Incident\Models;

use Domain\Incident\Enums\IncidentSeverity;
use Domain\Incident\Queries\IncidentQueryBuilder;
use Domain\Robot\Models\Robot;
use Domain\User\Models\User;
use Domain\Zone\Models\Zone;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'robot_id',
    'zone_id',
    'type',
    'description',
    'snapshot_data',
    'severity',
    'resolved_at',
    'resolved_by',
    'resolution_note',
])]
class Incident extends Model
{
    protected function casts(): array
    {
        return [
            'snapshot_data' => 'array',
            'severity' => IncidentSeverity::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function robot(): BelongsTo
    {
        return $this->belongsTo(Robot::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function newEloquentBuilder($query): IncidentQueryBuilder
    {
        return new IncidentQueryBuilder($query);
    }
}
