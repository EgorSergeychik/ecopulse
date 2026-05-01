<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Robot } from '@/types/robot';

interface SelectZone {
    id: number;
    name: string;
}

const props = defineProps<{
    open: boolean;
    robot?: Robot | null;
    zones: SelectZone[];
}>();

const emit = defineEmits(['update:open']);

const { t } = useI18n();

const form = useForm({
    name: '',
    mac_address: '',
    zone_id: '',
});

const resetCreateForm = () => {
    form.clearErrors();
    form.name = '';
    form.mac_address = '';
    form.zone_id = props.zones[0] ? String(props.zones[0].id) : '';
};

watch(
    () => props.open,
    (isOpen) => {
        if (! isOpen) {
            return;
        }

        if (props.robot) {
            form.name = props.robot.name;
            form.mac_address = props.robot.mac_address ?? '';
            form.zone_id = String(props.robot.zone_id);

            return;
        }

        resetCreateForm();
    },
);

const submit = () => {
    if (props.robot) {
        form.put(`/robots/${props.robot.id}`, {
            onSuccess: () => emit('update:open', false),
        });

        return;
    }

    form.post('/robots', {
        onSuccess: () => {
            resetCreateForm();
            emit('update:open', false);
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    {{
                        robot
                            ? t('pages.robots.modal.title.edit')
                            : t('pages.robots.modal.title.create')
                    }}
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="robot-name">{{
                        t('pages.robots.modal.fields.name')
                    }}</Label>
                    <Input id="robot-name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="robot-mac">{{
                        t('pages.robots.modal.fields.mac_address')
                    }}</Label>
                    <Input id="robot-mac" v-model="form.mac_address" />
                    <InputError :message="form.errors.mac_address" />
                </div>

                <div class="space-y-2">
                    <Label for="robot-zone">{{
                        t('pages.robots.modal.fields.zone')
                    }}</Label>
                    <Select v-model="form.zone_id">
                        <SelectTrigger id="robot-zone" class="w-full">
                            <SelectValue
                                :placeholder="
                                    t('pages.robots.modal.fields.zone_placeholder')
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="zone in zones"
                                :key="zone.id"
                                :value="String(zone.id)"
                            >
                                {{ zone.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.zone_id" />
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
