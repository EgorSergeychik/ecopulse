<?php

namespace Domain\Telemetry\Models;

use Domain\Robot\Models\Robot;
use Domain\Telemetry\Queries\TelemetryLogQueryBuilder;
use Domain\User\Models\User;
use Domain\Zone\Models\Zone;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

#[Fillable(['robot_id', 'lat', 'lng', 'metrics', 'recorded_at'])]
class TelemetryLog extends Model
{
    use HasRelationships;

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
            'metrics' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    /*
     * Relations
     */

    public function robot(): BelongsTo
    {
        return $this->belongsTo(Robot::class);
    }

    public function zone(): HasOneThrough
    {
        return $this->hasOneThrough(Zone::class, Robot::class);
    }

    public function users(): HasManyDeep
    {
        return $this->hasManyDeep(
            User::class,
            [Robot::class, Zone::class, 'user_zone'],
            ['id', 'id', 'zone_id', 'id'],
            ['robot_id', 'zone_id', 'id', 'user_id']
        );
    }

    /*
     * Access
     */

    public function checkAccess(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->users->where('users.id', $user->id)->isNotEmpty();
    }

    /*
     * Queries
     */

    public function newEloquentBuilder($query): TelemetryLogQueryBuilder
    {
        return new TelemetryLogQueryBuilder($query);
    }
}
