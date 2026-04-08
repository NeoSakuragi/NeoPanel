<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EnvironmentBadge from '@/Components/EnvironmentBadge.vue';
import ContextMenu from '@/Components/ContextMenu.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, PencilSquareIcon, TrashIcon, ArrowRightEndOnRectangleIcon } from '@heroicons/vue/24/outline';

defineProps({
    instances: { type: Array, default: () => [] },
});

const contextMenu = ref(null);
let longPressTimer = null;

function getContextMenuItems(inst) {
    const items = [];

    // Login profiles
    if (inst.login_profiles?.length) {
        for (const profile of inst.login_profiles) {
            items.push({
                key: `login:${profile.key}`,
                label: profile.label,
                icon: ArrowRightEndOnRectangleIcon,
                color: 'blue',
            });
        }
        items.push({ key: 'divider' });
    }

    items.push({ key: 'edit', label: 'Edit', icon: PencilSquareIcon });
    if (!inst.is_self) {
        items.push({ key: 'delete', label: 'Delete', icon: TrashIcon, color: 'red' });
    }

    return items;
}

function onRowClick(inst) {
    router.visit(route('instances.edit', inst.id));
}

function onContextMenu(e, inst) {
    contextMenu.value?.open(e, inst, getContextMenuItems(inst));
}

function onTouchStart(e, inst) {
    longPressTimer = setTimeout(() => {
        onContextMenu(e, inst);
    }, 500);
}

function onTouchEnd() {
    clearTimeout(longPressTimer);
}

const showDeleteConfirm = ref(false);
const toDelete = ref(null);

function handleContextAction(action, inst) {
    if (action.startsWith('login:')) {
        const profileKey = action.replace('login:', '');
        window.open(route('instances.login', [inst.id, profileKey]), '_blank');
        return;
    }
    if (action === 'edit') router.visit(route('instances.edit', inst.id));
    if (action === 'delete') {
        if (inst.is_self) return;
        toDelete.value = inst;
        showDeleteConfirm.value = true;
    }
}

function executeDelete() {
    if (toDelete.value) {
        router.delete(route('instances.destroy', toDelete.value.id), {
            preserveScroll: true,
            onFinish: () => {
                showDeleteConfirm.value = false;
                toDelete.value = null;
            },
        });
    }
}
</script>

<template>
    <Head title="Instances" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-slate-800">Instances</h1>
                <Link
                    :href="route('instances.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                >
                    <PlusIcon class="h-4 w-4" />
                    Add Instance
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Path</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Environment</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="inst in instances"
                            :key="inst.id"
                            :class="[
                                'transition-colors cursor-pointer select-none',
                                contextMenu?.activeRowId === inst.id
                                    ? 'bg-blue-50'
                                    : 'hover:bg-slate-50',
                            ]"
                            @click="onRowClick(inst)"
                            @contextmenu="onContextMenu($event, inst)"
                            @touchstart.passive="onTouchStart($event, inst)"
                            @touchend.passive="onTouchEnd"
                            @touchmove.passive="onTouchEnd"
                        >
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-slate-900">{{ inst.name }}</span>
                                    <span v-if="inst.is_self" class="text-xs text-slate-400 italic">self</span>
                                </div>
                                <p v-if="inst.url" class="text-xs text-slate-400 mt-0.5">{{ inst.url }}</p>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ inst.path }}</td>
                            <td class="px-5 py-3">
                                <EnvironmentBadge :environment="inst.environment" />
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                        inst.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500',
                                    ]"
                                >
                                    {{ inst.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!instances.length">
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">
                                No instances configured.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Context menu -->
        <ContextMenu
            ref="contextMenu"
            @action="handleContextAction"
        />

        <!-- Delete confirm dialog -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showDeleteConfirm = false">
                    <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
                        <h3 class="text-lg font-semibold text-slate-900">Remove Instance</h3>
                        <p class="text-sm text-slate-600 mt-2">
                            Remove <strong>{{ toDelete?.name }}</strong> from the dashboard? This does not delete the actual application.
                        </p>
                        <div class="flex justify-end gap-3 mt-5">
                            <button @click="showDeleteConfirm = false" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors">
                                Cancel
                            </button>
                            <button @click="executeDelete" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
