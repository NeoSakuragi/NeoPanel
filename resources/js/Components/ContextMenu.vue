<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
});

const emit = defineEmits(['action']);

const visible = ref(false);
const x = ref(0);
const y = ref(0);
const menuRef = ref(null);
const currentRow = ref(null);

function open(event, row) {
    event.preventDefault();
    currentRow.value = row;
    x.value = event.clientX || event.touches?.[0]?.clientX || 0;
    y.value = event.clientY || event.touches?.[0]?.clientY || 0;
    visible.value = true;

    nextTick(() => {
        if (!menuRef.value) return;
        const rect = menuRef.value.getBoundingClientRect();
        if (rect.right > window.innerWidth) x.value -= rect.width;
        if (rect.bottom > window.innerHeight) y.value -= rect.height;
    });
}

function close() {
    visible.value = false;
    currentRow.value = null;
}

function handleAction(key) {
    emit('action', key, currentRow.value);
    close();
}

function onClickOutside(e) {
    if (menuRef.value && !menuRef.value.contains(e.target)) close();
}

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));

defineExpose({ open, close });
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="visible"
                ref="menuRef"
                :style="{ position: 'fixed', left: x + 'px', top: y + 'px' }"
                class="z-50 min-w-[160px] rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
            >
                <button
                    v-for="item in items"
                    :key="item.key"
                    @click="handleAction(item.key)"
                    :class="[
                        'flex w-full items-center gap-2 px-4 py-2 text-sm transition-colors',
                        item.color === 'red'
                            ? 'text-red-600 hover:bg-red-50'
                            : 'text-slate-700 hover:bg-slate-50',
                    ]"
                >
                    <component v-if="item.icon" :is="item.icon" class="h-4 w-4" />
                    {{ item.label }}
                </button>
            </div>
        </Transition>
    </Teleport>
</template>
