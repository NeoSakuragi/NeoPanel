<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InstanceCard from '@/Components/InstanceCard.vue';
import { useInstanceStatus } from '@/Composables/useInstanceStatus.js';
import { Head } from '@inertiajs/vue3';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    instances: { type: Array, default: () => [] },
});

const { statuses, loading, refresh } = useInstanceStatus(props.instances);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ statuses.length }} instance{{ statuses.length !== 1 ? 's' : '' }} monitored</p>
                </div>
                <button
                    @click="refresh"
                    :disabled="loading"
                    class="inline-flex items-center gap-2 rounded-lg bg-white border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors disabled:opacity-50"
                >
                    <ArrowPathIcon :class="['h-4 w-4', loading ? 'animate-spin' : '']" />
                    Refresh
                </button>
            </div>

            <!-- Instance grid -->
            <div v-if="statuses.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                <InstanceCard
                    v-for="status in statuses"
                    :key="status.instance.id"
                    :data="status"
                />
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-xl border-2 border-dashed border-slate-200 py-16 text-center">
                <p class="text-slate-500 text-sm">No instances configured yet.</p>
                <a :href="route('instances.create')" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-1 inline-block">
                    Add your first instance
                </a>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
