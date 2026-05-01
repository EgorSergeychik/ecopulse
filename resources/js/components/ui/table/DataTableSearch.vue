<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Input } from '@/components/ui/input';

const props = withDefaults(defineProps<{
    modelValue?: string | null;
    debounceMs?: number;
    paramName?: string;
}>(), {
    modelValue: '',
    debounceMs: 300,
    paramName: 'search',
});

const { t } = useI18n();

const value = ref(props.modelValue ?? '');

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

const queueSubmit = () => {
    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }

    debounceTimeout = setTimeout(submit, props.debounceMs);
};

const submit = () => {
    const params = new URLSearchParams(window.location.search);
    const normalized = value.value.trim();

    if (normalized) {
        params.set(props.paramName, normalized);
    } else {
        params.delete(props.paramName);
    }

    params.delete('page');

    router.get(window.location.pathname, Object.fromEntries(params.entries()), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

watch(() => props.modelValue, (newValue) => {
    const normalized = newValue ?? '';

    if (value.value !== normalized) {
        value.value = normalized;
    }
}, { immediate: true });

watch(value, () => {
    queueSubmit();
});

const handlePaste = async () => {
    await nextTick();
    queueSubmit();
};

onBeforeUnmount(() => {
    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }
});
</script>

<template>
    <div class="relative w-full sm:w-72">
        <Search class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
        <Input
            v-model="value"
            type="search"
            class="pl-9"
            :placeholder="t('table.search.placeholder')"
            @paste="handlePaste"
        />
    </div>
</template>
