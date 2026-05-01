<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CheckCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import RowActionsMenu from '@/components/RowActionsMenu.vue';
import { Badge } from '@/components/ui/badge';
import {
    DataTable,
    DataTablePagination,
    DataTableSearch,
} from '@/components/ui/table';
import type { TableColumn } from '@/components/ui/table';
import { usePermission } from '@/composables/usePermission';
import { Permission } from '@/types/permissions';

defineProps<{
    incidents: any;
    filters: { search: string | null };
}>();

const { t } = useI18n();
const { can } = usePermission();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'pages.incidents.title',
                href: '/incidents',
            },
        ],
    },
});

const columns = computed<TableColumn[]>(() => [
    { key: 'robot_name', label: t('pages.incidents.table.columns.robot') },
    { key: 'zone_name', label: t('pages.incidents.table.columns.zone') },
    { key: 'type_label', label: t('pages.incidents.table.columns.type') },
    { key: 'severity', label: t('pages.incidents.table.columns.severity') },
    { key: 'description', label: t('pages.incidents.table.columns.description') },
    { key: 'created_at', label: t('pages.incidents.table.columns.created_at') },
    { key: 'resolved_at', label: t('pages.incidents.table.columns.resolved_at') },
    { key: 'actions', label: t('pages.incidents.table.columns.actions'), align: 'right' },
]);

const severityVariant = (severity: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        warning: 'secondary',
        critical: 'destructive',
        fatal: 'destructive',
    };

    return variants[severity] ?? 'outline';
};

const resolveIncident = (incidentId: number) => {
    router.post(`/incidents/${incidentId}/resolve`);
};
</script>

<template>
    <Head :title="t('pages.incidents.title')" />

    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <Heading
                :title="t('pages.incidents.title')"
                :description="t('pages.incidents.description')"
            />
            <DataTableSearch :model-value="filters.search" />
        </div>

        <DataTable
            :columns="columns"
            :data="incidents.data"
            :empty-text="t('pages.incidents.table.empty')"
        >
            <template #cell-robot_name="{ row }">
                <span class="font-medium">{{ row.robot_name }}</span>
            </template>

            <template #cell-severity="{ row }">
                <Badge :variant="severityVariant(row.severity)">
                    {{ row.severity_label }}
                </Badge>
            </template>

            <template #cell-resolved_at="{ row }">
                <span>{{ row.resolved_at || '—' }}</span>
            </template>

            <template #cell-actions="{ row }">
                <div class="flex justify-end">
                    <RowActionsMenu
                        :menu-label="t('common.actions.open_menu')"
                        :groups="[
                            {
                                items: [
                                    ...(!row.is_resolved && can(Permission.ResolveIncidents)
                                        ? [{
                                            label: t('common.actions.resolve'),
                                            icon: CheckCheck,
                                            onSelect: () => resolveIncident(row.id),
                                        }]
                                        : []),
                                ],
                            },
                        ]"
                    />
                </div>
            </template>
        </DataTable>

        <DataTablePagination :meta="incidents.meta" />
    </div>
</template>
