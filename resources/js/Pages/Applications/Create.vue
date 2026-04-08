<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    github_repo: '',
    description: '',
    sort_order: 0,
    is_active: true,
});

function submit() {
    form.post(route('applications.store'));
}
</script>

<template>
    <Head title="Add Application" />

    <AuthenticatedLayout>
        <div class="max-w-2xl space-y-6">
            <h1 class="text-2xl font-bold text-slate-800">Add Application</h1>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="github_repo" value="GitHub Repository" />
                    <TextInput id="github_repo" v-model="form.github_repo" class="mt-1 block w-full" placeholder="owner/repo" />
                    <InputError :message="form.errors.github_repo" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="description" value="Description" />
                    <textarea id="description" v-model="form.description" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                    <InputError :message="form.errors.description" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="sort_order" value="Sort Order" />
                        <TextInput id="sort_order" v-model="form.sort_order" type="number" min="0" class="mt-1 block w-full" />
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2">
                            <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>
                    <button type="button" :disabled="form.processing" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors disabled:opacity-50" @click="router.visit(route('applications.index'))">Close</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
