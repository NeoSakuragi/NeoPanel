<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, default: null }, // 'up', 'down', or null
    responseTime: { type: Number, default: null },
});

const colorClass = computed(() => {
    if (!props.status) return 'bg-slate-300';
    return props.status === 'up' ? 'bg-green-500' : 'bg-red-500';
});

const label = computed(() => {
    if (!props.status) return 'No URL configured';
    if (props.status === 'up' && props.responseTime) return `Up (${props.responseTime}ms)`;
    if (props.status === 'up') return 'Up';
    return 'Down';
});
</script>

<template>
    <span class="inline-flex items-center gap-1.5" :title="label">
        <span :class="['relative flex h-2.5 w-2.5 rounded-full', colorClass]">
            <span
                v-if="status === 'up'"
                :class="['absolute inline-flex h-full w-full rounded-full opacity-75 animate-ping', colorClass]"
            />
        </span>
        <span class="text-xs text-slate-500">{{ label }}</span>
    </span>
</template>
