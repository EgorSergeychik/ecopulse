<script setup lang="ts">
withDefaults(defineProps<{
    data: any[];
    emptyText?: string;
}>(), {
    emptyText: '—',
});
</script>

<template>
    <div>
        <div
            v-if="data.length === 0"
            class="rounded-md border bg-card p-8 text-center text-sm text-muted-foreground"
        >
            {{ emptyText }}
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="(row, i) in data"
                :key="row.id ?? i"
                class="flex flex-col overflow-hidden rounded-md border bg-card transition-colors hover:bg-muted/30"
            >
                <div v-if="$slots.thumbnail" class="shrink-0">
                    <slot name="thumbnail" :row="row" />
                </div>

                <div class="flex flex-1 flex-col gap-2 p-4">
                    <div class="flex items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <slot name="title" :row="row" />
                            <div class="mt-1">
                                <slot name="body" :row="row" />
                            </div>
                        </div>
                        <div v-if="$slots.rightbar" class="shrink-0">
                            <slot name="rightbar" :row="row" />
                        </div>
                    </div>

                    <div v-if="$slots.footer" class="mt-auto pt-2 border-t">
                        <slot name="footer" :row="row" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
