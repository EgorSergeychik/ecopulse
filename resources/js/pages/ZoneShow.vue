<script setup lang="ts">
import 'leaflet/dist/leaflet.css';
import { Head } from '@inertiajs/vue3';
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import {
    Activity,
    Bot,
    Map as MapIcon,
    RefreshCw,
    Route,
    ThermometerSun,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import MetricsList from '@/components/MetricsList.vue';
import SideListPanel from '@/components/SideListPanel.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DataTable, DataTablePagination } from '@/components/ui/table';
import type { TableColumn } from '@/components/ui/table';
import { METRIC_KEYS } from '@/config/telemetry';
import type { MetricKey } from '@/config/telemetry';
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
    metrics: Partial<Record<(typeof METRIC_KEYS)[number], number>>;
    recorded_at: string | null;
}

interface HeatmapPoint {
    lat: number;
    lng: number;
    value: number;
    count: number;
    intensity: number;
    recorded_at: string | null;
}

interface HeatmapSnapshot {
    metric: HeatmapMetric;
    limit: number;
    cell_size_m: number;
    sample_count: number;
    cell_count: number;
    stats: {
        min: number | null;
        max: number | null;
    };
    points: HeatmapPoint[];
    generated_at: string;
}

type HeatmapMetric = Exclude<MetricKey, 'battery_pct'>;
type MapMode = 'robots' | 'paths' | 'heatmap';

const HEATMAP_METRIC_KEYS = METRIC_KEYS.filter(
    (metric): metric is HeatmapMetric => metric !== 'battery_pct',
);

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
    { key: 'metrics', label: t('pages.telemetry_logs.table.columns.metrics') },
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

    if (robotPatches.value.size === 0) {
        return base;
    }

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
const mapMode = ref<MapMode>('robots');
const heatmapMetric = ref<HeatmapMetric>('pm25');
const heatmapSnapshot = ref<HeatmapSnapshot | null>(null);
const isHeatmapLoading = ref(false);
const heatmapError = ref<string | null>(null);
const isHeatmapStale = ref(false);

let map: L.Map | null = null;
let zoneLayer: L.Polygon | null = null;
let heatmapLayer: L.LayerGroup | null = null;
const markerLayers = new Map<number, L.Marker>();
const pathLayers = new Map<number, L.Polyline>();
const pathCache = new Map<number, L.LatLngTuple[]>();

const heatmapMetricLabel = computed(() =>
    t(`pages.telemetry_logs.metrics.${heatmapMetric.value}.label`),
);
const heatmapMetricUnit = computed(() =>
    t(`pages.telemetry_logs.metrics.${heatmapMetric.value}.unit`),
);
const mapDescription = computed(() => {
    if (mapMode.value === 'heatmap') {
        return t('pages.zones.show.heatmap.description');
    }

    if (mapMode.value === 'paths') {
        return t('pages.zones.show.map.paths_description');
    }

    return t('pages.zones.show.map.description');
});

const formatMetric = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (typeof value === 'number') {
        return Number.isInteger(value) ? String(value) : value.toFixed(2);
    }

    return String(value);
};

const formatHeatmapValue = (value: number | null): string => {
    if (value == null) {
        return '—';
    }

    return formatMetric(value) + heatmapMetricUnit.value;
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
    const metrics = (robot.latest_metrics ?? {}) as Record<string, unknown>;

    const metricsHtml = METRIC_KEYS.filter((key) => metrics[key] != null)
        .map(
            (key) =>
                `<div><strong>${t('pages.telemetry_logs.metrics.' + key + '.label')}:</strong> ` +
                `${formatMetric(metrics[key])}${t('pages.telemetry_logs.metrics.' + key + '.unit')}</div>`,
        )
        .join('');

    return `
        <div class="space-y-2">
            <div>
                <div style="font-weight:600;">${robot.name}</div>
                <div style="color:#64748b;font-size:12px;">${robot.status_label}</div>
            </div>
            <div style="font-size:13px;display:grid;gap:4px;">
                ${metricsHtml}
                <div><strong>${t('pages.zones.show.map.latest_recorded_at')}:</strong> ${robot.latest_recorded_at ?? '—'}</div>
            </div>
        </div>
    `;
};

