<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import RowActionsMenu from '@/components/RowActionsMenu.vue';
import { Button } from '@/components/ui/button';
import {
    DataTable,
    DataTablePagination,
    DataTableSearch,
} from '@/components/ui/table';
import type { TableColumn } from '@/components/ui/table';
import UserFormModal from '@/components/users/UserFormModal.vue';
import { usePermission } from '@/composables/usePermission';
import { index as usersIndex } from '@/routes/users';
import { Permission } from '@/types/permissions';

defineProps<{
    users: any;
    roles: { data: { id: string; name: string }[] };
    filters: { search: string | null };
}>();

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'pages.users.title',
                href: usersIndex(),
            },
        ],
    },
});

const { can } = usePermission();

const isModalOpen = ref(false);
const editingUser = ref<any | null>(undefined);

const columns = computed<TableColumn[]>(() => [
    { key: 'name', label: t('pages.users.table.columns.name') },
    { key: 'email', label: t('pages.users.table.columns.email') },
    { key: 'role', label: t('pages.users.table.columns.role') },
    { key: 'created_at', label: t('pages.users.table.columns.created_at') },
    {
        key: 'actions',
        label: t('pages.users.table.columns.actions'),
        align: 'right',
    },
]);

const openCreateModal = () => {
    editingUser.value = null;
    isModalOpen.value = true;
};

const openEditModal = (user: any) => {
    editingUser.value = user;
    isModalOpen.value = true;
};

const deleteUser = (user: any) => {
    if (confirm(t('pages.users.confirm_delete', { name: user.name }))) {
        router.delete(`/users/${user.id}`);
    }
};
</script>

<template>
    <Head :title="t('pages.users.title')" />

    <div class="space-y-6 p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading
                :title="t('pages.users.title')"
                :description="t('pages.users.description')"
            />
            <div
                class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center"
            >
                <DataTableSearch :model-value="filters.search" />
                <Button
                    v-if="can(Permission.CreateUsers)"
                    @click="openCreateModal"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    {{ t('pages.users.header.buttons.create') }}
                </Button>
            </div>
        </div>

        <DataTable
            :columns="columns"
            :data="users.data"
            :empty-text="t('pages.users.table.empty')"
        >
            <template #cell-name="{ row }">
                <span class="font-medium">{{ row.name }}</span>
            </template>
            <template #cell-actions="{ row }">
                <div class="flex justify-end">
                    <RowActionsMenu
                        :menu-label="t('common.actions.open_menu')"
                        :groups="[
                            {
                                items: [
                                    ...(can(Permission.UpdateUsers)
                                        ? [{
                                            label: t('common.actions.edit'),
                                            icon: Pencil,
                                            onSelect: () => openEditModal(row),
                                        }]
                                        : []),
                                    ...(can(Permission.DeleteUsers)
                                        ? [{
                                            label: t('common.actions.delete'),
                                            icon: Trash,
                                            variant: 'destructive' as const,
                                            onSelect: () => deleteUser(row),
                                        }]
                                        : []),
                                ],
                            },
                        ]"
                    />
                </div>
            </template>
        </DataTable>

        <DataTablePagination :meta="users.meta" />
    </div>

    <UserFormModal
        v-model:open="isModalOpen"
        :user="editingUser"
        :roles="roles.data"
    />
</template>
