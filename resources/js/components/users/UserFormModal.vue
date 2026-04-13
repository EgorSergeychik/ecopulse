<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    open: boolean;
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
    };
}>();

const emit = defineEmits(['update:open']);

const { t } = useI18n();

const form = useForm({
    name: props.user ? props.user.name : '',
    email: props.user ? props.user.email : '',
    role: props.user ? props.user.role : 'operator',
});

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        if (props.user) {
            form.name = props.user.name;
            form.email = props.user.email;
            form.role = props.user.role;
        } else {
            form.reset();
        }
    }
})

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
                <DialogTitle>{{
                    user ? t('pages.users.modal.title.edit') : t('pages.users.modal.title.create')
                }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">{{ t('pages.users.modal.fields.name') }}</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="space-y-2">
                    <Label for="email">{{ t('pages.users.modal.fields.email') }}</Label>
                    <Input id="email" type="email" v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                        >{{ t('common.buttons.cancel') }}</Button
                    >
                    <Button type="submit" :disabled="form.processing"
                        >{{ t('common.buttons.save') }}</Button
                    >
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
