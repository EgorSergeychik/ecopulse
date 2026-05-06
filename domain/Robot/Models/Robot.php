<?php

namespace Domain\Robot\Models;

use Domain\Incident\Models\Incident;
use Domain\Robot\Queries\RobotQueryBuilder;
use Domain\Robot\States\RobotState;
use Domain\Telemetry\Models\TelemetryLog;
use Domain\User\Models\User;
use Domain\Zone\Models\Zone;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

#[Fillable(['name', 'mac_address', 'zone_id', 'status', 'battery_pct'])]
class Robot extends Model
{
    use HasRelationships;
    use HasApiTokens;

    private const ACCESS_TOKEN_NAME = 'robot-api-token';

    protected function casts(): array
    {
        return [
            'status' => RobotState::class,
            'battery_pct' => 'decimal:2',
        ];
    }

    /*
     * Relations
     */

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function users(): HasManyDeep
    {
        return $this->hasManyDeep(
            User::class,
            [Zone::class, 'user_zone'],
            ['id', 'zone_id', 'id'],
            ['zone_id', 'id', 'user_id']
        );
    }

    public function telemetryLogs(): HasMany
    {
        return $this->hasMany(TelemetryLog::class);
    }

    public function latestTelemetryLog(): HasOne
    {
        return $this->hasOne(TelemetryLog::class)->latestOfMany('recorded_at');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /*
     * Access
     */

    public function checkAccess(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->users->where('id', $user->id)->isNotEmpty();
    }

    /*
     * Tokens
     */

    public function issueAccessToken(): NewAccessToken
    {
        return $this->createToken(self::ACCESS_TOKEN_NAME);
    }

    public function regenerateAccessToken(): NewAccessToken
    {
        $this->tokens()->delete();

        return $this->issueAccessToken();
    }

    /*
     * Queries
     */

    public function newEloquentBuilder($query): RobotQueryBuilder
    {
        return new RobotQueryBuilder($query);
    }
}
