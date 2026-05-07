<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { METRIC_KEYS } from '@/config/telemetry';
import type { MetricKey } from '@/config/telemetry';

defineProps<{
    metrics: Partial<Record<MetricKey, number>> | null | undefined;
}>();

const { t } = useI18n();
</script>

<template>
    <div class="metrics-list max-h-32 min-w-[180px] space-y-1 overflow-y-auto">
        <template v-for="key in METRIC_KEYS" :key="key">
            <div
                v-if="metrics?.[key] != null"
                class="flex items-center justify-between gap-3 text-sm"
            >
                <span class="text-muted-foreground">
                    {{ t(`pages.telemetry_logs.metrics.${key}.label`) }}
                </span>
                <span class="tabular-nums font-medium">
                    {{ metrics[key] }}{{ t(`pages.telemetry_logs.metrics.${key}.unit`) }}
                </span>
            </div>
        </template>
    </div>
</template>

<style scoped>
.metrics-list {
    scrollbar-width: none;
}

.metrics-list::-webkit-scrollbar {
    display: none;
}
</style>
