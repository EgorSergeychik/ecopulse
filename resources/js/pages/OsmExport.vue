<script setup lang="ts">
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
import 'leaflet/dist/leaflet.css';
import { Head } from '@inertiajs/vue3';
import L from 'leaflet';
import '@geoman-io/leaflet-geoman-free';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import { Download, Map } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import SideListPanel from '@/components/SideListPanel.vue';
import { Button } from '@/components/ui/button';
import { download as downloadRoute } from '@/routes/webots';
import type { Zone } from '@/types/zone';

delete (L.Icon.Default.prototype as { _getIconUrl?: unknown })._getIconUrl;
L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
});

interface Bbox {
    south: number;
    west: number;
    north: number;
    east: number;
}

const props = defineProps<{
    zones: Zone[] | { data: Zone[] };
}>();

const { t } = useI18n();

const zonesData = Array.isArray(props.zones) ? props.zones : props.zones.data;

const mapEl = ref<HTMLElement | null>(null);
const selectedZoneId = ref<number | null>(null);
const bbox = ref<Bbox | null>(null);
const downloading = ref(false);
const validationError = ref<string | null>(null);

let map: L.Map | null = null;
let customRectLayer: L.Rectangle | null = null;
let zonePolygonLayer: L.Polygon | null = null;
let bboxRectLayer: L.Rectangle | null = null;

const clearCustomRectangle = () => {
    if (customRectLayer) {
        customRectLayer.remove();
        customRectLayer = null;
    }
};

const clearZoneOverlay = () => {
    if (zonePolygonLayer) {
        zonePolygonLayer.remove();
        zonePolygonLayer = null;
    }
    if (bboxRectLayer) {
        bboxRectLayer.remove();
        bboxRectLayer = null;
    }
};

const updateBboxFromCustomRect = () => {
    if (!customRectLayer) return;
    const bounds = customRectLayer.getBounds();
    bbox.value = {
        south: bounds.getSouth(),
        west: bounds.getWest(),
        north: bounds.getNorth(),
        east: bounds.getEast(),
    };
};

const selectZone = (zone: Zone) => {
    if (!map) return;

    clearCustomRectangle();
    clearZoneOverlay();
    selectedZoneId.value = zone.id;
    validationError.value = null;

    const points = zone.polygon;
    if (!points || points.length < 3) {
        bbox.value = null;
        return;
    }

    const latlngs = points.map((p) => [p.lat, p.lng] as L.LatLngTuple);

    zonePolygonLayer = L.polygon(latlngs, {
        color: '#94a3b8',
        weight: 2,
        fillColor: '#94a3b8',
        fillOpacity: 0.1,
    }).addTo(map);

    const bounds = L.latLngBounds(latlngs);
    bboxRectLayer = L.rectangle(bounds, {
        color: '#16a34a',
        weight: 2,
        fillColor: '#22c55e',
        fillOpacity: 0.08,
    }).addTo(map);

    map.fitBounds(bounds, { padding: [24, 24] });

    bbox.value = {
        south: bounds.getSouth(),
        west: bounds.getWest(),
        north: bounds.getNorth(),
        east: bounds.getEast(),
    };
};

const initializeMap = () => {
    if (!mapEl.value || map) return;

    map = L.map(mapEl.value).setView([50.45, 30.52], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 22,
    }).addTo(map);

    map.pm.addControls({
        position: 'topleft',
        drawMarker: false,
        drawCircleMarker: false,
        drawPolyline: false,
        drawPolygon: false,
        drawCircle: false,
        drawText: false,
        drawRectangle: true,
        editMode: true,
        dragMode: false,
        cutPolygon: false,
        removalMode: true,
        rotateMode: false,
    });

    map.on('pm:create', (e: any) => {
        if (e.shape !== 'Rectangle') return;

        clearCustomRectangle();
        clearZoneOverlay();
        selectedZoneId.value = null;
        validationError.value = null;

        customRectLayer = e.layer as L.Rectangle;
        customRectLayer.on('pm:edit', updateBboxFromCustomRect);
        updateBboxFromCustomRect();
        map!.pm.disableDraw();
    });

    map.on('pm:remove', (e: any) => {
        if (e.layer === customRectLayer) {
            customRectLayer = null;
            bbox.value = null;
        }
        if (e.layer === bboxRectLayer) {
            bboxRectLayer = null;
            zonePolygonLayer?.remove();
            zonePolygonLayer = null;
            selectedZoneId.value = null;
            bbox.value = null;
        }
    });
};

const downloadOsm = async () => {
    if (!bbox.value) {
        validationError.value = t('pages.osm_export.validation.no_rectangle');
        return;
    }

    validationError.value = null;
    downloading.value = true;

    try {
        const url = downloadRoute.url({
            query: {
                south: bbox.value.south,
                west: bbox.value.west,
                north: bbox.value.north,
                east: bbox.value.east,
            },
        });

        const response = await fetch(url, { credentials: 'same-origin' });

        if (!response.ok) {
            throw new Error(String(response.status));
        }

        const blob = await response.blob();
        const objectUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = objectUrl;
        a.download = 'export.osm';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(objectUrl);
    } catch {
        validationError.value = t('pages.osm_export.validation.download_error');
    } finally {
        downloading.value = false;
    }
};

onMounted(() => {
    initializeMap();
});

onBeforeUnmount(() => {
    map?.remove();
    map = null;
    customRectLayer = null;
    zonePolygonLayer = null;
    bboxRectLayer = null;
});
</script>

<template>
    <Head :title="t('pages.osm_export.title')" />

    <div class="space-y-6 p-6">
        <Heading
            :title="t('pages.osm_export.title')"
            :description="t('pages.osm_export.description')"
        />

        <section class="grid gap-6 xl:grid-cols-4">
            <div class="xl:col-span-3">
                <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div
                        ref="mapEl"
                        class="min-h-[70vh] w-full xl:h-screen xl:max-h-[calc(100vh-10rem)]"
                    />
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <SideListPanel
                    :title="t('pages.osm_export.zones.title')"
                    :empty-text="t('pages.osm_export.zones.empty')"
                    :is-empty="zonesData.length === 0"
                >
                    <template #icon>
                        <Map class="h-4 w-4 text-muted-foreground" />
                    </template>

                    <button
                        v-for="zone in zonesData"
                        :key="zone.id"
                        type="button"
                        class="flex w-full items-center justify-between rounded-lg border p-3 text-left transition-colors hover:bg-muted/40"
                        :class="
                            selectedZoneId === zone.id
                                ? 'border-primary bg-primary/5'
                                : 'border-border'
                        "
                        @click="selectZone(zone)"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ zone.name }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ Number(zone.center_lat).toFixed(4) }},
                                {{ Number(zone.center_lng).toFixed(4) }}
                            </p>
                        </div>
                    </button>
                </SideListPanel>

                <div class="rounded-xl border bg-card p-4 shadow-sm">
                    <p
                        v-if="!bbox"
                        class="mb-3 text-sm text-muted-foreground"
                    >
                        {{ t('pages.osm_export.hint') }}
                    </p>

                    <Button
                        class="w-full gap-2"
                        :disabled="downloading"
                        @click="downloadOsm"
                    >
                        <Download class="h-4 w-4" />
                        {{ t('pages.osm_export.download') }}
                    </Button>

                    <p
                        v-if="validationError"
                        class="mt-2 text-sm text-destructive"
                    >
                        {{ validationError }}
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>
