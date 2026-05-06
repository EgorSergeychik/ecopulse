<?php

namespace Domain\Webots\Controllers;

use App\Http\Controllers\Controller;
use App\Support\SDK\Overpass\OverpassFacade;
use App\Support\Services\WebotsConverter;
use Domain\Webots\Requests\DownloadOsmRequest;
use Illuminate\Http\Response;

class DownloadWbtController extends Controller
{
    public function __construct(private readonly WebotsConverter $converter) {}

    public function __invoke(DownloadOsmRequest $request): Response
    {
        $osm = OverpassFacade::fetchBbox(
            south: $request->float('south'),
            west:  $request->float('west'),
            north: $request->float('north'),
            east:  $request->float('east'),
        );

        $wbt = $this->converter->convert(
            $osm,
            south: $request->float('south'),
            west:  $request->float('west'),
            north: $request->float('north'),
            east:  $request->float('east'),
        );

        return response($wbt, 200, [
            'Content-Type'        => 'model/vrml',
            'Content-Disposition' => 'attachment; filename="export.wbt"',
        ]);
    }
}
