<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { PlusIcon, TrashIcon, ClipboardDocumentIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
    instance: { type: Object, required: true },
});

const form = useForm({
    name: props.instance.name,
    path: props.instance.path,
    url: props.instance.url || '',
    github_repo: props.instance.github_repo || '',
    description: props.instance.description || '',
    environment: props.instance.environment,
    sort_order: props.instance.sort_order,
    is_active: props.instance.is_active,
    auth_secret: '',
    login_profiles: props.instance.login_profiles || [],
});

const generatedSecret = ref('');
const secretCopied = ref(false);

async function generateSecret() {
    const { data } = await axios.post(route('api.generate-secret'));
    generatedSecret.value = data.secret;
    form.auth_secret = data.secret;
    secretCopied.value = false;
}

function copySecret() {
    navigator.clipboard.writeText(generatedSecret.value);
    secretCopied.value = true;
    setTimeout(() => (secretCopied.value = false), 2000);
}

function addProfile() {
    form.login_profiles.push({ key: '', label: '', user: '', guard: 'web' });
}

function removeProfile(index) {
    form.login_profiles.splice(index, 1);
}

function submit() {
    form.put(route('instances.update', props.instance.id));
}
</script>

<template>
    <Head :title="`Edit ${instance.name}`" />

    <AuthenticatedLayout>
        <div class="max-w-2xl space-y-6">
            <h1 class="text-2xl font-bold text-slate-800">Edit {{ instance.name }}</h1>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="path" value="Filesystem Path" />
                    <TextInput id="path" v-model="form.path" class="mt-1 block w-full font-mono text-sm" required />
                    <InputError :message="form.errors.path" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="url" value="URL" />
                    <TextInput id="url" v-model="form.url" type="url" class="mt-1 block w-full" />
                    <InputError :message="form.errors.url" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="github_repo" value="GitHub Repository" />
                    <TextInput id="github_repo" v-model="form.github_repo" class="mt-1 block w-full" placeholder="owner/repo" />
                    <InputError :message="form.errors.github_repo" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="description" value="Description" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    />
                    <InputError :message="form.errors.description" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="environment" value="Environment" />
                        <select
                            id="environment"
                            v-model="form.environment"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        >
                            <option value="production">Production</option>
                            <option value="staging">Staging</option>
                            <option value="dev">Dev</option>
                            <option value="custom">Custom</option>
                        </select>
                        <InputError :message="form.errors.environment" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="sort_order" value="Sort Order" />
                        <TextInput id="sort_order" v-model="form.sort_order" type="number" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.sort_order" class="mt-1" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                    <InputLabel for="is_active" value="Active" class="!mb-0" />
                </div>

                <!-- Auth Bridge section -->
                <div class="border-t border-slate-200 pt-5">
                    <h2 class="text-base font-semibold text-slate-800 mb-3">Remote Login</h2>

                    <!-- Shared secret -->
                    <div class="space-y-2">
                        <InputLabel value="Shared Secret" />
                        <div class="flex items-center gap-2">
                            <span v-if="instance.has_secret && !generatedSecret" class="text-sm text-green-600 font-medium">Configured</span>
                            <span v-else-if="!generatedSecret" class="text-sm text-slate-400">Not set</span>
                            <button
                                type="button"
                                @click="generateSecret"
                                class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200 transition-colors"
                            >
                                <ArrowPathIcon class="h-3.5 w-3.5" />
                                {{ instance.has_secret ? 'Regenerate' : 'Generate' }}
                            </button>
                        </div>
                        <div v-if="generatedSecret" class="mt-2 rounded-lg bg-slate-50 border border-slate-200 p-3">
                            <p class="text-xs text-slate-500 mb-2">Copy this secret into the target app's <code class="bg-slate-200 px-1 rounded">.env</code> file as <code class="bg-slate-200 px-1 rounded">NEOPANEL_SECRET</code></p>
                            <div class="flex items-center gap-2">
                                <code class="flex-1 text-xs font-mono text-slate-700 bg-white border border-slate-200 rounded px-2 py-1.5 select-all break-all">{{ generatedSecret }}</code>
                                <button
                                    type="button"
                                    @click="copySecret"
                                    class="rounded p-1.5 text-slate-400 hover:text-slate-600 transition-colors"
                                    :title="secretCopied ? 'Copied!' : 'Copy'"
                                >
                                    <ClipboardDocumentIcon class="h-4 w-4" />
                                </button>
                            </div>
                            <p v-if="secretCopied" class="text-xs text-green-600 mt-1">Copied to clipboard</p>
                        </div>
                        <InputError :message="form.errors.auth_secret" class="mt-1" />
                    </div>

                    <!-- Login profiles -->
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <InputLabel value="Login Profiles" />
                            <button
                                type="button"
                                @click="addProfile"
                                class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium"
                            >
                                <PlusIcon class="h-3.5 w-3.5" />
                                Add
                            </button>
                        </div>
                        <p v-if="!form.login_profiles.length" class="text-xs text-slate-400">No login profiles configured.</p>

                        <div
                            v-for="(profile, idx) in form.login_profiles"
                            :key="idx"
                            class="flex items-start gap-2 rounded-lg bg-slate-50 border border-slate-200 p-3"
                        >
                            <div class="grid grid-cols-2 gap-2 flex-1">
                                <div>
                                    <input
                                        v-model="profile.key"
                                        placeholder="Key (e.g. admin)"
                                        class="block w-full rounded-md border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <input
                                        v-model="profile.label"
                                        placeholder="Label (e.g. App Admin)"
                                        class="block w-full rounded-md border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <input
                                        v-model="profile.user"
                                        placeholder="Username (e.g. root)"
                                        class="block w-full rounded-md border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <select
                                        v-model="profile.guard"
                                        class="block w-full rounded-md border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="web">web</option>
                                        <option value="system">system</option>
                                    </select>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="removeProfile(idx)"
                                class="rounded p-1 text-slate-400 hover:text-red-600 transition-colors mt-1"
                            >
                                <TrashIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing">
                        Save Changes
                    </PrimaryButton>
                    <button
                        type="button"
                        :disabled="form.processing"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors disabled:opacity-50"
                        @click="router.visit(route('instances.index'))"
                    >
                        Close
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
