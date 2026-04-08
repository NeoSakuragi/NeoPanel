<script setup>
import { computed } from 'vue';
import EnvironmentBadge from './EnvironmentBadge.vue';
import HealthDot from './HealthDot.vue';
import {
    ArrowTopRightOnSquareIcon,
    ArrowRightEndOnRectangleIcon,
    CodeBracketIcon,
    ExclamationTriangleIcon,
    ArrowUpIcon,
    ArrowDownIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    data: { type: Object, required: true },
});

const inst = computed(() => props.data.instance);
const git = computed(() => props.data.git);
const env = computed(() => props.data.env);
const health = computed(() => props.data.health);

const githubUrl = computed(() => {
    if (!inst.value.github_repo) return null;
    return `https://github.com/${inst.value.github_repo}`;
});

const commitUrl = computed(() => {
    if (!githubUrl.value || !git.value?.commit_hash_full) return null;
    return `${githubUrl.value}/commit/${git.value.commit_hash_full}`;
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
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition-shadow">
        <!-- Header -->
        <div class="flex items-start justify-between gap-3 px-5 pt-5 pb-3">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-semibold text-slate-900 truncate">{{ inst.name }}</h3>
                    <span v-if="inst.is_self" class="text-xs text-slate-400 italic">self</span>
                </div>
                <p v-if="inst.description" class="text-xs text-slate-500 mt-0.5 truncate">{{ inst.description }}</p>
            </div>
            <EnvironmentBadge :environment="inst.environment" />
        </div>

        <!-- Health + URL + Login -->
        <div class="px-5 pb-3 space-y-2">
            <div class="flex items-center justify-between">
                <HealthDot
                    :status="health?.status"
                    :response-time="health?.response_time_ms"
                />
                <div v-if="inst.has_auth && inst.login_profiles?.length" class="flex items-center gap-1.5">
                    <a
                        v-for="profile in inst.login_profiles"
                        :key="profile.key"
                        :href="route('instances.login', [inst.id, profile.key])"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1 rounded-md bg-blue-50 border border-blue-200 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 transition-colors"
                    >
                        <ArrowRightEndOnRectangleIcon class="h-3.5 w-3.5" />
                        {{ profile.label }}
                    </a>
                </div>
            </div>
            <div v-if="inst.url" class="flex items-center gap-1.5">
                <a
                    :href="inst.url"
                    target="_blank"
                    rel="noopener"
                    class="text-sm text-blue-600 hover:text-blue-800 truncate"
                >
                    {{ inst.url }}
                </a>
                <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5 text-blue-400 flex-shrink-0" />
            </div>
        </div>

        <!-- Git info -->
        <div v-if="git" class="border-t border-slate-100 px-5 py-3 space-y-2">
            <div class="flex items-center gap-2 text-sm">
                <CodeBracketIcon class="h-4 w-4 text-slate-400 flex-shrink-0" />
                <span class="font-mono text-xs font-medium text-slate-700">{{ git.branch }}</span>
                <span
                    v-if="git.is_dirty"
                    class="inline-flex items-center gap-0.5 text-xs text-amber-600"
                    title="Working directory has uncommitted changes"
                >
                    <ExclamationTriangleIcon class="h-3.5 w-3.5" />
                    dirty
                </span>
            </div>

            <!-- Last commit -->
            <div class="flex items-start gap-2 text-xs text-slate-600">
                <span class="font-mono text-slate-400 flex-shrink-0">
                    <a v-if="commitUrl" :href="commitUrl" target="_blank" rel="noopener" class="hover:text-blue-600">
                        {{ git.commit_hash }}
                    </a>
                    <span v-else>{{ git.commit_hash }}</span>
                </span>
                <span class="truncate" :title="git.commit_message">{{ git.commit_message }}</span>
            </div>

            <div class="flex items-center gap-3 text-xs text-slate-400">
                <span>{{ git.commit_author }}</span>
                <span>{{ relativeDate(git.commit_date) }}</span>
            </div>

            <!-- Ahead/behind -->
            <div v-if="git.ahead > 0 || git.behind > 0" class="flex items-center gap-3 text-xs">
                <span v-if="git.ahead > 0" class="flex items-center gap-0.5 text-green-600">
                    <ArrowUpIcon class="h-3 w-3" />
                    {{ git.ahead }} ahead
                </span>
                <span v-if="git.behind > 0" class="flex items-center gap-0.5 text-orange-600">
                    <ArrowDownIcon class="h-3 w-3" />
                    {{ git.behind }} behind
                </span>
            </div>
        </div>
        <div v-else class="border-t border-slate-100 px-5 py-3">
            <p class="text-xs text-slate-400 italic">No git repository detected</p>
        </div>

        <!-- GitHub link -->
        <div v-if="githubUrl" class="border-t border-slate-100 px-5 py-2.5">
            <a :href="githubUrl" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-700">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                {{ inst.github_repo }}
            </a>
        </div>

        <!-- Env vars -->
        <div v-if="env && Object.keys(env).length" class="border-t border-slate-100 px-5 py-2.5">
            <div class="flex flex-wrap gap-x-3 gap-y-1">
                <span v-for="(val, key) in env" :key="key" class="text-xs">
                    <span class="font-mono text-slate-400">{{ key }}:</span>
                    <span class="text-slate-600 ml-0.5">{{ val }}</span>
                </span>
            </div>
        </div>

    </div>
</template>
