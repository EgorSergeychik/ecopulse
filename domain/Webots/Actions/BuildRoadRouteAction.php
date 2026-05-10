<?php

namespace Domain\Webots\Actions;

use App\Support\SDK\Overpass\OverpassFacade;

class BuildRoadRouteAction
{
    private const EARTH_RADIUS = 6_371_000.0;
    private const MAX_WAYPOINT_SPACING = 8.0;
    private const EXCLUDED_HIGHWAYS = [
        'steps',
        'corridor',
        'elevator',
        'proposed',
        'construction',
    ];

    public function execute(float $south, float $west, float $north, float $east): array
    {
        $refLat = ($south + $north) / 2;
        $refLng = ($west + $east) / 2;

        $data = $this->fetchData($south, $west, $north, $east);

        $elements = $data['elements'] ?? [];
        $nodes    = $this->parseNodes($elements);
        $graph    = $this->buildGraph($elements, $nodes);

        if (empty($graph)) {
            throw new \RuntimeException('No road data returned for this zone.');
        }

        $startNodeId = $this->findStartNodeId($graph, $nodes, $refLat, $refLng);
        $routeNodeIds = $this->buildContinuousRoute($graph, $startNodeId);

        if (empty($routeNodeIds)) {
            throw new \RuntimeException('Could not build a road route for this zone.');
        }

        $route = array_map(fn (int $nodeId) => $nodes[$nodeId], $routeNodeIds);
        $thinned = $this->thinRoute($route);

        return array_map(fn ($n) => $this->withLocal($n, $refLat, $refLng), $thinned);
    }

    private function fetchData(float $south, float $west, float $north, float $east): array
    {
        $testFile = base_path('test.json');
        if (file_exists($testFile)) {
            return json_decode(file_get_contents($testFile), true) ?? [];
        }

        return OverpassFacade::fetchHighwaysBbox($south, $west, $north, $east);
    }

    private function parseNodes(array $elements): array
    {
        $nodes = [];
        foreach ($elements as $el) {
            if ($el['type'] === 'node') {
                $nodes[$el['id']] = ['lat' => (float) $el['lat'], 'lng' => (float) $el['lon']];
            }
        }
        return $nodes;
    }

    private function buildGraph(array $elements, array $nodes): array
    {
        $graph = [];

        foreach ($elements as $el) {
            if ($el['type'] !== 'way') {
                continue;
            }

            $highway = $el['tags']['highway'] ?? null;
            if (!is_string($highway) || in_array($highway, self::EXCLUDED_HIGHWAYS, true)) {
                continue;
            }

            $rawIds   = $el['nodes'] ?? [];
            $validIds = array_values(array_filter($rawIds, fn ($id) => isset($nodes[$id])));

            if (count($validIds) < 2) {
                continue;
            }

            foreach ($validIds as $nodeId) {
                $graph[$nodeId] ??= [];
            }

            for ($i = 0, $last = count($validIds) - 1; $i < $last; $i++) {
                $from = $validIds[$i];
                $to   = $validIds[$i + 1];

                if ($from === $to) {
                    continue;
                }

                $graph[$from][$to] = true;
                $graph[$to][$from] = true;
            }
        }

        return $graph;
    }

    private function findStartNodeId(array $graph, array $nodes, float $refLat, float $refLng): int
    {
        $bestId   = array_key_first($graph);
        $bestDist = PHP_FLOAT_MAX;

        foreach (array_keys($graph) as $nodeId) {
            $node = $nodes[$nodeId];
            $dist = $this->haversine($refLat, $refLng, $node['lat'], $node['lng']);
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $bestId   = $nodeId;
            }
        }

        return $bestId;
    }

    private function buildContinuousRoute(array $graph, int $startNodeId): array
    {
        $route = [];
        $visitedEdges = [];

        $visit = function (int $nodeId, ?int $fromNodeId = null) use (&$visit, &$route, &$visitedEdges, $graph): void {
            if ($fromNodeId === null || end($route) !== $nodeId) {
                $route[] = $nodeId;
            }

            $neighbors = array_keys($graph[$nodeId]);
            sort($neighbors);

            foreach ($neighbors as $neighborId) {
                $edgeKey = $this->edgeKey($nodeId, $neighborId);
                if (isset($visitedEdges[$edgeKey])) {
                    continue;
                }

                $visitedEdges[$edgeKey] = true;
                $visit($neighborId, $nodeId);
                $route[] = $nodeId;
            }
        };

        $visit($startNodeId);

        return $this->trimConsecutiveDuplicates($route);
    }

    private function edgeKey(int $a, int $b): string
    {
        return $a < $b ? "{$a}:{$b}" : "{$b}:{$a}";
    }

    private function trimConsecutiveDuplicates(array $route): array
    {
        $trimmed = [];

        foreach ($route as $nodeId) {
            if ($trimmed === [] || end($trimmed) !== $nodeId) {
                $trimmed[] = $nodeId;
            }
        }

        return $trimmed;
    }

    private function thinRoute(array $route): array
    {
        if ($route === []) {
            return [];
        }

        $thinned = [$route[0]];

        foreach (array_slice($route, 1) as $node) {
            $last = end($thinned);
            $dist = $this->haversine($last['lat'], $last['lng'], $node['lat'], $node['lng']);

            if ($dist <= self::MAX_WAYPOINT_SPACING) {
                $thinned[] = $node;
                continue;
            }

            $segments = (int) ceil($dist / self::MAX_WAYPOINT_SPACING);
            for ($step = 1; $step < $segments; $step++) {
                $ratio = $step / $segments;
                $thinned[] = [
                    'lat' => $last['lat'] + (($node['lat'] - $last['lat']) * $ratio),
                    'lng' => $last['lng'] + (($node['lng'] - $last['lng']) * $ratio),
                ];
            }

            $thinned[] = $node;
        }

        return $thinned;
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dlat = deg2rad($lat2 - $lat1);
        $dlng = deg2rad($lng2 - $lng1);
        $a    = sin($dlat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dlng / 2) ** 2;
        return 2 * self::EARTH_RADIUS * asin(sqrt($a));
    }

    private function withLocal(array $node, float $refLat, float $refLng): array
    {
        $x = deg2rad($node['lng'] - $refLng) * self::EARTH_RADIUS * cos(deg2rad($refLat));
        $z = deg2rad($node['lat'] - $refLat) * self::EARTH_RADIUS;
        return array_merge($node, ['x' => round($x, 3), 'z' => round($z, 3)]);
    }
}
