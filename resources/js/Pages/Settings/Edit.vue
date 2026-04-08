<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({
    github_token: props.settings.github_token || '',
    refresh_interval: props.settings.refresh_interval || 30,
});

function submit() {
    form.put(route('settings.update'));
}
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <div class="max-w-2xl space-y-6">
            <h1 class="text-2xl font-bold text-slate-800">Settings</h1>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
                <div>
                    <InputLabel for="github_token" value="GitHub Personal Access Token" />
                    <TextInput
                        id="github_token"
                        v-model="form.github_token"
                        type="password"
                        class="mt-1 block w-full font-mono text-sm"
                        placeholder="ghp_..."
                    />
                    <InputError :message="form.errors.github_token" class="mt-1" />
                    <p class="text-xs text-slate-400 mt-1">
                        Used for GitHub API access (repo info, releases). Stored encrypted. Leave empty to disable.
                    </p>
                </div>

                <div>
                    <InputLabel for="refresh_interval" value="Auto-refresh Interval (seconds)" />
                    <TextInput
                        id="refresh_interval"
                        v-model="form.refresh_interval"
                        type="number"
                        min="10"
                        max="300"
                        class="mt-1 block w-32"
                    />
                    <InputError :message="form.errors.refresh_interval" class="mt-1" />
                    <p class="text-xs text-slate-400 mt-1">How often the dashboard refreshes instance status (10-300 seconds).</p>
                </div>

                <div class="pt-2">
                    <PrimaryButton :disabled="form.processing">
                        Save Settings
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
