<script setup lang="ts">
import { computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { PaginationMeta } from './types';

const props = withDefaults(defineProps<{
    meta: PaginationMeta;
    pageSizeOptions?: number[];
}>(), {
    pageSizeOptions: () => [10, 25, 50, 100],
});

const { t } = useI18n();

const prevUrl = computed(() => props.meta.links.at(0)?.url ?? null);
const nextUrl = computed(() => props.meta.links.at(-1)?.url ?? null);

const changePageSize = (size: unknown) => {
    if (size === null || size === undefined) {
        return;
    }

    const normalizedSize = String(size);
    const params = new URLSearchParams(window.location.search);

    params.set('per_page', normalizedSize);
    params.delete('page');

    router.visit(props.meta.path, {
        data: Object.fromEntries(params.entries()),
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const navigate = (url: string | null) => {
    if (url) router.visit(url, { preserveScroll: true });
};
</script>

<template>
    <div class="flex items-center justify-between py-4">
        <!-- Page size selector -->
        <div class="flex items-center gap-2">
            <span class="hidden text-sm text-muted-foreground md:inline">
                {{ t('table.pagination.rows_per_page') }}
            </span>
            <Select :model-value="String(meta.per_page)" @update:model-value="changePageSize">
                <SelectTrigger class="h-8 w-[70px]">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="size in pageSizeOptions"
                        :key="size"
                        :value="String(size)"
                    >
                        {{ size }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Desktop: showing text + page links -->
        <div class="hidden items-center gap-4 md:flex">
            <span v-if="meta.from && meta.to" class="text-sm text-muted-foreground">
                {{ t('table.pagination.showing', { from: meta.from, to: meta.to, total: meta.total }) }}
            </span>
            <div v-if="meta.last_page > 1" class="flex items-center gap-1">
                <template v-for="(link, k) in meta.links" :key="k">
                    <div
                        v-if="link.url === null"
                        class="cursor-not-allowed rounded border px-3 py-1.5 text-sm leading-4 text-muted-foreground"
                        v-html="link.label"
                    />
                    <Link
                        v-else
                        class="rounded border px-3 py-1.5 text-sm leading-4 hover:bg-muted"
                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                        :href="link.url"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>

        <!-- Mobile: prev/next icon buttons only -->
        <div class="flex items-center gap-2 md:hidden">
            <Button
                variant="outline"
                size="icon"
                :disabled="!prevUrl"
                @click="navigate(prevUrl)"
            >
                <ChevronLeft class="h-4 w-4" />
            </Button>
            <Button
                variant="outline"
                size="icon"
                :disabled="!nextUrl"
                @click="navigate(nextUrl)"
            >
                <ChevronRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
