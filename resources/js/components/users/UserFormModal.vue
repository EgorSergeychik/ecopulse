<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Role {
    id: string;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    role_id: string;
}

const props = defineProps<{
    open: boolean;
    user?: User;
    roles: Role[];
}>();

const emit = defineEmits(['update:open']);

const { t } = useI18n();

const form = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
});

const passwordPlaceholder = computed(() => {
    return props.user
        ? t('pages.users.modal.fields.password_placeholder_edit')
        : t('pages.users.modal.fields.password_placeholder_create');
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            if (props.user) {
                form.name = props.user.name;
                form.email = props.user.email;
                form.role = props.user.role_id;
                form.password = '';
            } else {
                form.reset();
            }
        }
    },
);

const submit = () => {
    if (props.user) {
        form.put(`/users/${props.user.id}`, {
            onSuccess: () => emit('update:open', false),
        });
    } else {
        form.post('/users', {
            onSuccess: () => emit('update:open', false),
        });
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    {{
                        user
                            ? t('pages.users.modal.title.edit')
                            : t('pages.users.modal.title.create')
                    }}
                </DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">{{
                        t('pages.users.modal.fields.name')
                    }}</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="space-y-2">
                    <Label for="email">{{
                        t('pages.users.modal.fields.email')
                    }}</Label>
                    <Input id="email" type="email" v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="space-y-2">
                    <Label for="role">{{
                        t('pages.users.modal.fields.role')
                    }}</Label>
                    <Select v-model="form.role">
                        <SelectTrigger id="role" class="w-full">
                            <SelectValue
                                :placeholder="
                                    t(
                                        'pages.users.modal.fields.role_placeholder',
                                    )
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.role" />
                </div>
                <div class="space-y-2">
                    <Label for="password">{{
                        t('pages.users.modal.fields.password')
                    }}</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        :placeholder="passwordPlaceholder"
                    />
                    <InputError :message="form.errors.password" />
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                    >
                        {{ t('common.buttons.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ t('common.buttons.save') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
