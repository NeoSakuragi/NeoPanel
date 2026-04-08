<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { RocketLaunchIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    show: { type: Boolean, default: false },
    instance: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const tags = ref([]);
const currentTag = ref(null);
const selectedTag = ref('');
const loadingTags = ref(false);
const deploying = ref(false);
const deployment = ref(null);
const confirmText = ref('');
let pollTimer = null;

const isProduction = () => props.instance?.environment === 'production';

watch(() => props.show, async (visible) => {
    if (visible && props.instance) {
        tags.value = [];
        selectedTag.value = '';
        deployment.value = null;
        confirmText.value = '';
        await fetchTags();
    } else {
        clearInterval(pollTimer);
    }
});

async function fetchTags() {
    loadingTags.value = true;
    try {
        const { data } = await axios.get(route('api.instances.tags', props.instance.id));
        tags.value = data.tags;
        currentTag.value = data.current;
    } catch { /* silent */ }
    finally { loadingTags.value = false; }
}

async function startDeploy() {
    if (isProduction() && confirmText.value !== selectedTag.value) return;
    deploying.value = true;

    try {
        const { data } = await axios.post(route('api.instances.deploy', props.instance.id), {
            tag: selectedTag.value,
        });
        deployment.value = data.deployment;

        // Poll for updates if still running
        if (deployment.value.status === 'running') {
            pollTimer = setInterval(pollStatus, 1000);
        }
    } catch (e) {
        deployment.value = {
            status: 'failed',
            output: e.response?.data?.error || 'Deployment failed.',
        };
    } finally {
        deploying.value = false;
    }
}

async function pollStatus() {
    if (!deployment.value?.id) return;
    try {
        const { data } = await axios.get(route('api.deployments.status', deployment.value.id));
        deployment.value = data.deployment;
        if (data.deployment.status !== 'running') {
            clearInterval(pollTimer);
        }
    } catch { /* silent */ }
}

function close() {
    clearInterval(pollTimer);
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
                <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[80vh] flex flex-col">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Deploy to {{ instance?.name }}
                            <span class="text-sm font-normal text-slate-500">({{ instance?.environment }})</span>
                        </h3>
                        <p v-if="currentTag" class="text-xs text-slate-500 mt-0.5">Currently on: <span class="font-mono font-medium">{{ currentTag }}</span></p>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4 overflow-y-auto flex-1 space-y-4">
                        <!-- No deployment in progress: tag selection -->
                        <template v-if="!deployment">
                            <div v-if="loadingTags" class="text-sm text-slate-500">Fetching tags...</div>

                            <div v-else-if="!tags.length" class="text-sm text-slate-500">No tags found. Create a git tag first.</div>

                            <template v-else>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Select tag</label>
                                    <select
                                        v-model="selectedTag"
                                        class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                    >
                                        <option value="">Choose a tag...</option>
                                        <option v-for="tag in tags" :key="tag" :value="tag">
                                            {{ tag }}{{ tag === currentTag ? ' (current)' : '' }}
                                        </option>
                                    </select>
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

                        <!-- Deployment output -->
                        <template v-else>
                            <div class="flex items-center gap-2 mb-2">
                                <CheckCircleIcon v-if="deployment.status === 'success'" class="h-5 w-5 text-green-600" />
                                <XCircleIcon v-else-if="deployment.status === 'failed'" class="h-5 w-5 text-red-600" />
                                <div v-else class="h-4 w-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
                                <span :class="[
                                    'text-sm font-medium',
                                    deployment.status === 'success' ? 'text-green-700' :
                                    deployment.status === 'failed' ? 'text-red-700' : 'text-blue-700',
                                ]">
                                    {{ deployment.status === 'success' ? 'Deployment successful' :
                                       deployment.status === 'failed' ? 'Deployment failed' : 'Deploying...' }}
                                </span>
                            </div>
                            <pre class="bg-slate-900 text-slate-100 text-xs font-mono rounded-lg p-4 overflow-auto max-h-64 whitespace-pre-wrap">{{ deployment.output || 'Starting...' }}</pre>
                        </template>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
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
        </Transition>
    </Teleport>
</template>
