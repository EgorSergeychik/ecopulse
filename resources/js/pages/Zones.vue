<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { MapPin, Pencil, Plus, Trash } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import RowActionsMenu from '@/components/RowActionsMenu.vue';
import { Button } from '@/components/ui/button';
import {
    DataTablePagination,
    DataTableSearch,
    DataView,
} from '@/components/ui/table';
import ZoneFormModal from '@/components/zones/ZoneFormModal.vue';
import { usePermission } from '@/composables/usePermission';
import { index as zonesIndex } from '@/routes/zones';
import { Permission } from '@/types/permissions';

interface SelectUser {
    id: number;
    name: string;
}

const props = defineProps<{
    zones: any;
    users: SelectUser[];
    filters: { search: string | null };
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

const { can } = usePermission();

const isModalOpen = ref(false);
const editingZone = ref<any | null>(undefined);

const openCreateModal = () => {
    editingZone.value = null;
    isModalOpen.value = true;
};

const openEditModal = (zone: any) => {
    editingZone.value = zone;
    isModalOpen.value = true;
};

const deleteZone = (zone: any) => {
    if (confirm(t('pages.zones.confirm_delete', { name: zone.name }))) {
        router.delete(`/zones/${zone.id}`);
    }
};
</script>

<template>
    <Head :title="t('pages.zones.title')" />

    <div class="space-y-6 p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading
                :title="t('pages.zones.title')"
                :description="t('pages.zones.description')"
            />
            <div
                class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center"
            >
                <DataTableSearch :model-value="filters.search" />
                <Button
                    v-if="can(Permission.CreateZones)"
                    @click="openCreateModal"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    {{ t('pages.zones.header.buttons.create') }}
                </Button>
            </div>
        </div>

        <DataView :data="zones.data" :empty-text="t('pages.zones.cards.empty')">
            <template #thumbnail="{ row }">
                <div class="relative h-40 w-full overflow-hidden bg-muted">
                    <img
                        v-if="row.thumbnail_url"
                        :src="row.thumbnail_url"
                        :alt="row.name"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center text-muted-foreground"
                    >
                        <MapPin class="h-10 w-10 opacity-30" />
                    </div>
                </div>
            </template>

            <template #title="{ row }">
                <p class="truncate font-semibold">{{ row.name }}</p>
            </template>

            <template #body="{ row }">
                <div class="mt-1 space-y-0.5 text-sm text-muted-foreground">
                    <p>
                        <span class="font-medium"
                            >{{ t('pages.zones.cards.lat') }}:</span
                        >
                        {{ row.center_lat }}
                    </p>
                    <p>
                        <span class="font-medium"
                            >{{ t('pages.zones.cards.lng') }}:</span
                        >
                        {{ row.center_lng }}
                    </p>
                    <p>
                        <span class="font-medium"
                            >{{ t('pages.zones.cards.zoom') }}:</span
                        >
                        {{ row.default_zoom }}
                    </p>
                </div>
            </template>

            <template #rightbar="{ row }">
                <div class="flex">
                    <RowActionsMenu
                        :menu-label="t('common.actions.open_menu')"
                        :groups="[
                            {
                                items: [
                                    ...(can(Permission.UpdateZones)
                                        ? [{
                                            label: t('common.actions.edit'),
                                            icon: Pencil,
                                            onSelect: () => openEditModal(row),
                                        }]
                                        : []),
                                    ...(can(Permission.DeleteZones)
                                        ? [{
                                            label: t('common.actions.delete'),
                                            icon: Trash,
                                            variant: 'destructive' as const,
                                            onSelect: () => deleteZone(row),
                                        }]
                                        : []),
                                ],
                            },
                        ]"
                    />
                </div>
            </template>

            <template #footer="{ row }">
                <p class="text-xs text-muted-foreground">
                    {{ row.created_at }}
                </p>
            </template>
        </DataView>

        <DataTablePagination :meta="zones.meta" />
    </div>

    <ZoneFormModal
        v-model:open="isModalOpen"
        :zone="editingZone"
        :users="props.users"
    />
</template>