const heatmapColor = (intensity: number): string => {
    const hue = 210 - Math.round(Math.max(0, Math.min(1, intensity)) * 210);
    const lightness = 62 - Math.round(Math.max(0, Math.min(1, intensity)) * 24);

    return `hsl(${hue}, 88%, ${lightness}%)`;
};

const ensureHeatmapLayer = (): L.LayerGroup | null => {
    if (!map) {
        return null;
    }

    if (!heatmapLayer) {
        heatmapLayer = L.layerGroup();
    }

    return heatmapLayer;
};

const syncLayerVisibility = () => {
    if (!map) {
        return;
    }

    const showRobots = mapMode.value !== 'heatmap';
    const showPaths = mapMode.value === 'paths';
    const showHeatmap = mapMode.value === 'heatmap';

    markerLayers.forEach((marker) => {
        const isOnMap = map!.hasLayer(marker);

        if (showRobots && !isOnMap) {
            marker.addTo(map!);
        } else if (!showRobots && isOnMap) {
            marker.remove();
        }
    });

    pathLayers.forEach((polyline) => {
        const isOnMap = map!.hasLayer(polyline);

        if (showPaths && !isOnMap) {
            polyline.addTo(map!);
        } else if (!showPaths && isOnMap) {
            polyline.remove();
        }
    });

    const layer = ensureHeatmapLayer();

    if (!layer) {
        return;
    }

    const heatmapIsOnMap = map.hasLayer(layer);

    if (showHeatmap && !heatmapIsOnMap) {
        layer.addTo(map);
    } else if (!showHeatmap && heatmapIsOnMap) {
        layer.remove();
    }
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
        const polyline = L.polyline(points, {
            color: '#94a3b8',
            weight: 2,
            opacity: 0.4,
        });
        pathLayers.set(robotId, polyline);
    }

    refreshPolylineStyles();
    syncLayerVisibility();
};

const fetchPath = async (robotId: number) => {
    if (pathCache.has(robotId)) {
        drawPath(robotId, pathCache.get(robotId)!);

        return;
    }

    const res = await fetch(`/robots/${robotId}/path`);
    const data: { lat: string | number; lng: string | number }[] =
        await res.json();
    const points: L.LatLngTuple[] = data.map((p) => [
        Number(p.lat),
        Number(p.lng),
    ]);

    pathCache.set(robotId, points);
    drawPath(robotId, points);
};

const focusRobot = async (
    robotId: number,
    openPopup = false,
    switchToPathMode = false,
) => {
    if (switchToPathMode) {
        mapMode.value = 'paths';
    }

    selectedRobotId.value = robotId;
    refreshPolylineStyles();

    const marker = markerLayers.get(robotId);

    if (marker && map) {
        map.panTo(marker.getLatLng(), { animate: true });
    } else if (map) {
        const robot = robotsData.value.find((item) => item.id === robotId);

        if (robot?.lat != null && robot?.lng != null) {
            map.panTo([Number(robot.lat), Number(robot.lng)], {
                animate: true,
            });
        }
    }

    if (openPopup && marker && mapMode.value !== 'heatmap') {
        marker.openPopup();
    }

    if (mapMode.value === 'paths' || switchToPathMode) {
        await fetchPath(robotId);
    }
};

const clearHeatmap = () => {
    ensureHeatmapLayer()?.clearLayers();
};

