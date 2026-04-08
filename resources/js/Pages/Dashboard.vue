<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InstanceMiniCard from '@/Components/InstanceMiniCard.vue';
import InstanceCard from '@/Components/InstanceCard.vue';
import DeployModal from '@/Components/DeployModal.vue';
import { Head } from '@inertiajs/vue3';
import { ArrowPathIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    applications: { type: Array, default: () => [] },
    ungrouped: { type: Array, default: () => [] },
});

const apps = ref(props.applications);
const orphans = ref(props.ungrouped);
const loading = ref(false);
let timer = null;

async function refresh() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/instances/status');
        // Re-group by application_id
        const byApp = {};
        const noApp = [];
        for (const status of data) {
            const appId = status.instance.application_id;
            if (appId) {
                if (!byApp[appId]) byApp[appId] = [];
                byApp[appId].push(status);
            } else {
                noApp.push(status);
            }
        }
        // Update each app's instances
        for (const app of apps.value) {
            app.instances = byApp[app.application.id] || [];
        }
        orphans.value = noApp;
    } catch { /* silent */ }
    finally { loading.value = false; }
}

onMounted(() => { timer = setInterval(refresh, 30000); });
onUnmounted(() => { if (timer) clearInterval(timer); });

const showDeploy = ref(false);
const deployInstance = ref(null);

function openDeploy(instance) {
    deployInstance.value = instance;
    showDeploy.value = true;
}

const totalInstances = computed(() => {
    let count = orphans.value.length;
    for (const app of apps.value) count += app.instances.length;
    return count;
});

function githubUrl(repo) {
    return repo ? `https://github.com/${repo}` : null;
}
</script>

<script>
import { computed } from 'vue';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ totalInstances }} instance{{ totalInstances !== 1 ? 's' : '' }} across {{ apps.length }} app{{ apps.length !== 1 ? 's' : '' }}</p>
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

            <!-- Application groups -->
            <div v-for="group in apps" :key="group.application.id" class="space-y-3">
                <!-- App header -->
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold text-slate-800">{{ group.application.name }}</h2>
                    <a
                        v-if="group.application.github_repo"
                        :href="githubUrl(group.application.github_repo)"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600"
                    >
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                        {{ group.application.github_repo }}
                    </a>
                    <span v-if="group.application.description" class="text-xs text-slate-400">{{ group.application.description }}</span>
                </div>

                <!-- Instance cards row -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <InstanceMiniCard
                        v-for="status in group.instances"
                        :key="status.instance.id"
                        :data="status"
                        :app="group.application"
                        @deploy="openDeploy"
                    />
                </div>
            </div>

            <!-- Ungrouped instances -->
            <div v-if="orphans.length" class="space-y-3">
                <h2 class="text-lg font-semibold text-slate-500">Ungrouped</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <InstanceCard
                        v-for="status in orphans"
                        :key="status.instance.id"
                        :data="status"
                    />
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="!apps.length && !orphans.length" class="rounded-xl border-2 border-dashed border-slate-200 py-16 text-center">
                <p class="text-slate-500 text-sm">No applications configured yet.</p>
                <a :href="route('applications.create')" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-1 inline-block">
                    Add your first application
                </a>
            </div>
        </div>
        <DeployModal
            :show="showDeploy"
            :instance="deployInstance"
            @close="showDeploy = false"
        />
    </AuthenticatedLayout>
</template>
