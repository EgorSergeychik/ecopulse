<?php

namespace App\Support\SDK\Overpass;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string fetchBbox(float $south, float $west, float $north, float $east)
 */
class OverpassFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OverpassClient::class;
    }
}
