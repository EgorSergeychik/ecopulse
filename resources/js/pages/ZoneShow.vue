<script setup lang="ts">
import 'leaflet/dist/leaflet.css';
import { Head } from '@inertiajs/vue3';
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import { Bot, MapPinned } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import SideListPanel from '@/components/SideListPanel.vue';
import { DataTable, DataTablePagination } from '@/components/ui/table';
import type { TableColumn } from '@/components/ui/table';
import echo from '@/echo';
import { index as zonesIndex } from '@/routes/zones';
import type { Robot } from '@/types/robot';
import type { LatLng, Zone } from '@/types/zone';

delete (L.Icon.Default.prototype as { _getIconUrl?: unknown })._getIconUrl;
L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
});

interface TelemetryLogRow {
    id: number;
    robot_name: string | null;
    zone_name: string | null;
    lat: number;
    lng: number;
    battery_pct: number | null;
    co2: number | null;
    noise_level: number | null;
    metrics: Record<string, unknown>;
    recorded_at: string | null;
}

const props = defineProps<{
    zone: Zone | { data: Zone };
    robots: Robot[] | { data: Robot[] };
    telemetryLogs: {
        data: TelemetryLogRow[];
        meta: any;
    };
}>();

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'pages.zones.title',
                href: zonesIndex(),
            },
        ],
    },
});

const columns = computed<TableColumn[]>(() => [
    { key: 'robot_name', label: t('pages.telemetry_logs.table.columns.robot') },
    { key: 'lat', label: t('pages.telemetry_logs.table.columns.lat') },
    { key: 'lng', label: t('pages.telemetry_logs.table.columns.lng') },
    {
        key: 'battery_pct',
        label: t('pages.telemetry_logs.table.columns.battery_pct'),
    },
    { key: 'co2', label: t('pages.telemetry_logs.table.columns.co2') },
    {
        key: 'noise_level',
        label: t('pages.telemetry_logs.table.columns.noise_level'),
    },
    {
        key: 'recorded_at',
        label: t('pages.telemetry_logs.table.columns.recorded_at'),
    },
]);

interface RealtimeRobotPatch {
    lat: number;
    lng: number;
    battery_pct: number;
    status: string;
    status_label: string;
    latest_recorded_at: string | null;
    latest_metrics: Record<string, unknown>;
}

const mapEl = ref<HTMLElement | null>(null);
const zoneData = computed<Zone>(() =>
    'data' in props.zone ? props.zone.data : props.zone,
);
const robotPatches = ref<Map<number, RealtimeRobotPatch>>(new Map());
const realtimeLogs = ref<TelemetryLogRow[]>([]);
const robotsData = computed<Robot[]>(() => {
    const base = Array.isArray(props.robots) ? props.robots : props.robots.data;
    if (robotPatches.value.size === 0) return base;
    return base.map((r) => {
        const patch = robotPatches.value.get(r.id);
        return patch ? { ...r, ...patch } : r;
    });
});
const logsData = computed<TelemetryLogRow[]>(() => {
    const seen = new Set(realtimeLogs.value.map((l) => l.id));
    return [
        ...realtimeLogs.value,
        ...props.telemetryLogs.data.filter((l) => !seen.has(l.id)),
    ];
});
const selectedRobotId = ref<number | null>(null);

let map: L.Map | null = null;
let zoneLayer: L.Polygon | null = null;
const markerLayers = new Map<number, L.Marker>();
const pathLayers = new Map<number, L.Polyline>();
const pathCache = new Map<number, L.LatLngTuple[]>();

const formatMetric = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (typeof value === 'number') {
        return Number.isInteger(value) ? String(value) : value.toFixed(2);
    }

    return String(value);
};

const robotStatusClass = (status: string): string => {
    if (status === 'active') {
        return 'robot-marker__status--active';
    }

    if (status === 'maintenance') {
        return 'robot-marker__status--maintenance';
    }

    if (status === 'error') {
        return 'robot-marker__status--error';
    }

    return 'robot-marker__status--offline';
};

const robotMarkerIcon = (robot: Robot) =>
    L.divIcon({
        className: 'robot-marker-shell',
        html: `
            <div class="robot-marker">
                <div class="robot-marker__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 3h6v2h1a3 3 0 0 1 3 3v5a3 3 0 0 1-3 3v2h2v2h-2a2 2 0 0 1-2-2v-2h-4v2a2 2 0 0 1-2 2H6v-2h2v-2a3 3 0 0 1-3-3V8a3 3 0 0 1 3-3h1V3Zm-1 4a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1H8Zm1.5 2a1 1 0 1 1 0 2a1 1 0 0 1 0-2Zm5 0a1 1 0 1 1 0 2a1 1 0 0 1 0-2Zm-4.5 4h4v1h-4v-1Z" />
                    </svg>
                </div>
                <span class="robot-marker__status ${robotStatusClass(robot.status)}"></span>
            </div>
        `,
        iconSize: [34, 34],
        iconAnchor: [17, 17],
        popupAnchor: [0, -20],
    });