const renderHeatmap = () => {
    const layer = ensureHeatmapLayer();

    if (!layer) {
        return;
    }

    clearHeatmap();

    if (!heatmapSnapshot.value) {
        syncLayerVisibility();

        return;
    }

    heatmapSnapshot.value.points.forEach((point) => {
        const fillColor = heatmapColor(point.intensity);
        const radius = heatmapSnapshot.value!.cell_size_m * 0.75;

        L.circle([point.lat, point.lng], {
            radius,
            color: fillColor,
            weight: 1,
            opacity: 0.7,
            fillColor,
            fillOpacity: 0.22 + point.intensity * 0.5,
        })
            .bindPopup(
                `
                    <div style="display:grid;gap:4px;min-width:180px;">
                        <div style="font-weight:600;">${heatmapMetricLabel.value}</div>
                        <div><strong>${t('pages.zones.show.heatmap.popup.value')}:</strong> ${formatHeatmapValue(point.value)}</div>
                        <div><strong>${t('pages.zones.show.heatmap.popup.samples')}:</strong> ${point.count}</div>
                        <div><strong>${t('pages.zones.show.heatmap.popup.recorded_at')}:</strong> ${point.recorded_at ?? '—'}</div>
                    </div>
                `,
            )
            .addTo(layer);
    });

    syncLayerVisibility();
};

const refreshHeatmapSnapshot = async () => {
    isHeatmapLoading.value = true;
    heatmapError.value = null;

    try {
        const params = new URLSearchParams({
            metric: heatmapMetric.value,
        });
        const response = await fetch(
            `/zones/${zoneData.value.id}/heatmap?${params.toString()}`,
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        if (!response.ok) {
            throw new Error('Failed to fetch heatmap snapshot.');
        }

        heatmapSnapshot.value = (await response.json()).data as HeatmapSnapshot;
        isHeatmapStale.value = false;
        renderHeatmap();
    } catch {
        heatmapError.value = t('pages.zones.show.heatmap.fetch_error');
        heatmapSnapshot.value = null;
        clearHeatmap();
    } finally {
        isHeatmapLoading.value = false;
    }
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
        }).bindPopup(buildPopupContent(robot));

        marker.on('click', () => focusRobot(robot.id));
        markerLayers.set(robot.id, marker);
    });

    renderHeatmap();
    refreshPolylineStyles();
    syncLayerVisibility();
};

const handleRealtimeEvent = (event: {
    telemetry_log: TelemetryLogRow;
    robot: RealtimeRobotPatch & { id: number };
}) => {
    const { telemetry_log: log, robot } = event;

    realtimeLogs.value = [log, ...realtimeLogs.value].slice(0, 100);
    isHeatmapStale.value = true;

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
    const patchedRobot = {
        ...robotsData.value.find((r) => r.id === robot.id),
        ...robot,
    } as Robot;

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

    echo?.private(`zone.${zoneData.value.id}`).listen(
        '.TelemetryLogStored',
        handleRealtimeEvent,
    );
});

onBeforeUnmount(() => {
    echo?.leave(`zone.${zoneData.value.id}`);

    map?.remove();
    map = null;
    zoneLayer = null;
    heatmapLayer = null;
    markerLayers.clear();
    pathLayers.clear();
    pathCache.clear();
});

watch(selectedRobotId, () => {
    refreshPolylineStyles();
});

watch(mapMode, async (mode) => {
    syncLayerVisibility();

    if (mode === 'paths' && selectedRobotId.value != null) {
        await fetchPath(selectedRobotId.value);
    }

    if (
        mode === 'heatmap' &&
        !heatmapSnapshot.value &&
        !isHeatmapLoading.value
    ) {
        await refreshHeatmapSnapshot();
    }
});

