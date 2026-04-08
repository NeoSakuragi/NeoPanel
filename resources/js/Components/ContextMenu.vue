<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
});

const emit = defineEmits(['action']);

const visible = ref(false);
const x = ref(0);
const y = ref(0);
const menuRef = ref(null);
const currentRow = ref(null);
const dynamicItems = ref(null);

const displayItems = computed(() => dynamicItems.value || props.items);

function open(event, row, items = null) {
    event.preventDefault();
    currentRow.value = row;
    dynamicItems.value = items;
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
    dynamicItems.value = null;
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

const activeRowId = computed(() => currentRow.value?.id ?? null);

defineExpose({ open, close, activeRowId });
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
                <template v-for="(item, idx) in displayItems" :key="idx">
                    <div v-if="item.key === 'divider'" class="my-1 border-t border-slate-100" />
                    <button
                        v-else
                        @click="handleAction(item.key)"
                        :class="[
                            'flex w-full items-center gap-2 px-4 py-2 text-sm transition-colors',
                            item.color === 'red'
                                ? 'text-red-600 hover:bg-red-50'
                                : item.color === 'blue'
                                    ? 'text-blue-600 hover:bg-blue-50'
                                    : 'text-slate-700 hover:bg-slate-50',
                        ]"
                    >
                        <component v-if="item.icon" :is="item.icon" class="h-4 w-4" />
                        {{ item.label }}
                    </button>
                </template>
            </div>
        </Transition>
    </Teleport>
</template>
