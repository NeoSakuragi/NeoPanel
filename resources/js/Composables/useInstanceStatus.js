import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export function useInstanceStatus(initialData, intervalMs = 30000) {
    const statuses = ref(initialData);
    const loading = ref(false);
    let timer = null;

    async function refresh() {
        loading.value = true;
        try {
            const { data } = await axios.get('/api/instances/status');
            statuses.value = data;
        } catch {
            // silent — dashboard shows stale data
        } finally {
            loading.value = false;
        }
    }

    onMounted(() => {
        timer = setInterval(refresh, intervalMs);
    });

    onUnmounted(() => {
        if (timer) clearInterval(timer);
    });

    return { statuses, loading, refresh };
}
