<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    instance: { type: Object, required: true },
    applications: { type: Array, default: () => [] },
});

const form = useForm({
    application_id: props.instance.application_id || '',
    name: props.instance.name,
    path: props.instance.path,
    url: props.instance.url || '',
    description: props.instance.description || '',
    environment: props.instance.environment,
    sort_order: props.instance.sort_order,
    is_active: props.instance.is_active,
});

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
                    <InputLabel for="application_id" value="Application" />
                    <select id="application_id" v-model="form.application_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">None</option>
                        <option v-for="app in applications" :key="app.id" :value="app.id">{{ app.name }}</option>
                    </select>
                    <InputError :message="form.errors.application_id" class="mt-1" />
                </div>

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
                    <InputLabel for="description" value="Description" />
                    <textarea id="description" v-model="form.description" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                    <InputError :message="form.errors.description" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="environment" value="Environment" />
                        <select id="environment" v-model="form.environment" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
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
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                    <InputLabel for="is_active" value="Active" class="!mb-0" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>
                    <button type="button" :disabled="form.processing" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors disabled:opacity-50" @click="router.visit(route('instances.index'))">Close</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
