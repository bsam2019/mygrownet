<script setup lang="ts">
import AppLayout from '@/layouts/InvestorLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    permission: {
        id: number;
        name: string;
    };
}>();

const form = useForm({
    name: props.permission.name
});

const submit = () => {
    form.patch(route('admin.permissions.update', props.permission.id));
};
</script>

<template>
    <Head title="Edit Permission" />

    <AppLayout>
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Edit Permission</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Permission Name</label>
                    <input type="text"
                           v-model="form.name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <Link :href="route('admin.permissions.index')"
                          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        Update Permission
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
