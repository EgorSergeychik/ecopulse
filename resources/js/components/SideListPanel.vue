<script setup lang="ts">
import Heading from '@/components/Heading.vue';

defineProps<{
    title: string;
    emptyText: string;
    isEmpty: boolean;
}>();
</script>

<template>
    <section class="flex flex-1 flex-col overflow-hidden rounded-xl border bg-card p-4 shadow-sm">
        <div class="mb-4 flex items-center gap-2">
            <slot name="icon" />
            <Heading :title="title" variant="small" />
        </div>

        <div
            v-if="isEmpty"
            class="rounded-lg border border-dashed p-6 text-sm text-muted-foreground"
        >
            {{ emptyText }}
        </div>

        <div v-else class="flex-1 space-y-3 overflow-y-auto">
            <slot />
        </div>
    </section>
</template>
