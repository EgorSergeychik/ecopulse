<?php

namespace Domain\Zone\Models;

use Domain\User\Models\User;
use Domain\Zone\Queries\ZoneQueryBuilder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['name', 'center_lat', 'center_lng', 'default_zoom', 'bounding_box'])]
class Zone extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'bounding_box' => 'array',
            'center_lat' => 'decimal:8',
            'center_lng' => 'decimal:8',
        ];
    }

    /*
     * Relations
     */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /*
     * Media
     */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300)
            ->nonQueued();
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
     * Queries
     */

    public function newEloquentBuilder($query): ZoneQueryBuilder
    {
        return new ZoneQueryBuilder($query);
    }
}
