<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, Trash } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import UserFormModal from '@/components/users/UserFormModal.vue';
import { usePermission } from '@/composables/usePermission';
import { index as usersIndex } from '@/routes/users';
import { Permission } from '@/types/permissions';

defineProps<{
    users: any;
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
const editingUser = ref(null);

const openCreateModal = () => {
    editingUser.value = null;
    isModalOpen.value = true;
};

const openEditModal = (user: any) => {
    editingUser.value = user;
    isModalOpen.value = true;
};

const deleteUser = (user: any) => {
    if (confirm(`Are you sure you want to delete ${user.name}?`)) {
        console.log(`Deleting user with ID: ${user.id}`);
    }
};
</script>

<template>
    <Head :title="t('pages.users.title')" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <Heading
                :title="t('pages.users.title')"
                :description="t('pages.users.description')"
            />
            <Button @click="openCreateModal" v-if="can(Permission.CreateUsers)">
                <Plus class="mr-2 h-4 w-4" />
                {{ t('pages.users.header.buttons.create') }}
            </Button>
        </div>

        <div class="rounded-md border bg-card">
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr
                            class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
                        >
                            <th
                                class="h-12 px-4 text-left align-middle font-medium text-muted-foreground"
                            >
                                {{ t('pages.users.table.columns.name') }}
                            </th>
                            <th
                                class="h-12 px-4 text-left align-middle font-medium text-muted-foreground"
                            >
                                {{ t('pages.users.table.columns.email') }}
                            </th>
                            <th
                                class="h-12 px-4 text-right align-middle font-medium text-muted-foreground"
                            >
                                {{ t('pages.users.table.columns.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
                        >
                            <td class="p-4 align-middle font-medium">
                                {{ user.name }}
                            </td>
                            <td class="p-4 align-middle">{{ user.email }}</td>
                            <td class="space-x-2 p-4 text-right align-middle">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    @click="openEditModal(user)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="icon"
                                    @click="deleteUser(user)"
                                >
                                    <Trash class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td
                                colspan="3"
                                class="p-8 text-center text-muted-foreground"
                            >
                                {{ t('pages.users.table.empty') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="users.links && users.links.length > 3"
            class="flex items-center justify-end space-x-2 py-4"
        >
            <template v-for="(link, k) in users.links" :key="k">
                <div
                    v-if="link.url === null"
                    class="mr-1 mb-1 rounded border px-4 py-3 text-sm leading-4 text-gray-400"
                    v-html="link.label"
                />
                <Link
                    v-else
                    class="mr-1 mb-1 rounded border px-4 py-3 text-sm leading-4 hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                    :class="{ 'bg-blue-700 text-white': link.active }"
                    :href="link.url"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>

    <UserFormModal v-model:open="isModalOpen" :user="editingUser" />
</template>
