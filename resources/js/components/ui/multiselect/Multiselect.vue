<script setup lang="ts">
import { computed } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Option {
    value: number | string;
    label: string;
}

const props = defineProps<{
    modelValue: (number | string)[];
    options: Option[];
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [(number | string)[]];
}>();

const selected = computed(() =>
    props.options.filter((o) => props.modelValue.includes(o.value)),
);

const toggle = (value: number | string) => {
    if (props.modelValue.includes(value)) {
        emit('update:modelValue', props.modelValue.filter((v) => v !== value));
    } else {
        emit('update:modelValue', [...props.modelValue, value]);
    }
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <button
                type="button"
                class="border-input focus-visible:border-ring focus-visible:ring-ring/50 dark:bg-input/30 dark:hover:bg-input/50 flex h-auto min-h-9 w-full items-center justify-between gap-2 rounded-md border bg-transparent px-3 py-1.5 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
            >
                <span
                    v-if="selected.length === 0"
                    class="text-muted-foreground"
                >
                    {{ placeholder ?? 'Select...' }}
                </span>
                <div v-else class="flex flex-wrap gap-1">
                    <Badge
                        v-for="opt in selected"
                        :key="opt.value"
                        variant="secondary"
                        class="text-xs"
                    >
                        {{ opt.label }}
                    </Badge>
                </div>
                <ChevronDown class="ml-auto size-4 shrink-0 opacity-50" />
            </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-(--reka-dropdown-menu-trigger-width)">
            <div
                v-if="!options.length"
                class="px-2 py-1.5 text-sm text-muted-foreground"
            >
                <slot name="empty">No options available</slot>
            </div>
            <DropdownMenuCheckboxItem
                v-for="option in options"
                :key="option.value"
                :checked="modelValue.includes(option.value)"
                @select="(e: Event) => { e.preventDefault(); toggle(option.value); }"
            >
                {{ option.label }}
            </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
