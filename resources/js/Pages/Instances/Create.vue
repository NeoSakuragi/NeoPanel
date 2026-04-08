<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    path: '',
    url: '',
    github_repo: '',
    description: '',
    environment: 'production',
    sort_order: 0,
    is_active: true,
});

function submit() {
    form.post(route('instances.store'));
}
</script>

<template>
    <Head title="Add Instance" />

    <AuthenticatedLayout>
        <div class="max-w-2xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-slate-800">Add Instance</h1>
                <Link :href="route('instances.index')" class="text-sm text-slate-500 hover:text-slate-700">
                    Back to list
                </Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="path" value="Filesystem Path" />
                    <TextInput id="path" v-model="form.path" class="mt-1 block w-full font-mono text-sm" placeholder="/var/www/myapp" required />
                    <InputError :message="form.errors.path" class="mt-1" />
                    <p class="text-xs text-slate-400 mt-1">Absolute path to the application directory on disk.</p>
                </div>

                <div>
                    <InputLabel for="url" value="URL" />
                    <TextInput id="url" v-model="form.url" type="url" class="mt-1 block w-full" placeholder="https://myapp.example.com" />
                    <InputError :message="form.errors.url" class="mt-1" />
                    <p class="text-xs text-slate-400 mt-1">Used for health checks. Leave empty if not publicly accessible.</p>
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

                <div class="flex items-center gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing">
                        Add Instance
                    </PrimaryButton>
                    <Link :href="route('instances.index')" class="text-sm text-slate-500 hover:text-slate-700">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
