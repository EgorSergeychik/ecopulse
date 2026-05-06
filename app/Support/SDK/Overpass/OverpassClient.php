<?php

namespace App\Support\SDK\Overpass;

use Illuminate\Support\Facades\Http;

class OverpassClient
{
    private const string BASE_URL = 'https://overpass-api.de/api/interpreter';

    public function fetchBbox(float $south, float $west, float $north, float $east): string
    {
        $query = "[out:xml][timeout:60];\n(\n  node({$south},{$west},{$north},{$east});\n  way({$south},{$west},{$north},{$east});\n  relation({$south},{$west},{$north},{$east});\n);\nout body;\n>;\nout skel qt;";

        return Http::timeout(120)
            ->withHeaders([
                'Accept'     => 'application/xml, text/xml',
                'User-Agent' => 'EcoPulse/1.0 (environmental monitoring; sergeychike.egor@gmail.com )',
            ])
            ->withOptions(['curl' => [CURLOPT_IGNORE_CONTENT_LENGTH => true]])
            ->asForm()
            ->post(self::BASE_URL, ['data' => $query])
            ->throw()
            ->body();
    }
}
