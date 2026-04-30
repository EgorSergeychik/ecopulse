<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { onBeforeUnmount, ref, watch } from 'vue';
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

const page = usePage();
const { t } = useI18n();

const value = ref(props.modelValue ?? '');

let syncFromProps = false;
let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

const submit = () => {
    const [path, search = ''] = page.url.split('?');
    const params = new URLSearchParams(search);
    const normalized = value.value.trim();

    if (normalized) {
        params.set(props.paramName, normalized);
    } else {
        params.delete(props.paramName);
    }

    params.delete('page');

    router.visit(path, {
        data: Object.fromEntries(params.entries()),
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

watch(() => props.modelValue, (newValue) => {
    syncFromProps = true;
    value.value = newValue ?? '';
}, { immediate: true });

watch(value, () => {
    if (syncFromProps) {
        syncFromProps = false;

        return;
    }

    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }

    debounceTimeout = setTimeout(submit, props.debounceMs);
});

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
        />
    </div>
</template>
