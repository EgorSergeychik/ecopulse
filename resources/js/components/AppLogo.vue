<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useSidebar } from '@/components/ui/sidebar/utils';

const { state } = useSidebar();
</script>

<template>
    <div
        class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-md bg-white overflow-hidden border border-border dark:border-transparent"
    >
        <AppLogoIcon class="size-5" />
    </div>
    <div
        class="ml-1 grid flex-1 text-left text-sm"
        v-if="state !== 'collapsed'"
    >
        <span class="mb-0.5 truncate leading-tight font-semibold"
            >Ecopulse</span
        >
    </div>
</template>
