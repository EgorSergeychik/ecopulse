<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Pencil, Plus, Trash } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import RobotFormModal from '@/components/robots/RobotFormModal.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    DataTable,
    DataTablePagination,
    DataTableSearch,
} from '@/components/ui/table';
import type { TableColumn } from '@/components/ui/table';
import { usePermission } from '@/composables/usePermission';
import { index as robotsIndex } from '@/routes/robots';
import type { Permission as PermissionType } from '@/types/permissions';
import { Permission } from '@/types/permissions';
import type { Robot } from '@/types/robot';

interface SelectZone {
    id: number;
    name: string;
}

defineProps<{
    robots: any;
    zones: SelectZone[];
    filters: { search: string | null };
}>();

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'pages.robots.title',
                href: robotsIndex(),
            },
        ],
    },
});

const { can } = usePermission();

const isModalOpen = ref(false);
const editingRobot = ref<Robot | null>(null);

const columns = computed<TableColumn[]>(() => [
    { key: 'name', label: t('pages.robots.table.columns.name') },
    { key: 'mac_address', label: t('pages.robots.table.columns.mac_address') },
    { key: 'zone_name', label: t('pages.robots.table.columns.zone') },
    { key: 'status', label: t('pages.robots.table.columns.status') },
    { key: 'battery_pct', label: t('pages.robots.table.columns.battery_pct') },
    { key: 'created_at', label: t('pages.robots.table.columns.created_at') },
    {
        key: 'actions',
        label: t('pages.robots.table.columns.actions'),
        align: 'right',
    },
]);

const openCreateModal = () => {
    editingRobot.value = null;
    isModalOpen.value = true;
};

const openEditModal = (robot: Robot) => {
    editingRobot.value = robot;
    isModalOpen.value = true;
};

const deleteRobot = (robot: Robot) => {
    if (confirm(t('pages.robots.confirm_delete', { name: robot.name }))) {
        router.delete(`/robots/${robot.id}`);
    }
};

const statusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        active: 'default',
        maintenance: 'secondary',
        error: 'destructive',
        offline: 'outline',
    };

    return variants[status] ?? 'outline';
};

const canAction = (permission: PermissionType) => can(permission);
</script>

<template>
    <Head :title="t('pages.robots.title')" />

    <div class="space-y-6 p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading
                :title="t('pages.robots.title')"
                :description="t('pages.robots.description')"
            />
            <div
                class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center"
            >
                <DataTableSearch :model-value="filters.search" />
                <Button
                    v-if="canAction(Permission.CreateRobots)"
                    @click="openCreateModal"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    {{ t('pages.robots.header.buttons.create') }}
                </Button>
            </div>
        </div>

        <DataTable
            :columns="columns"
            :data="robots.data"
            :empty-text="t('pages.robots.table.empty')"
        >
            <template #cell-name="{ row }">
                <span class="font-medium">{{ row.name }}</span>
            </template>

            <template #cell-mac_address="{ row }">
                <span>{{ row.mac_address || '—' }}</span>
            </template>

            <template #cell-status="{ row }">
                <Badge :variant="statusVariant(row.status)">
                    {{ row.status_label }}
                </Badge>
            </template>

            <template #cell-battery_pct="{ row }">
                <span>{{ Number(row.battery_pct).toFixed(2) }}%</span>
            </template>

            <template #cell-actions="{ row }">
                <div class="flex justify-end gap-2">
                    <Button
                        v-if="canAction(Permission.UpdateRobots)"
                        variant="outline"
                        size="icon"
                        @click="openEditModal(row)"
                    >
                        <Pencil class="h-4 w-4" />
                    </Button>
                    <Button
                        v-if="canAction(Permission.DeleteRobots)"
                        variant="destructive"
                        size="icon"
                        @click="deleteRobot(row)"
                    >
                        <Trash class="h-4 w-4" />
                    </Button>
                </div>
            </template>
        </DataTable>

        <DataTablePagination :meta="robots.meta" />
    </div>

    <RobotFormModal
        v-model:open="isModalOpen"
        :robot="editingRobot"
        :zones="zones"
    />
</template>
