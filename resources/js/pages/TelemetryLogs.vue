<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import {
    DataTable,
    DataTablePagination,
    DataTableSearch,
} from '@/components/ui/table';
import type { TableColumn } from '@/components/ui/table';

defineProps<{
    telemetryLogs: any;
    filters: { search: string | null };
}>();

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'pages.telemetry_logs.title',
                href: '/telemetry-logs',
            },
        ],
    },
});

const columns = computed<TableColumn[]>(() => [
    { key: 'robot_name', label: t('pages.telemetry_logs.table.columns.robot') },
    { key: 'zone_name', label: t('pages.telemetry_logs.table.columns.zone') },
    { key: 'lat', label: t('pages.telemetry_logs.table.columns.lat') },
    { key: 'lng', label: t('pages.telemetry_logs.table.columns.lng') },
    { key: 'battery_pct', label: t('pages.telemetry_logs.table.columns.battery_pct') },
    { key: 'co2', label: t('pages.telemetry_logs.table.columns.co2') },
    { key: 'noise_level', label: t('pages.telemetry_logs.table.columns.noise_level') },
    { key: 'recorded_at', label: t('pages.telemetry_logs.table.columns.recorded_at') },
]);
</script>

<template>
    <Head :title="t('pages.telemetry_logs.title')" />

    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <Heading
                :title="t('pages.telemetry_logs.title')"
                :description="t('pages.telemetry_logs.description')"
            />
            <DataTableSearch :model-value="filters.search" />
        </div>

        <DataTable
            :columns="columns"
            :data="telemetryLogs.data"
            :empty-text="t('pages.telemetry_logs.table.empty')"
        >
            <template #cell-robot_name="{ row }">
                <span class="font-medium">{{ row.robot_name }}</span>
            </template>

            <template #cell-battery_pct="{ row }">
                <span>{{ Number(row.battery_pct).toFixed(2) }}%</span>
            </template>

            <template #cell-co2="{ row }">
                <span>{{ row.co2 ?? '—' }}</span>
            </template>

            <template #cell-noise_level="{ row }">
                <span>{{ row.noise_level ?? '—' }}</span>
            </template>
        </DataTable>

        <DataTablePagination :meta="telemetryLogs.meta" />
    </div>
</template>
