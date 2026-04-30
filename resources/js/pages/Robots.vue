<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useClipboard } from '@vueuse/core';
import { KeyRound, Pencil, Plus, Power, RefreshCw, Trash, TriangleAlert, Wrench } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import RobotFormModal from '@/components/robots/RobotFormModal.vue';
import RowActionsMenu from '@/components/RowActionsMenu.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
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

interface RobotTokenFlash {
    robot_id: number;
    robot_name: string;
    token: string;
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
const page = usePage();
const { copy } = useClipboard();

const isModalOpen = ref(false);
const editingRobot = ref<Robot | null>(null);
const isTokenDialogOpen = ref(false);
const tokenPayload = ref<RobotTokenFlash | null>(null);

const columns = computed<TableColumn[]>(() => [
    { key: 'name', label: t('pages.robots.table.columns.name') },
    { key: 'mac_address', label: t('pages.robots.table.columns.mac_address') },
    { key: 'zone_name', label: t('pages.robots.table.columns.zone') },
    { key: 'status', label: t('pages.robots.table.columns.status') },
    { key: 'battery_pct', label: t('pages.robots.table.columns.battery_pct') },
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

const regenerateRobotToken = (robot: Robot) => {
    router.post(`/robots/${robot.id}/token/regenerate`);
};

const nextTransitionState = (robot: Robot): 'maintenance' | 'offline' | null => {
    if (robot.status === 'active' || robot.status === 'error') {
        return 'maintenance';
    }

    if (robot.status === 'maintenance') {
        return 'offline';
    }

    return null;
};

const transitionRobot = (robot: Robot) => {
    const state = nextTransitionState(robot);

    if (!state) {
        return;
    }

    router.post(`/robots/${robot.id}/to/${state}`);
};

const isLowBattery = (robot: Robot): boolean => Number(robot.battery_pct) < 10;

const transitionLabel = (robot: Robot): string => {
    const state = nextTransitionState(robot);

    if (state === 'maintenance') {
        return t('pages.robots.actions.to_maintenance');
    }

    if (state === 'offline') {
        return t('pages.robots.actions.to_offline');
    }

    return '';
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

const copyRobotToken = async (event: MouseEvent) => {
    if (!tokenPayload.value?.token) {
        return;
    }

    await copy(tokenPayload.value.token);

    if (event.target instanceof HTMLInputElement) {
        event.target.select();
    }
};

watch(
    () => page.props.robot_token as RobotTokenFlash | null | undefined,
    (robotToken) => {
        if (!robotToken?.token) {
            return;
        }

        tokenPayload.value = robotToken;
        isTokenDialogOpen.value = true;
    },
    { immediate: true },
);
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
                <div class="flex justify-end">
                    <RowActionsMenu
                        :menu-label="t('common.actions.open_menu')"
                        :groups="[
                            {
                                items: [
                                    ...(canAction(Permission.UpdateRobots)
                                        ? [{
                                            label: t('common.actions.edit'),
                                            icon: Pencil,
                                            onSelect: () => openEditModal(row),
                                        }]
                                        : []),
                                    ...(canAction(Permission.DeleteRobots)
                                        ? [{
                                            label: t('common.actions.delete'),
                                            icon: Trash,
                                            variant: 'destructive' as const,
                                            onSelect: () => deleteRobot(row),
                                        }]
                                        : []),
                                ],
                            },
                            {
                                label: t('pages.robots.actions.token_group'),
                                items: canAction(Permission.UpdateRobots)
                                    ? [{
                                        label: t('pages.robots.actions.regenerate_token'),
                                        icon: RefreshCw,
                                        onSelect: () => regenerateRobotToken(row),
                                    }]
                                    : [],
                            },
                            {
                                label: t('pages.robots.actions.status_group'),
                                items: canAction(Permission.TransitionRobots) && nextTransitionState(row)
                                    ? [{
                                        label: transitionLabel(row),
                                        icon: isLowBattery(row)
                                            ? TriangleAlert
                                            : nextTransitionState(row) === 'maintenance'
                                                ? Wrench
                                                : Power,
                                        variant: isLowBattery(row) ? 'destructive' as const : 'default' as const,
                                        onSelect: () => transitionRobot(row),
                                    }]
                                    : [],
                            },
                        ]"
                    />
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

    <Dialog v-model:open="isTokenDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <KeyRound class="h-4 w-4" />
                    {{ t('pages.robots.token_dialog.title') }}
                </DialogTitle>
                <DialogDescription>
                    {{ t('pages.robots.token_dialog.description') }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="tokenPayload" class="space-y-4">
                <div class="rounded-md border bg-muted/40 p-3 text-sm">
                    <span class="font-medium">{{ tokenPayload.robot_name }}</span>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium">
                        {{ t('pages.robots.token_dialog.token_label') }}
                    </label>
                    <Input
                        :model-value="tokenPayload.token"
                        readonly
                        class="cursor-copy"
                        @click="copyRobotToken"
                    />
                </div>
            </div>

            <DialogFooter>
                <Button type="button" @click="isTokenDialogOpen = false">
                    {{ t('pages.robots.token_dialog.close') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