const buildPopupContent = (robot: Robot): string => {
    const metrics = robot.latest_metrics ?? {};

    return `
        <div class="space-y-2">
            <div>
                <div style="font-weight:600;">${robot.name}</div>
                <div style="color:#64748b;font-size:12px;">${robot.status_label}</div>
            </div>
            <div style="font-size:13px;display:grid;gap:4px;">
                <div><strong>${t('pages.zones.show.robots.battery')}:</strong> ${Number(robot.battery_pct).toFixed(2)}%</div>
                <div><strong>${t('pages.zones.show.map.latest_co2')}:</strong> ${formatMetric(metrics.co2)}</div>
                <div><strong>${t('pages.zones.show.map.latest_noise')}:</strong> ${formatMetric(metrics.noise_level)}</div>
                <div><strong>${t('pages.zones.show.map.latest_recorded_at')}:</strong> ${robot.latest_recorded_at ?? '—'}</div>
            </div>
        </div>
    `;
};

const refreshPolylineStyles = () => {
    pathLayers.forEach((layer, robotId) => {
        const isSelected = robotId === selectedRobotId.value;
        layer.setStyle({
            color: isSelected ? '#0f766e' : '#94a3b8',
            weight: isSelected ? 4 : 2,
            opacity: isSelected ? 0.9 : 0.4,
        });
    });

    markerLayers.forEach((marker, robotId) => {
        const element = marker.getElement();

        if (!element) {
            return;
        }

        element.classList.toggle(
            'robot-marker-selected',
            robotId === selectedRobotId.value,
        );
    });
};

const drawPath = (robotId: number, points: L.LatLngTuple[]) => {
    if (points.length < 2 || !map) {
        return;
    }

    const existing = pathLayers.get(robotId);

    if (existing) {
        existing.setLatLngs(points);
    } else {
        const polyline = L.polyline(points, { color: '#94a3b8', weight: 2, opacity: 0.4 }).addTo(map);
        pathLayers.set(robotId, polyline);
    }

    refreshPolylineStyles();
};

const fetchPath = async (robotId: number) => {
    if (pathCache.has(robotId)) {
        drawPath(robotId, pathCache.get(robotId)!);
        return;
    }

    const res = await fetch(`/robots/${robotId}/path`);
    const data: { lat: string | number; lng: string | number }[] = await res.json();
    const points: L.LatLngTuple[] = data.map((p) => [Number(p.lat), Number(p.lng)]);

    pathCache.set(robotId, points);
    drawPath(robotId, points);
};

const focusRobot = async (robotId: number, openPopup = false) => {
    selectedRobotId.value = robotId;
    refreshPolylineStyles();

    const marker = markerLayers.get(robotId);

    if (marker && map) {
        map.panTo(marker.getLatLng(), { animate: true });
    }

    if (openPopup && marker) {
        marker.openPopup();
    }

    await fetchPath(robotId);
};

const initializeMap = () => {
    if (!mapEl.value || map) {
        return;
    }

    map = L.map(mapEl.value, {
        zoomControl: true,
    }).setView(
        [Number(zoneData.value.center_lat), Number(zoneData.value.center_lng)],
        Number(zoneData.value.default_zoom),
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 22,
    }).addTo(map);

    if (zoneData.value.polygon && zoneData.value.polygon.length >= 3) {
        zoneLayer = L.polygon(
            zoneData.value.polygon.map(
                (point: LatLng) => [point.lat, point.lng] as L.LatLngTuple,
            ),
            {
                color: '#16a34a',
                weight: 2,
                fillColor: '#22c55e',
                fillOpacity: 0.12,
            },
        ).addTo(map);

        map.fitBounds(zoneLayer.getBounds(), { padding: [24, 24] });
    }

    robotsData.value.forEach((robot) => {
        if (robot.lat == null || robot.lng == null) {
            return;
        }

        const marker = L.marker([Number(robot.lat), Number(robot.lng)], {
            icon: robotMarkerIcon(robot),
        })
            .addTo(map!)
            .bindPopup(buildPopupContent(robot));

        marker.on('click', () => focusRobot(robot.id));
        markerLayers.set(robot.id, marker);
    });

    refreshPolylineStyles();
};

const handleRealtimeEvent = (event: {
    telemetry_log: TelemetryLogRow;
    robot: RealtimeRobotPatch & { id: number };
}) => {
    const { telemetry_log: log, robot } = event;

    realtimeLogs.value = [log, ...realtimeLogs.value].slice(0, 100);

    robotPatches.value = new Map(robotPatches.value).set(robot.id, {
        lat: robot.lat,
        lng: robot.lng,
        battery_pct: robot.battery_pct,
        status: robot.status,
        status_label: robot.status_label,
        latest_recorded_at: robot.latest_recorded_at,
        latest_metrics: robot.latest_metrics,
    });

    const newLatLng: L.LatLngTuple = [robot.lat, robot.lng];
    const patchedRobot = { ...robotsData.value.find((r) => r.id === robot.id)!, ...robot };

    const marker = markerLayers.get(robot.id);
    if (marker && map) {
        marker.setLatLng(newLatLng);
        marker.setIcon(robotMarkerIcon(patchedRobot));
        marker.setPopupContent(buildPopupContent(patchedRobot));
    }

    const cachedPath = pathCache.get(robot.id);
    if (cachedPath) {
        const updated = [...cachedPath, newLatLng];
        pathCache.set(robot.id, updated);
        drawPath(robot.id, updated);
    }
};

