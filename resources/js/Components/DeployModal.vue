<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue';
import axios from 'axios';
import { RocketLaunchIcon, CheckCircleIcon, XCircleIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    show: { type: Boolean, default: false },
    instance: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const tags = ref([]);
const currentTag = ref(null);
const selectedTag = ref('');
const search = ref('');
const loadingTags = ref(false);
const deploying = ref(false);
const deployment = ref(null);
const confirmText = ref('');
const elapsed = ref(0);
const outputEl = ref(null);
let pollTimer = null;
let elapsedTimer = null;

const isProduction = () => props.instance?.environment === 'production';

const filteredTags = computed(() => {
    if (!search.value) return tags.value;
    const q = search.value.toLowerCase();
    return tags.value.filter(t =>
        t.name.toLowerCase().includes(q) || t.message.toLowerCase().includes(q)
    );
});

const selectedTagObj = computed(() => tags.value.find(t => t.name === selectedTag.value));

const progressPercent = computed(() => {
    const dep = deployment.value;
    if (!dep || !dep.total_steps) return 0;
    if (dep.status === 'success') return 100;
    if (dep.status === 'failed') return (dep.current_step / dep.total_steps) * 100;
    // While running, show progress up to the current step
    return (Math.max(0, dep.current_step - 0.5) / dep.total_steps) * 100;
});

const elapsedFormatted = computed(() => {
    const s = elapsed.value;
    if (s < 60) return `${s}s`;
    return `${Math.floor(s / 60)}m ${s % 60}s`;
});

watch(() => props.show, async (visible) => {
    if (visible && props.instance) {
        tags.value = [];
        selectedTag.value = '';
        search.value = '';
        deployment.value = null;
        confirmText.value = '';
        elapsed.value = 0;
        await fetchTags();
    } else {
        stopTimers();
    }
});

onBeforeUnmount(stopTimers);

function stopTimers() {
    clearInterval(pollTimer);
    clearInterval(elapsedTimer);
    pollTimer = null;
    elapsedTimer = null;
}

async function fetchTags() {
    loadingTags.value = true;
    try {
        const { data } = await axios.get(route('api.instances.tags', props.instance.id));
        tags.value = data.tags;
        currentTag.value = data.current;
    } catch { /* silent */ }
    finally { loadingTags.value = false; }
}

function selectTag(tagName) {
    selectedTag.value = tagName;
}

async function startDeploy() {
    if (isProduction() && confirmText.value !== selectedTag.value) return;
    deploying.value = true;
    elapsed.value = 0;

    try {
        const { data } = await axios.post(route('api.instances.deploy', props.instance.id), {
            tag: selectedTag.value,
        });
        deployment.value = data.deployment;

        // Start polling and elapsed timer
        startTimers();
    } catch (e) {
        deployment.value = {
            status: 'failed',
            output: e.response?.data?.error || 'Deployment failed to start.',
            current_step: 0,
            total_steps: 0,
        };
    } finally {
        deploying.value = false;
    }
}

function startTimers() {
    pollTimer = setInterval(pollStatus, 800);
    elapsedTimer = setInterval(() => { elapsed.value++; }, 1000);
}

async function pollStatus() {
    if (!deployment.value?.id) return;
    try {
        const { data } = await axios.get(route('api.deployments.status', deployment.value.id));
        deployment.value = data.deployment;
        scrollOutput();
        if (!['pending', 'running'].includes(data.deployment.status)) {
            stopTimers();
        }
    } catch { /* silent */ }
}

function scrollOutput() {
    nextTick(() => {
        if (outputEl.value) {
            outputEl.value.scrollTop = outputEl.value.scrollHeight;
        }
    });
}

function close() {
    stopTimers();
    emit('close');
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="close">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[85vh] flex flex-col">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Deploy to {{ instance?.name }}
                            <span
                                :class="[
                                    'text-sm',
                                    isProduction()
                                        ? 'font-bold text-red-600'
                                        : 'font-normal text-slate-500',
                                ]"
                            >({{ instance?.environment }})</span>
                        </h3>
                        <p v-if="currentTag && !deployment" class="text-xs text-slate-500 mt-0.5">Currently on: <span class="font-mono font-medium">{{ currentTag }}</span></p>

                        <!-- Progress bar (during deployment) -->
                        <div v-if="deployment && ['pending', 'running'].includes(deployment.status)" class="mt-3 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-blue-700 font-medium">
                                    Step {{ deployment.current_step || '...' }} of {{ deployment.total_steps || '...' }}
                                </span>
                                <span class="text-slate-400 tabular-nums">{{ elapsedFormatted }}</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500 ease-out"
                                    :class="deployment.status === 'running' ? 'bg-blue-500' : 'bg-slate-300'"
                                    :style="{ width: progressPercent + '%' }"
                                >
                                    <div v-if="deployment.status === 'running'" class="h-full w-full bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse" />
                                </div>
                            </div>
                        </div>

                        <!-- Final status bar -->
                        <div v-else-if="deployment" class="mt-3">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5">
                                    <CheckCircleIcon v-if="deployment.status === 'success'" class="h-4 w-4 text-green-600" />
                                    <XCircleIcon v-else-if="deployment.status === 'failed'" class="h-4 w-4 text-red-600" />
                                    <span :class="deployment.status === 'success' ? 'text-green-700 font-medium' : 'text-red-700 font-medium'">
                                        {{ deployment.status === 'success' ? 'Deployed successfully' : `Failed at step ${deployment.current_step}` }}
                                    </span>
                                </div>
                                <span class="text-slate-400 tabular-nums">{{ elapsedFormatted }}</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden mt-1.5">
                                <div
                                    class="h-full rounded-full transition-all duration-300"
                                    :class="deployment.status === 'success' ? 'bg-green-500' : 'bg-red-500'"
                                    :style="{ width: progressPercent + '%' }"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4 overflow-y-auto flex-1 space-y-4">
                        <template v-if="!deployment">
                            <div v-if="loadingTags" class="text-sm text-slate-500">Fetching tags...</div>

                            <div v-else-if="!tags.length" class="text-sm text-slate-500">No tags found. Create a git tag first.</div>

                            <template v-else>
                                <!-- Search -->
                                <div class="relative">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Search tags..."
                                        class="block w-full rounded-md border-slate-300 pl-9 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>

                                <!-- Tag list -->
                                <div class="max-h-52 overflow-y-auto rounded-lg border border-slate-200 divide-y divide-slate-100">
                                    <button
                                        v-for="tag in filteredTags"
                                        :key="tag.name"
                                        @click="selectTag(tag.name)"
                                        :class="[
                                            'flex items-start gap-3 w-full px-3 py-2.5 text-left transition-colors',
                                            selectedTag === tag.name
                                                ? 'bg-blue-50'
                                                : 'hover:bg-slate-50',
                                            tag.name === currentTag ? 'opacity-60' : '',
                                        ]"
                                    >
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-mono font-medium text-slate-900">{{ tag.name }}</span>
                                                <span v-if="tag.name === currentTag" class="text-xs text-slate-400">(current)</span>
                                            </div>
                                            <p v-if="tag.message" class="text-xs text-slate-500 mt-0.5 truncate">{{ tag.message }}</p>
                                        </div>
                                        <div v-if="selectedTag === tag.name" class="flex-shrink-0 mt-0.5">
                                            <div class="h-4 w-4 rounded-full bg-blue-600 flex items-center justify-center">
                                                <svg class="h-2.5 w-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                            </div>
                                        </div>
                                    </button>
                                    <div v-if="!filteredTags.length" class="px-3 py-4 text-center text-xs text-slate-400">
                                        No tags matching "{{ search }}"
                                    </div>
                                </div>

                                <!-- Production confirmation -->
                                <div v-if="isProduction() && selectedTag" class="rounded-lg bg-red-50 border border-red-200 p-3">
                                    <p class="text-xs text-red-700 font-medium mb-2">Production deployment — type the tag name to confirm:</p>
                                    <input
                                        v-model="confirmText"
                                        :placeholder="selectedTag"
                                        class="block w-full rounded-md border-red-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500 font-mono"
                                    />
                                </div>
                            </template>
                        </template>

                        <!-- Deployment output (live log) -->
                        <template v-else>
                            <pre
                                ref="outputEl"
                                class="bg-slate-900 text-slate-100 text-xs font-mono rounded-lg p-4 overflow-auto max-h-72 whitespace-pre-wrap scroll-smooth"
                            >{{ deployment.output || 'Waiting for deployment to start...' }}<span v-if="['pending','running'].includes(deployment.status)" class="inline-block w-1.5 h-3.5 bg-blue-400 ml-0.5 animate-pulse align-text-bottom" /></pre>
                        </template>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                        <div v-if="!deployment && selectedTagObj?.message" class="text-xs text-slate-500 truncate mr-4">
                            {{ selectedTagObj.message }}
                        </div>
                        <div v-else></div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <button
                                @click="close"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors"
                            >
                                {{ deployment ? 'Close' : 'Cancel' }}
                            </button>
                            <button
                                v-if="!deployment && selectedTag"
                                @click="startDeploy"
                                :disabled="deploying || (isProduction() && confirmText !== selectedTag)"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors disabled:opacity-50"
                            >
                                <RocketLaunchIcon class="h-4 w-4" />
                                Deploy {{ selectedTag }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
