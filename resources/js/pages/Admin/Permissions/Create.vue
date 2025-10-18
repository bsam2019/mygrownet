<script setup lang="ts">
import AppLayout from '@/layouts/InvestorLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: ''
});

const submit = () => {
    form.post(route('admin.permissions.store'));
};
</script>

<template>
    <Head title="Create Permission" />

    <AppLayout>
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Create New Permission</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Permission Name</label>
                    <input type="text"
                           v-model="form.name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Enter permission name">
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        Create Permission
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
