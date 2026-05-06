<?php

namespace Domain\Webots\Controllers;

use App\Http\Controllers\Controller;
use App\Support\SDK\Overpass\OverpassFacade;
use Domain\Webots\Requests\DownloadOsmRequest;
use Illuminate\Http\Response;

class DownloadOsmController extends Controller
{
    public function __invoke(DownloadOsmRequest $request): Response
    {
        $osm = OverpassFacade::fetchBbox(
            south: $request->float('south'),
            west:  $request->float('west'),
            north: $request->float('north'),
            east:  $request->float('east'),
        );

        return response($osm, 200, [
            'Content-Type'        => 'application/xml',
            'Content-Disposition' => 'attachment; filename="export.osm"',
        ]);
    }
}
