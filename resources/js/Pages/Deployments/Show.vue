<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EnvironmentBadge from '@/Components/EnvironmentBadge.vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircleIcon, XCircleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    deployment: { type: Object, required: true },
});

const dep = props.deployment;

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString();
}

function duration() {
    if (!dep.started_at || !dep.finished_at) return '-';
    const ms = new Date(dep.finished_at) - new Date(dep.started_at);
    const secs = Math.round(ms / 1000);
    if (secs < 60) return `${secs}s`;
    return `${Math.floor(secs / 60)}m ${secs % 60}s`;
}
</script>

<template>
    <Head :title="`Deployment #${dep.id}`" />

    <AuthenticatedLayout>
        <div class="max-w-3xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-slate-800">Deployment #{{ dep.id }}</h1>
                <button
                    @click="router.visit(route('deployments.index'))"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    Close
                </button>
            </div>

            <!-- Info card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Status</p>
                        <span
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium mt-1',
                                dep.status === 'success' ? 'bg-green-100 text-green-700' :
                                dep.status === 'failed' ? 'bg-red-100 text-red-700' :
                                dep.status === 'running' ? 'bg-blue-100 text-blue-700' :
                                'bg-slate-100 text-slate-600',
                            ]"
                        >
                            <CheckCircleIcon v-if="dep.status === 'success'" class="h-3.5 w-3.5" />
                            <XCircleIcon v-else-if="dep.status === 'failed'" class="h-3.5 w-3.5" />
                            <ArrowPathIcon v-else-if="dep.status === 'running'" class="h-3.5 w-3.5 animate-spin" />
                            {{ dep.status }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Instance</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-sm font-medium text-slate-900">{{ dep.instance?.application?.name || dep.instance?.name }}</span>
                            <EnvironmentBadge :environment="dep.instance?.environment" />
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Tag</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span v-if="dep.previous_tag" class="font-mono text-xs text-slate-400">{{ dep.previous_tag }}</span>
                            <span v-if="dep.previous_tag" class="text-slate-300">&rarr;</span>
                            <span class="font-mono text-sm font-medium text-slate-800">{{ dep.tag }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Triggered by</p>
                        <p class="text-sm text-slate-900 mt-1">{{ dep.user?.name || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Started</p>
                        <p class="text-sm text-slate-700 mt-1">{{ formatDate(dep.started_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Duration</p>
                        <p class="text-sm text-slate-700 mt-1">{{ duration() }}</p>
                    </div>
                </div>
            </div>

            <!-- Output log -->
            <div>
                <h2 class="text-base font-semibold text-slate-800 mb-2">Output</h2>
                <pre class="bg-slate-900 text-slate-100 text-xs font-mono rounded-xl p-5 overflow-auto max-h-[60vh] whitespace-pre-wrap">{{ dep.output || 'No output recorded.' }}</pre>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