watch(heatmapMetric, (metric, prevMetric) => {
    if (metric === prevMetric) {
        return;
    }

    heatmapSnapshot.value = null;
    heatmapError.value = null;
    isHeatmapStale.value = true;
    clearHeatmap();
    syncLayerVisibility();
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
                    <div class="border-b px-4 py-4">
                        <div class="space-y-4">
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    size="sm"
                                    :variant="
                                        mapMode === 'robots'
                                            ? 'default'
                                            : 'outline'
                                    "
                                    @click="mapMode = 'robots'"
                                >
                                    <Bot class="h-4 w-4" />
                                    {{ t('pages.zones.show.map.modes.robots') }}
                                </Button>
                                <Button
                                    size="sm"
                                    :variant="
                                        mapMode === 'paths'
                                            ? 'default'
                                            : 'outline'
                                    "
                                    @click="mapMode = 'paths'"
                                >
                                    <Route class="h-4 w-4" />
                                    {{ t('pages.zones.show.map.modes.paths') }}
                                </Button>
                                <Button
                                    size="sm"
                                    :variant="
                                        mapMode === 'heatmap'
                                            ? 'default'
                                            : 'outline'
                                    "
                                    @click="mapMode = 'heatmap'"
                                >
                                    <ThermometerSun class="h-4 w-4" />
                                    {{
                                        t('pages.zones.show.map.modes.heatmap')
                                    }}
                                </Button>
                            </div>

                            <p class="max-w-2xl text-sm text-muted-foreground">
                                {{ mapDescription }}
                            </p>

                            <div
                                v-if="mapMode === 'heatmap'"
                                class="relative z-[1200] border-t border-dashed pt-4"
                            >
                                <div
                                    class="flex flex-col gap-3 sm:flex-row sm:items-center"
                                >
                                    <Select v-model="heatmapMetric">
                                        <SelectTrigger
                                            class="w-full sm:max-w-md sm:min-w-72"
                                        >
                                            <SelectValue
                                                :placeholder="
                                                    t(
                                                        'pages.zones.show.heatmap.metric_placeholder',
                                                    )
                                                "
                                            />
                                        </SelectTrigger>
                                        <SelectContent class="z-[1300]">
                                            <SelectItem
                                                v-for="metric in HEATMAP_METRIC_KEYS"
                                                :key="metric"
                                                :value="metric"
                                            >
                                                {{
                                                    t(
                                                        `pages.telemetry_logs.metrics.${metric}.label`,
                                                    )
                                                }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>

                                    <Button
                                        :variant="
                                            !heatmapSnapshot || isHeatmapStale
                                                ? 'destructive'
                                                : 'default'
                                        "
                                        :disabled="isHeatmapLoading"
                                        @click="refreshHeatmapSnapshot"
                                    >
                                        <RefreshCw
                                            class="h-4 w-4"
                                            :class="{
                                                'animate-spin':
                                                    isHeatmapLoading,
                                            }"
                                        />
                                        {{
                                            t(
                                                'pages.zones.show.heatmap.refresh',
                                            )
                                        }}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div
                            ref="mapEl"
                            class="min-h-[70vh] w-full xl:h-screen xl:max-h-[calc(100vh-10rem)]"
                        />

                        <div
                            v-if="
                                mapMode === 'heatmap' &&
                                heatmapSnapshot &&
                                heatmapSnapshot.stats.min != null &&
                                heatmapSnapshot.stats.max != null
                            "
                            class="pointer-events-none absolute bottom-4 left-4 z-[500] rounded-lg border bg-white/95 px-3 py-2 shadow-sm"
                        >
                            <div class="mb-2 flex items-center gap-2 text-xs">
                                <MapIcon class="h-3.5 w-3.5 text-slate-500" />
                                <span class="font-medium">
                                    {{ t('pages.zones.show.heatmap.legend') }}
                                </span>
                            </div>
                            <div class="heatmap-legend-gradient" />
                            <div
                                class="mt-2 flex items-center justify-between gap-6 text-[11px] text-slate-600"
                            >
                                <span>
                                    {{
                                        formatHeatmapValue(
                                            heatmapSnapshot.stats.min,
                                        )
                                    }}
                                </span>
                                <span>
                                    {{
                                        formatHeatmapValue(
                                            heatmapSnapshot.stats.max,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
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
                        @click="focusRobot(robot.id, true, true)"
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
                <Activity class="h-4 w-4 text-muted-foreground" />
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

                <template #cell-metrics="{ row }">
                    <MetricsList :metrics="row.metrics" />
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

.heatmap-legend-gradient {
    height: 0.6rem;
    width: 13rem;
    border-radius: 9999px;
    background: linear-gradient(
        90deg,
        hsl(210 88% 62%) 0%,
        hsl(150 88% 52%) 35%,
        hsl(50 95% 54%) 68%,
        hsl(0 88% 38%) 100%
    );
}
</style>
