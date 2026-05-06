#!/usr/bin/env python3
"""
OSM to Webots .wbt converter.
Usage: osm_importer.py <input.osm> <output.wbt>

Parses OpenStreetMap XML and generates a Webots world file (.wbt) containing
roads and buildings derived from the map data.
"""

import math
import sys
import xml.etree.ElementTree as ET

EARTH_RADIUS = 6_371_000  # metres


# ---------------------------------------------------------------------------
# Coordinate helpers
# ---------------------------------------------------------------------------

def latlon_to_xy(lat: float, lon: float, ref_lat: float, ref_lon: float):
    """Equirectangular projection – returns (x, z) in metres."""
    x = math.radians(lon - ref_lon) * EARTH_RADIUS * math.cos(math.radians(ref_lat))
    z = math.radians(lat - ref_lat) * EARTH_RADIUS
    return x, z


# ---------------------------------------------------------------------------
# OSM parsing
# ---------------------------------------------------------------------------

def parse_osm(path: str):
    tree = ET.parse(path)
    root = tree.getroot()

    nodes: dict[str, tuple[float, float]] = {}
    for node in root.findall('node'):
        nid = node.get('id')
        lat = float(node.get('lat'))
        lon = float(node.get('lon'))
        nodes[nid] = (lat, lon)

    ways: list[dict] = []
    for way in root.findall('way'):
        tags = {t.get('k'): t.get('v') for t in way.findall('tag')}
        refs = [nd.get('ref') for nd in way.findall('nd')]
        ways.append({'tags': tags, 'refs': refs})

    return nodes, ways


def centroid(nodes: dict) -> tuple[float, float]:
    lats = [v[0] for v in nodes.values()]
    lons = [v[1] for v in nodes.values()]
    return sum(lats) / len(lats), sum(lons) / len(lons)


# ---------------------------------------------------------------------------
# WBT generation helpers
# ---------------------------------------------------------------------------

def wbt_header() -> list[str]:
    return [
        '#VRML_SIM R2023b utf8',
        '',
        'WorldInfo {',
        '  info [',
        '    "Generated from OpenStreetMap data by EcoPulse"',
        '  ]',
        '  title "OSM Export"',
        '}',
        'Viewpoint {',
        '  orientation -0.5773502691896258 0.5773502691896258 0.5773502691896258 2.0944',
        '  position 0 200 0',
        '}',
        'Background {',
        '  skyColor [',
        '    0.53 0.81 0.98',
        '  ]',
        '}',
        'DirectionalLight {',
        '  direction 0.6 -0.8 -0.5',
        '  intensity 0.5',
        '  castShadows TRUE',
        '}',
        '',
    ]


_SPEED_BY_HIGHWAY = {
    'motorway': 130, 'motorway_link': 100,
    'trunk': 110,    'trunk_link': 80,
    'primary': 90,   'primary_link': 70,
    'secondary': 70, 'secondary_link': 60,
    'tertiary': 50,  'tertiary_link': 40,
    'residential': 30, 'living_street': 10,
    'service': 20,   'track': 20,
    'path': 10,      'footway': 5,
    'cycleway': 15,  'unclassified': 30,
}

_LANES_BY_HIGHWAY = {
    'motorway': 3, 'trunk': 2, 'primary': 2,
}


def road_node(coords: list[tuple[float, float]], tags: dict) -> list[str]:
    hw = tags.get('highway', '')
    speed = _SPEED_BY_HIGHWAY.get(hw, 30)
    lanes = _LANES_BY_HIGHWAY.get(hw, 1)

    try:
        lanes = int(tags.get('lanes', lanes))
    except ValueError:
        pass

    lines = [
        'Road {',
        '  splineSubdivision -1',
        '  wayPoints [',
    ]
    for x, z in coords:
        lines.append(f'    {x:.3f} 0 {-z:.3f},')
    lines += [
        '  ]',
        f'  numberOfLanes {lanes}',
        f'  speedLimit {speed}',
        '  roadBorderHeight 0',
        '}',
        '',
    ]
    return lines


def building_node(coords: list[tuple[float, float]], tags: dict) -> list[str]:
    cx = sum(c[0] for c in coords) / len(coords)
    cz = sum(c[1] for c in coords) / len(coords)

    floors = 3
    try:
        floors = int(tags.get('building:levels', floors))
    except ValueError:
        pass

    height = floors * 3.0

    shape_pts = ' '.join(f'{x - cx:.3f} {z - cz:.3f}' for x, z in coords[:-1])

    return [
        'Building {',
        f'  translation {cx:.3f} 0 {-cz:.3f}',
        f'  floorNumber {floors}',
        '  startingFloor 0',
        f'  height {height:.1f}',
        f'  shape [ {shape_pts} ]',
        '}',
        '',
    ]


# ---------------------------------------------------------------------------
# Main conversion
# ---------------------------------------------------------------------------

def convert(osm_path: str, wbt_path: str) -> None:
    nodes, ways = parse_osm(osm_path)

    if not nodes:
        raise ValueError('No nodes found in the OSM file.')

    ref_lat, ref_lon = centroid(nodes)
    lines = wbt_header()

    for way in ways:
        tags = way['tags']
        refs = way['refs']

        coords = []
        for ref in refs:
            if ref in nodes:
                lat, lon = nodes[ref]
                coords.append(latlon_to_xy(lat, lon, ref_lat, ref_lon))

        if len(coords) < 2:
            continue

        if tags.get('highway'):
            lines.extend(road_node(coords, tags))
        elif tags.get('building') and len(coords) >= 4:
            lines.extend(building_node(coords, tags))

    with open(wbt_path, 'w', encoding='utf-8') as f:
        f.write('\n'.join(lines))


def main() -> None:
    if len(sys.argv) != 3:
        print(f'Usage: {sys.argv[0]} <input.osm> <output.wbt>', file=sys.stderr)
        sys.exit(1)

    try:
        convert(sys.argv[1], sys.argv[2])
        print(f'Written: {sys.argv[2]}')
    except Exception as exc:
        print(f'Error: {exc}', file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()
