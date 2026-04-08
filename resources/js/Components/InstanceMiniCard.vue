<script setup>
import { computed } from 'vue';
import EnvironmentBadge from './EnvironmentBadge.vue';
import HealthDot from './HealthDot.vue';
import {
    ArrowRightEndOnRectangleIcon,
    ArrowTopRightOnSquareIcon,
    CodeBracketIcon,
    ExclamationTriangleIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    RocketLaunchIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    data: { type: Object, required: true },
    app: { type: Object, default: null },
});

const emit = defineEmits(['deploy']);

const inst = computed(() => props.data.instance);
const git = computed(() => props.data.git);
const health = computed(() => props.data.health);

const loginProfiles = computed(() => {
    return inst.value.effective_login_profiles || inst.value.login_profiles || props.app?.login_profiles || [];
});
const hasAuth = computed(() => inst.value.has_auth);

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
        <!-- Header: environment + health + login -->
        <div class="flex flex-wrap items-center gap-2 px-4 pt-4 pb-2">
            <div class="flex items-center gap-2">
                <EnvironmentBadge :environment="inst.environment" />
                <HealthDot :status="health?.status" :response-time="health?.response_time_ms" />
            </div>
            <div class="flex flex-wrap items-center gap-1 ml-auto">
                <button
                    v-if="app?.deploy_recipe?.length"
                    @click="emit('deploy', inst)"
                    class="inline-flex items-center gap-1 rounded-md bg-slate-100 border border-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600 hover:bg-slate-200 transition-colors"
                    title="Deploy"
                >
                    <RocketLaunchIcon class="h-3 w-3" />
                    Deploy
                </button>
                <a
                    v-for="profile in loginProfiles"
                    :key="profile.key"
                    :href="route('instances.login', [inst.id, profile.key])"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1 rounded-md bg-blue-50 border border-blue-200 px-2 py-0.5 text-xs font-medium text-blue-700 hover:bg-blue-100 transition-colors"
                    :title="profile.label"
                >
                    <ArrowRightEndOnRectangleIcon class="h-3 w-3" />
                    {{ profile.label }}
                </a>
            </div>
        </div>

        <!-- URL -->
        <div v-if="inst.url" class="px-4 pb-2">
            <a
                :href="inst.url"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 truncate"
            >
                {{ inst.url }}
                <ArrowTopRightOnSquareIcon class="h-3 w-3 flex-shrink-0" />
            </a>
        </div>

        <!-- Git info -->
        <div v-if="git" class="border-t border-slate-100 px-4 py-3 space-y-1.5">
            <div class="flex items-center gap-2 text-sm">
                <CodeBracketIcon class="h-3.5 w-3.5 text-slate-400 flex-shrink-0" />
                <span class="font-mono text-xs font-medium text-slate-700">{{ git.branch }}</span>
                <span
                    v-if="git.is_dirty"
                    class="inline-flex items-center gap-0.5 text-xs text-amber-600"
                >
                    <ExclamationTriangleIcon class="h-3 w-3" />
                    dirty
                </span>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-600">
                <span class="font-mono text-slate-400">{{ git.commit_hash }}</span>
                <span class="truncate">{{ git.commit_message }}</span>
            </div>

            <div class="flex items-center gap-3 text-xs text-slate-400">
                <span>{{ git.commit_author }}</span>
                <span>{{ relativeDate(git.commit_date) }}</span>
                <span v-if="git.ahead > 0" class="flex items-center gap-0.5 text-green-600">
                    <ArrowUpIcon class="h-3 w-3" /> {{ git.ahead }}
                </span>
                <span v-if="git.behind > 0" class="flex items-center gap-0.5 text-orange-600">
                    <ArrowDownIcon class="h-3 w-3" /> {{ git.behind }}
                </span>
            </div>
        </div>
        <div v-else class="border-t border-slate-100 px-4 py-3">
            <p class="text-xs text-slate-400 italic">No git repository</p>
        </div>
    </div>
</template>
