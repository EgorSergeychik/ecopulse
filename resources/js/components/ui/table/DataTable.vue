<script setup lang="ts">
import { computed } from 'vue';
import type { TableColumn } from './types';

const props = withDefaults(defineProps<{
    columns: TableColumn[];
    data: any[];
    emptyText?: string;
}>(), {
    emptyText: '—',
});

const colHeaderClasses = computed(() =>
    props.columns.map((col) => [
        'h-12 px-4 align-middle font-medium text-muted-foreground',
        col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
        col.class,
    ]),
);

const colCellClasses = computed(() =>
    props.columns.map((col) => [
        'p-4 align-middle',
        col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : '',
        col.class,
    ]),
);
</script>

<template>
    <div class="rounded-md border bg-card">
        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b">
                    <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                        <th
                            v-for="(col, i) in columns"
                            :key="col.key"
                            :class="colHeaderClasses[i]"
                        >
                            {{ col.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    <tr
                        v-for="(row, rowIndex) in data"
                        :key="(row.id ?? rowIndex)"
                        v-memo="[row]"
                        class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
                    >
                        <td
                            v-for="(col, i) in columns"
                            :key="col.key"
                            :class="colCellClasses[i]"
                        >
                            <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                                {{ row[col.key] }}
                            </slot>
                        </td>
                    </tr>
                    <tr v-if="data.length === 0">
                        <td
                            :colspan="columns.length"
                            class="p-8 text-center text-muted-foreground"
                        >
                            {{ emptyText }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
