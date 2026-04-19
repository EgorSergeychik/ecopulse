<script setup lang="ts">
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import '@geoman-io/leaflet-geoman-free';

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { LatLng } from '@/types/zone';

delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({ iconUrl: markerIcon, iconRetinaUrl: markerIcon2x, shadowUrl: markerShadow });

const props = defineProps<{
    lat: string | number;
    lng: string | number;
    zoom: string | number;
    polygon?: LatLng[] | null;
}>();

const emit = defineEmits<{
    'update:lat': [v: string];
    'update:lng': [v: string];
    'update:zoom': [v: string];
    'update:polygon': [v: LatLng[] | null];
}>();

const mapEl = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let centerMarker: L.Marker | null = null;
let polygonLayer: L.Polygon | null = null;
let skipMoveEnd = false;

const toLat  = () => parseFloat(String(props.lat))  || 50.45;
const toLng  = () => parseFloat(String(props.lng))  || 30.52;
const toZoom = () => parseInt(String(props.zoom))   || 12;

const capturePolygon = () => {
    if (!polygonLayer) return;
    const rings = polygonLayer.getLatLngs() as L.LatLng[][];
    emit('update:polygon', rings[0].map(ll => ({ lat: ll.lat, lng: ll.lng })));
};

const drawExistingPolygon = () => {
    if (!map) return;
    if (polygonLayer) { polygonLayer.remove(); polygonLayer = null; }
    if (!props.polygon || props.polygon.length < 3) return;
    polygonLayer = L.polygon(props.polygon.map(p => [p.lat, p.lng] as L.LatLngTuple), {
        color: 'hsl(142 76% 36%)',
        weight: 2,
        fillOpacity: 0.12,
    }).addTo(map);
    polygonLayer.on('pm:edit', capturePolygon);
};

onMounted(() => {
    if (!mapEl.value) return;

    map = L.map(mapEl.value).setView([toLat(), toLng()], toZoom());

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 22,
    }).addTo(map);

    centerMarker = L.marker([toLat(), toLng()], { draggable: true }).addTo(map);

    centerMarker.on('dragend', () => {
        const ll = centerMarker!.getLatLng();
        skipMoveEnd = true;
        map!.panTo(ll);
        map!.once('moveend', () => { skipMoveEnd = false; });
        emit('update:lat', ll.lat.toFixed(8));
        emit('update:lng', ll.lng.toFixed(8));
    });

    map.on('moveend', () => {
        if (skipMoveEnd) return;

        const center = map!.getCenter();

        centerMarker?.setLatLng(center);
        emit('update:lat', center.lat.toFixed(8));
        emit('update:lng', center.lng.toFixed(8));
    });

    map.on('zoomend', () => emit('update:zoom', String(map!.getZoom())));

    map.pm.addControls({
        position: 'topleft',
        drawMarker:       false,
        drawCircleMarker: false,
        drawPolyline:     false,
        drawPolygon:      true,
        drawCircle:       false,
        drawText:         false,
        drawRectangle:    true,
        editMode:         true,
        dragMode:         false,
        cutPolygon:       false,
        removalMode:      true,
        rotateMode:       false,
    });

    map.on('pm:create', (e: any) => {
        if (polygonLayer) polygonLayer.remove();

        polygonLayer = e.layer as L.Polygon;

        polygonLayer.on('pm:edit', capturePolygon);
        capturePolygon();
        map!.pm.disableDraw();
    });

    map.on('pm:remove', (e: any) => {
        if (e.layer === polygonLayer) {
            polygonLayer = null;
            emit('update:polygon', null);
        }
    });

    drawExistingPolygon();
});

onBeforeUnmount(() => {
    map?.remove();
    map = null; polygonLayer = null; centerMarker = null;
});

watch([() => props.lat, () => props.lng], () => {
    if (!map || !centerMarker) return;

    const ll: L.LatLngTuple = [toLat(), toLng()];

    centerMarker.setLatLng(ll);
    skipMoveEnd = true;
    map.panTo(ll);
    map.once('moveend', () => { skipMoveEnd = false; });
});

watch(() => props.zoom, () => map?.setZoom(toZoom()));
</script>

<template>
    <div ref="mapEl" class="h-72 w-full rounded-md border" />
</template>
