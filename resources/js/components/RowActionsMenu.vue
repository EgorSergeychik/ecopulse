<script setup lang="ts">
import { MoreHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface RowActionItem {
    label: string;
    icon?: unknown;
    variant?: 'default' | 'destructive';
    onSelect: () => void;
}

interface RowActionGroup {
    label?: string;
    items: RowActionItem[];
}

const props = defineProps<{
    groups: RowActionGroup[];
    menuLabel: string;
}>();

const visibleGroups = computed(() =>
    props.groups.filter((group) => group.items.length > 0),
);
</script>

<template>
    <DropdownMenu v-if="visibleGroups.length > 0">
        <DropdownMenuTrigger :as-child="true">
            <Button
                variant="outline"
                size="icon"
                :title="menuLabel"
                :aria-label="menuLabel"
            >
                <MoreHorizontal class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-52">
            <template v-for="(group, index) in visibleGroups" :key="index">
                <DropdownMenuSeparator v-if="index > 0" />
                <DropdownMenuLabel v-if="group.label">
                    {{ group.label }}
                </DropdownMenuLabel>
                <DropdownMenuItem
                    v-for="item in group.items"
                    :key="item.label"
                    :variant="item.variant ?? 'default'"
                    @select="item.onSelect"
                >
                    <component :is="item.icon" v-if="item.icon" class="h-4 w-4" />
                    {{ item.label }}
                </DropdownMenuItem>
            </template>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
