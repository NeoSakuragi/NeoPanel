<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ContextMenu from '@/Components/ContextMenu.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

defineProps({
    applications: { type: Array, default: () => [] },
});

const contextMenu = ref(null);
let longPressTimer = null;

function getContextMenuItems(app) {
    const items = [{ key: 'edit', label: 'Edit', icon: PencilSquareIcon }];
    if (!app.instances_count) {
        items.push({ key: 'delete', label: 'Delete', icon: TrashIcon, color: 'red' });
    }
    return items;
}

function onRowClick(app) {
    router.visit(route('applications.edit', app.id));
}

function onContextMenu(e, app) {
    contextMenu.value?.open(e, app, getContextMenuItems(app));
}

function onTouchStart(e, app) {
    longPressTimer = setTimeout(() => onContextMenu(e, app), 500);
}
function onTouchEnd() { clearTimeout(longPressTimer); }

const showDeleteConfirm = ref(false);
const toDelete = ref(null);

function handleContextAction(action, app) {
    if (action === 'edit') router.visit(route('applications.edit', app.id));
    if (action === 'delete') {
        toDelete.value = app;
        showDeleteConfirm.value = true;
    }
}

function executeDelete() {
    if (toDelete.value) {
        router.delete(route('applications.destroy', toDelete.value.id), {
            preserveScroll: true,
            onFinish: () => { showDeleteConfirm.value = false; toDelete.value = null; },
        });
    }
}
</script>

<template>
    <Head title="Applications" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-slate-800">Applications</h1>
                <Link
                    :href="route('applications.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                >
                    <PlusIcon class="h-4 w-4" />
                    Add Application
                </Link>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">GitHub</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Instances</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="app in applications"
                            :key="app.id"
                            :class="[
                                'transition-colors cursor-pointer select-none',
                                contextMenu?.activeRowId === app.id ? 'bg-blue-50' : 'hover:bg-slate-50',
                            ]"
                            @click="onRowClick(app)"
                            @contextmenu="onContextMenu($event, app)"
                            @touchstart.passive="onTouchStart($event, app)"
                            @touchend.passive="onTouchEnd"
                            @touchmove.passive="onTouchEnd"
                        >
                            <td class="px-5 py-3">
                                <span class="text-sm font-medium text-slate-900">{{ app.name }}</span>
                                <p v-if="app.description" class="text-xs text-slate-400 mt-0.5 truncate max-w-xs">{{ app.description }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <span v-if="app.github_repo" class="font-mono text-xs text-slate-600">{{ app.github_repo }}</span>
                                <span v-else class="text-xs text-slate-400">-</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                    {{ app.instances_count }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                        app.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500',
                                    ]"
                                >
                                    {{ app.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!applications.length">
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">No applications configured.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ContextMenu ref="contextMenu" @action="handleContextAction" />

        <Teleport to="body">
            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showDeleteConfirm = false">
                    <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
                        <h3 class="text-lg font-semibold text-slate-900">Remove Application</h3>
                        <p class="text-sm text-slate-600 mt-2">Remove <strong>{{ toDelete?.name }}</strong>?</p>
                        <div class="flex justify-end gap-3 mt-5">
                            <button @click="showDeleteConfirm = false" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors">Cancel</button>
                            <button @click="executeDelete" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">Remove</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
