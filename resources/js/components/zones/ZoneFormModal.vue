<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
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
import { Multiselect } from '@/components/ui/multiselect';
import ZoneMap from '@/components/zones/ZoneMap.vue';
import type { LatLng, Zone } from '@/types/zone';

interface SelectUser {
    id: number;
    name: string;
}

const props = defineProps<{
    open: boolean;
    zone?: Zone;
    users: SelectUser[];
}>();

const emit = defineEmits(['update:open']);

const { t } = useI18n();

const fileInput = ref<HTMLInputElement | null>(null);
const showMap = ref(false);

const form = useForm({
    name: '',
    center_lat: '50.45',
    center_lng: '30.52',
    default_zoom: '12',
    polygon: null as LatLng[] | null,
    thumbnail: null as File | null,
    user_ids: [] as number[],
});

const resetCreateForm = () => {
    form.clearErrors();
    form.name = '';
    form.center_lat = '50.45';
    form.center_lng = '30.52';
    form.default_zoom = '12';
    form.polygon = null;
    form.thumbnail = null;
    form.user_ids = [];
};

const userOptions = computed(() =>
    props.users.map((u) => ({ value: u.id, label: u.name })),
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            if (props.zone) {
                form.name = props.zone.name;
                form.center_lat = String(props.zone.center_lat);
                form.center_lng = String(props.zone.center_lng);
                form.default_zoom = String(props.zone.default_zoom);
                form.polygon = props.zone.polygon ?? null;
                form.thumbnail = null;
                form.user_ids = [...(props.zone.user_ids ?? [])];
            } else {
                resetCreateForm();
            }

            if (fileInput.value) {
                fileInput.value.value = '';
            }

            showMap.value = false;
            setTimeout(() => {
                showMap.value = true;
            }, 150);
        } else {
            showMap.value = false;
        }
    },
);

const handleFileChange = (e: Event) => {
    form.thumbnail = (e.target as HTMLInputElement).files?.[0] ?? null;
};

const submit = () => {
    const url = props.zone ? `/zones/${props.zone.id}` : '/zones';
    const options = {
        forceFormData: true,
        onSuccess: () => emit('update:open', false),
    };

    const transformed = form.transform((data) => ({
        ...data,
        polygon: data.polygon ? JSON.stringify(data.polygon) : null,
    }));

    if (props.zone) {
        transformed.put(url, options);
    } else {
        transformed.post(url, {
            ...options,
            onSuccess: () => {
                resetCreateForm();
                emit('update:open', false);
            },
        });
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="flex max-h-[90vh] flex-col sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>
                    {{
                        zone
                            ? t('pages.zones.modal.title.edit')
                            : t('pages.zones.modal.title.create')
                    }}
                </DialogTitle>
            </DialogHeader>

            <form
                @submit.prevent="submit"
                class="flex flex-col gap-4 overflow-y-auto pr-1"
            >
                <!-- Map -->
                <div class="space-y-1">
                    <Label>{{ t('pages.zones.modal.fields.map') }}</Label>
                    <p class="text-xs text-muted-foreground">
                        {{ t('pages.zones.modal.fields.map_hint') }}
                    </p>
                    <ZoneMap
                        v-if="showMap"
                        :lat="form.center_lat"
                        :lng="form.center_lng"
                        :zoom="form.default_zoom"
                        :polygon="form.polygon"
                        @update:lat="form.center_lat = $event"
                        @update:lng="form.center_lng = $event"
                        @update:zoom="form.default_zoom = $event"
                        @update:polygon="form.polygon = $event"
                    />
                </div>

                <!-- Name -->
                <div class="space-y-2">
                    <Label for="zone-name">{{
                        t('pages.zones.modal.fields.name')
                    }}</Label>
                    <Input id="zone-name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Lat / Lng -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="center-lat">{{
                            t('pages.zones.modal.fields.lat')
                        }}</Label>
                        <Input
                            id="center-lat"
                            type="number"
                            step="any"
                            v-model="form.center_lat"
                        />
                        <InputError :message="form.errors.center_lat" />
                    </div>
                    <div class="space-y-2">
                        <Label for="center-lng">{{
                            t('pages.zones.modal.fields.lng')
                        }}</Label>
                        <Input
                            id="center-lng"
                            type="number"
                            step="any"
                            v-model="form.center_lng"
                        />
                        <InputError :message="form.errors.center_lng" />
                    </div>
                </div>

                <!-- Zoom -->
                <div class="space-y-2">
                    <Label for="default-zoom">{{
                        t('pages.zones.modal.fields.zoom')
                    }}</Label>
                    <Input
                        id="default-zoom"
                        type="number"
                        min="1"
                        max="22"
                        v-model="form.default_zoom"
                    />
                    <InputError :message="form.errors.default_zoom" />
                </div>

                <!-- Thumbnail -->
                <div class="space-y-2">
                    <Label for="zone-thumbnail">{{
                        t('pages.zones.modal.fields.thumbnail')
                    }}</Label>
                    <div
                        v-if="zone?.thumbnail_url && !form.thumbnail"
                        class="mb-2"
                    >
                        <img
                            :src="zone.thumbnail_url"
                            :alt="zone.name"
                            class="h-24 w-auto rounded-md object-cover"
                        />
                    </div>
                    <Input
                        id="zone-thumbnail"
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        @change="handleFileChange"
                    />
                    <InputError :message="form.errors.thumbnail" />
                </div>

                <!-- Users -->
                <div class="space-y-2">
                    <Label>{{ t('pages.zones.modal.fields.users') }}</Label>
                    <Multiselect
                        :model-value="form.user_ids"
                        :options="userOptions"
                        :placeholder="t('pages.zones.modal.fields.users_empty')"
                        @update:model-value="form.user_ids = $event as number[]"
                    />
                    <InputError :message="form.errors.user_ids" />
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
