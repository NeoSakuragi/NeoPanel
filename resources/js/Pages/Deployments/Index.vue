<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EnvironmentBadge from '@/Components/EnvironmentBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircleIcon, XCircleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

defineProps({
    deployments: { type: Object, required: true },
});

function relativeDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return 'just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours}h ago`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 30) return `${diffDays}d ago`;
    return date.toLocaleDateString();
}

function duration(dep) {
    if (!dep.started_at || !dep.finished_at) return '-';
    const ms = new Date(dep.finished_at) - new Date(dep.started_at);
    const secs = Math.round(ms / 1000);
    if (secs < 60) return `${secs}s`;
    return `${Math.floor(secs / 60)}m ${secs % 60}s`;
}
</script>

<template>
    <Head title="Deployments" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-slate-800">Deployment History</h1>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Instance</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Tag</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Triggered by</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">When</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="dep in deployments.data"
                            :key="dep.id"
                            class="hover:bg-slate-50 transition-colors cursor-pointer"
                            @click="router.visit(route('deployments.show', dep.id))"
                        >
                            <td class="px-5 py-3">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium',
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
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-slate-900">{{ dep.instance?.application?.name || dep.instance?.name }}</span>
                                    <EnvironmentBadge :environment="dep.instance?.environment" />
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-1.5 text-sm">
                                    <span v-if="dep.previous_tag" class="font-mono text-xs text-slate-400">{{ dep.previous_tag }}</span>
                                    <span v-if="dep.previous_tag" class="text-slate-300">&rarr;</span>
                                    <span class="font-mono text-xs font-medium text-slate-800">{{ dep.tag }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-600">{{ dep.user?.name || '-' }}</td>
                            <td class="px-5 py-3 text-sm text-slate-500" :title="dep.created_at">{{ relativeDate(dep.created_at) }}</td>
                            <td class="px-5 py-3 text-sm text-slate-500">{{ duration(dep) }}</td>
                        </tr>
                        <tr v-if="!deployments.data.length">
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">No deployments yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="deployments.last_page > 1" class="flex justify-center gap-1">
                <Link
                    v-for="link in deployments.links"
                    :key="link.label"
                    :href="link.url"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-sm',
                        link.active ? 'bg-blue-600 text-white' : link.url ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300',
                    ]"
                    v-html="link.label"
                    :preserve-scroll="true"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
