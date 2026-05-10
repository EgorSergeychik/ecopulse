<?php

namespace Domain\Webots\Services;

class RobotControllerTemplate
{
    public function generate(
        array $waypoints,
        array $hotspots,
        float $refLat,
        float $refLng,
        string $apiUrl,
    ): string {
        $waypointsJson  = json_encode($waypoints, JSON_PRETTY_PRINT);
        $hotspotsJson   = json_encode($hotspots,  JSON_PRETTY_PRINT);
        $baseValues     = $this->baseValues();
        $baseValuesJson = json_encode($baseValues, JSON_PRETTY_PRINT);

        return <<<PYTHON
        # ============================================================
        # EcoPulse — Pioneer 3-DX Robot Controller
        # Generated automatically. Follow the setup steps below.
        #
        # SETUP
        # 1. In Webots scene tree, select your Pioneer 3-DX robot.
        # 2. Add a GPS node (children): name = "gps"
        # 3. Replace TOKEN_PLACEHOLDER below with your robot's API token.
        # 4. Set SEND_INTERVAL_MS to your preferred telemetry interval.
        # 5. Attach this file as the robot controller.
        # ============================================================

        import math
        import json
        import urllib.request
        import urllib.error
        from controller import Robot

        # --- Configuration ---
        API_URL           = "{$apiUrl}/api/telemetry"
        API_TOKEN         = "TOKEN_PLACEHOLDER"
        REF_LAT           = {$refLat}
        REF_LNG           = {$refLng}
        EARTH_R           = 6_371_000.0
        MAX_SPEED         = 6.28    # rad/s (Pioneer 3-DX wheel)
        GOAL_DIST         = 1.5     # metres — waypoint reached threshold
        SIGMA             = 200.0   # gaussian hotspot decay (metres)
        SEND_INTERVAL_MS  = 1000    # ← change this to adjust telemetry frequency (milliseconds)
        WHEEL_RADIUS      = 0.0975  # metres (Pioneer 3-DX)
        WHEEL_BASE        = 0.33    # metres (Pioneer 3-DX axle width)

        WAYPOINTS = {$waypointsJson}

        HOTSPOTS = {$hotspotsJson}

        BASE_VALUES = {$baseValuesJson}


        # --- Helpers ---

        def latlng_to_local(lat, lng):
            """Convert WGS84 lat/lng to local navigation coordinates (metres, north-positive z)."""
            x = math.radians(lng - REF_LNG) * EARTH_R * math.cos(math.radians(REF_LAT))
            z = math.radians(lat - REF_LAT) * EARTH_R
            return x, z


        def haversine_m(lat1, lng1, lat2, lng2):
            dlat = math.radians(lat2 - lat1)
            dlng = math.radians(lng2 - lng1)
            a = math.sin(dlat / 2) ** 2 + math.cos(math.radians(lat1)) * math.cos(math.radians(lat2)) * math.sin(dlng / 2) ** 2
            return 2 * EARTH_R * math.asin(math.sqrt(a))


        def compute_metric(name, lat, lng):
            spots = HOTSPOTS.get(name, [])
            base  = BASE_VALUES.get(name, 0.0)
            value = base
            for spot in spots:
                dist = haversine_m(lat, lng, spot["lat"], spot["lng"])
                decay = math.exp(-(dist ** 2) / (2 * SIGMA ** 2))
                influence = (spot["value"] - base) * decay
                if influence > value - base:
                    value = base + influence
            return round(value, 2)


        def send_telemetry(lat, lng, battery, metrics):
            payload = json.dumps({
                "coords": {
                    "lat": round(lat, 7),
                    "lng": round(lng, 7),
                },
                "metrics": {
                    "battery_pct": round(battery, 2),
                    **{k: round(v, 7) if isinstance(v, float) else v for k, v in metrics.items()},
                },
            }).encode()
            req = urllib.request.Request(
                API_URL,
                data=payload,
                headers={
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": f"Bearer {API_TOKEN}",
                },
                method="POST",
            )
            try:
                with urllib.request.urlopen(req, timeout=5) as resp:
                    print(f"[telemetry] {resp.status} lat={round(lat,5)} lng={round(lng,5)}")
            except urllib.error.HTTPError as e:
                body = e.read().decode(errors="replace")
                print(f"[telemetry] HTTP {e.code} {e.reason} — {body[:300]}")
            except urllib.error.URLError as e:
                print(f"[telemetry] network error — {e.reason}")


        # --- Main ---

        robot    = Robot()
        timestep = int(robot.getBasicTimeStep())

        # How many simulation steps between telemetry sends
        send_every = max(1, round(SEND_INTERVAL_MS / timestep))

        left_motor  = robot.getDevice("left wheel")
        right_motor = robot.getDevice("right wheel")
        left_motor.setPosition(float("inf"))
        right_motor.setPosition(float("inf"))
        left_motor.setVelocity(0)
        right_motor.setVelocity(0)

        # GPS with gpsCoordinateSystem "WGS84" returns [latitude, longitude, altitude].
        gps = robot.getDevice("gps")
        gps.enable(timestep)

        robot.step(timestep)
        _p = gps.getValues()
        _x0, _z0 = latlng_to_local(_p[0], _p[1])
        wp_index = 0
        _bd = float("inf")
        for _i, _wp in enumerate(WAYPOINTS):
            _d = math.hypot(_wp["x"] - _x0, _wp["z"] - _z0)
            if _d < _bd:
                _bd = _d
                wp_index = _i

        tick      = 0
        battery   = 85.0
        angle     = 0.0   # heading (radians, north = 0, east = +π/2) — updated via dead reckoning
        cmd_left  = 0.0   # last commanded left wheel velocity (rad/s)
        cmd_right = 0.0   # last commanded right wheel velocity (rad/s)

        while robot.step(timestep) != -1:
            tick += 1

            pos     = gps.getValues()   # [latitude, longitude, altitude] — WGS84
            lat_cur = pos[0]
            lng_cur = pos[1]

            # Dead reckoning: integrate wheel velocities to update heading
            dt    = timestep / 1000.0
            omega = (cmd_left - cmd_right) * WHEEL_RADIUS / WHEEL_BASE
            angle += omega * dt
            while angle >  math.pi: angle -= 2 * math.pi
            while angle < -math.pi: angle += 2 * math.pi

            x_cur, z_cur = latlng_to_local(lat_cur, lng_cur)

            if wp_index >= len(WAYPOINTS):
                wp_index = 0

            wp   = WAYPOINTS[wp_index]
            dx   = wp["x"] - x_cur
            dz   = wp["z"] - z_cur
            dist = math.hypot(dx, dz)

            if dist < GOAL_DIST:
                wp_index += 1
            else:
                target_angle = math.atan2(dx, dz)
                error        = target_angle - angle
                while error >  math.pi: error -= 2 * math.pi
                while error < -math.pi: error += 2 * math.pi

                turn  = max(-1.0, min(1.0, error / math.pi))
                speed = MAX_SPEED * (1.0 - abs(turn) * 0.6)
                cmd_left  = speed + turn * MAX_SPEED * 0.4
                cmd_right = speed - turn * MAX_SPEED * 0.4
                left_motor.setVelocity(cmd_left)
                right_motor.setVelocity(cmd_right)

            battery = max(0.0, battery - 0.0002)

            if tick % send_every == 0:
                metrics = {name: compute_metric(name, lat_cur, lng_cur) for name in HOTSPOTS}
                send_telemetry(lat_cur, lng_cur, battery, metrics)
        PYTHON;
    }

    private function baseValues(): array
    {
        $metrics = config('telemetry.metrics');
        $base = [];
        foreach ($metrics as $name => $cfg) {
            if ($name === 'battery_pct') {
                continue;
            }
            $fake = $cfg['fake'];
            $range = $fake['max'] - $fake['min'];
            $value = $fake['min'] + $range * 0.15;
            $base[$name] = $fake['type'] === 'int'
                ? (int) round($value)
                : round($value, $fake['decimals'] ?? 2);
        }
        return $base;
    }
}
