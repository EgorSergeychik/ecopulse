<?php

namespace App\Support\Services;

use RuntimeException;

class WebotsConverter
{
    private string $script;

    public function __construct()
    {
        $this->script = base_path('scripts/osm_importer/importer.py');
    }

    public function convert(string $osmContent, float $south, float $west, float $north, float $east): string
    {
        if (! str_contains($osmContent, '<bounds')) {
            $bounds = sprintf(
                '<bounds minlat="%.7f" minlon="%.7f" maxlat="%.7f" maxlon="%.7f"/>',
                $south, $west, $north, $east,
            );
            $osmContent = preg_replace('/(<osm\b[^>]*>)/', "$1\n  {$bounds}", $osmContent);
        }

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $osmFile = tempnam($tempDir, 'ecopulse_') . '.osm';
        $wbtFile = substr($osmFile, 0, -4) . '.wbt';

        try {
            file_put_contents($osmFile, $osmContent);

            $cwd = dirname($this->script);

            $process = proc_open(
                ['python3', $this->script, "--input={$osmFile}", "--output={$wbtFile}"],
                [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes,
                $cwd,
            );

            if ($process === false) {
                throw new RuntimeException('Failed to start Python converter process.');
            }

            stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $code = proc_close($process);

            if ($code !== 0 || ! file_exists($wbtFile)) {
                throw new RuntimeException('OSM→WBT conversion failed: ' . $stderr);
            }

            return file_get_contents($wbtFile);
        } finally {
            if (file_exists($osmFile)) {
                @unlink($osmFile);
            }
            if (file_exists($wbtFile)) {
                @unlink($wbtFile);
            }
        }
    }
}
