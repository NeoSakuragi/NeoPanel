<script setup>
import { computed } from 'vue';

const props = defineProps({
    environment: { type: String, required: true },
});

const classes = computed(() => {
    const map = {
        production: 'bg-red-100 text-red-700',
        staging: 'bg-amber-100 text-amber-700',
        dev: 'bg-blue-100 text-blue-700',
        custom: 'bg-slate-100 text-slate-700',
    };
    return map[props.environment] || map.custom;
});
</script>

<template>
    <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold uppercase tracking-wide', classes]">
        {{ environment }}
    </span>
</template>
