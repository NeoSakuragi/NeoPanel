<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircleIcon, ExclamationCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');
let timeout = null;

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([success, error]) => {
        if (success || error) {
            message.value = success || error;
            type.value = success ? 'success' : 'error';
            visible.value = true;
            clearTimeout(timeout);
            timeout = setTimeout(() => (visible.value = false), 4000);
        }
    },
    { immediate: true },
);
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="visible"
            class="fixed bottom-4 right-4 z-50 flex items-center gap-3 rounded-lg px-4 py-3 shadow-lg text-sm font-medium"
            :class="type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'"
        >
            <CheckCircleIcon v-if="type === 'success'" class="h-5 w-5 flex-shrink-0" />
            <ExclamationCircleIcon v-else class="h-5 w-5 flex-shrink-0" />
            <span>{{ message }}</span>
            <button @click="visible = false" class="ml-2 hover:opacity-70">
                <XMarkIcon class="h-4 w-4" />
            </button>
        </div>
    </Transition>
</template>