onMounted(() => {
    selectedRobotId.value = robotsData.value[0]?.id ?? null;
    initializeMap();

    echo?.private(`zone.${zoneData.value.id}`).listen('.TelemetryLogStored', handleRealtimeEvent);
});

onBeforeUnmount(() => {
    echo?.leave(`zone.${zoneData.value.id}`);

    map?.remove();
    map = null;
    zoneLayer = null;
    markerLayers.clear();
    pathLayers.clear();
    pathCache.clear();
});

watch(selectedRobotId, () => {
    refreshPolylineStyles();
});
</script>

<template>
    <Head :title="zoneData.name" />

    <div class="space-y-6 p-6">
        <Heading
            :title="zoneData.name"
            :description="t('pages.zones.show.description')"
        />

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2">
                <div
                    class="overflow-hidden rounded-xl border bg-card shadow-sm"
                >
                    <div
                        ref="mapEl"
                        class="min-h-[70vh] w-full xl:h-screen xl:max-h-[calc(100vh-10rem)]"
                    />
                </div>
            </div>

            <div class="flex h-full flex-col">
                <SideListPanel
                    :title="t('pages.zones.show.robots.title')"
                    :empty-text="t('pages.zones.show.robots.empty')"
                    :is-empty="robotsData.length === 0"
                >
                    <template #icon>
                        <Bot class="h-4 w-4 text-muted-foreground" />
                    </template>

                    <button
                        v-for="robot in robotsData"
                        :key="robot.id"
                        type="button"
                        class="flex w-full items-center justify-between rounded-lg border p-3 text-left transition-colors hover:bg-muted/40"
                        :class="
                            selectedRobotId === robot.id
                                ? 'border-primary bg-primary/5'
                                : 'border-border'
                        "
                        @click="focusRobot(robot.id, true)"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium">
                                {{ robot.name }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ robot.status_label }}
                            </p>
                        </div>
                        <div class="ml-4 text-right">
                            <p class="text-sm font-medium">
                                {{ Number(robot.battery_pct).toFixed(0) }}%
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ t('pages.zones.show.robots.battery') }}
                            </p>
                        </div>
                    </button>
                </SideListPanel>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <MapPinned class="h-4 w-4 text-muted-foreground" />
                <Heading
                    :title="t('pages.zones.show.logs.title')"
                    variant="small"
                />
            </div>

            <DataTable
                :columns="columns"
                :data="logsData"
                :empty-text="t('pages.telemetry_logs.table.empty')"
            >
                <template #cell-robot_name="{ row }">
                    <span class="font-medium">{{ row.robot_name ?? '—' }}</span>
                </template>

                <template #cell-battery_pct="{ row }">
                    <span>{{
                        row.battery_pct !== null
                            ? `${Number(row.battery_pct).toFixed(2)}%`
                            : '—'
                    }}</span>
                </template>

                <template #cell-co2="{ row }">
                    <span>{{ row.co2 ?? '—' }}</span>
                </template>

                <template #cell-noise_level="{ row }">
                    <span>{{ row.noise_level ?? '—' }}</span>
                </template>
            </DataTable>

            <DataTablePagination :meta="telemetryLogs.meta" />
        </section>
    </div>
</template>

<style scoped>
:deep(.robot-marker-shell) {
    background: transparent;
    border: 0;
}

:deep(.robot-marker) {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    border: 1px solid rgb(226 232 240 / 0.95);
    background: rgb(255 255 255 / 0.96);
    width: 2.125rem;
    height: 2.125rem;
    box-shadow: 0 10px 25px rgb(15 23 42 / 0.14);
    position: relative;
    transition:
        transform 0.15s ease,
        box-shadow 0.15s ease,
        border-color 0.15s ease;
}

:deep(.robot-marker__status) {
    position: absolute;
    right: 0.2rem;
    bottom: 0.2rem;
    height: 0.5rem;
    width: 0.5rem;
    border-radius: 9999px;
    border: 1px solid rgb(255 255 255);
}

:deep(.robot-marker__status--active) {
    background: rgb(16 185 129);
}

:deep(.robot-marker__status--maintenance) {
    background: rgb(245 158 11);
}

:deep(.robot-marker__status--error) {
    background: rgb(239 68 68);
}

:deep(.robot-marker__status--offline) {
    background: rgb(148 163 184);
}

:deep(.robot-marker__icon) {
    width: 1rem;
    height: 1rem;
    color: rgb(15 23 42);
}

:deep(.robot-marker__icon svg) {
    display: block;
    width: 100%;
    height: 100%;
    fill: currentColor;
}

:deep(.robot-marker-selected .robot-marker) {
    border-color: rgb(13 148 136);
    box-shadow: 0 12px 30px rgb(13 148 136 / 0.24);
    transform: translateY(-2px);
}
</style>
