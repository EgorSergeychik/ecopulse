<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 692 692"
        :class="className"
        v-bind="$attrs"
    >
        <rect width="692" height="692" fill="#ffffff" />
        <path d="M354.354,691.667c130.657,0 240.32,-54.461 301.943,-138.313l-109.663,-91.139c-41.413,53.14 -105.531,83.778 -172.892,82.618c-102.253,-0 -172.892,-61.747 -191.045,-149.057l-167.335,0c21.982,167.335 154.615,295.892 338.991,295.892Z" style="fill:#1a1a1a;fill-rule:nonzero;"/><path d="M675.81,296.486l-660.447,0c-2.261,16.371 -3.457,32.872 -3.581,49.398c0.112,16.526 1.308,33.028 3.581,49.398l663.287,0c0,-11.114 1.235,-24.699 1.235,-38.16c-0.134,-20.276 -1.495,-40.524 -4.075,-60.636Z" style="fill:#ed1c24;fill-rule:nonzero;"/><path d="M15.363,296.486l660.447,0c-4.122,-33.825 -12.418,-67.01 -24.699,-98.795l-608.209,0c-13.872,31.44 -23.147,64.713 -27.539,98.795Z" style="fill:#f15a24;fill-rule:nonzero;"/><path d="M42.902,197.69l608.826,0c-13.698,-36.263 -33.867,-69.738 -59.524,-98.795l-482.615,-0c-27.999,28.645 -50.592,62.116 -66.687,98.795Z" style="fill:#f7931e;fill-rule:nonzero;"/><path d="M592.204,98.895c-55.572,-61.747 -135.844,-98.795 -238.22,-98.795c-91.451,-1.558 -179.717,34.123 -244.395,98.795l482.615,-0Z" style="fill:#fbb03b;fill-rule:nonzero;"/></svg>
</template>
