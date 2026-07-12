<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Manager {
    id: number;
    name: string;
}

interface Branch {
    id: number;
    branch_code: string;
    branch_name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    province: string | null;
    is_head_office: boolean;
    is_active: boolean;
    manager_id: number | null;
}

interface Props {
    branch: Branch;
    managers: Manager[];
}

const props = defineProps<Props>();

const form = useForm({
    branch_code: props.branch.branch_code,
    branch_name: props.branch.branch_name,
    phone: props.branch.phone || '',
    email: props.branch.email || '',
    address: props.branch.address || '',
    city: props.branch.city || '',
    province: props.branch.province || '',
    is_head_office: props.branch.is_head_office,
    is_active: props.branch.is_active,
    manager_id: props.branch.manager_id || '',
});

const submit = () => {
    form.put(route('cms.branches.update', props.branch.id), {
        onError: () => {},
    });
};
</script>

<template>
    <Head title="Edit Branch" />

    <CMSLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Link :href="route('cms.branches.index')" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6">
                <ArrowLeftIcon class="h-4 w-4" />
                Back to Branches
            </Link>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h1 class="text-xl font-semibold text-gray-900">Edit Branch</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ branch.branch_name }} ({{ branch.branch_code }})</p>
                </div>

                <form @submit.prevent="submit" class="px-6 py-5 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branch Code *</label>
                            <input v-model="form.branch_code" type="text" maxlength="50"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-300': form.errors.branch_code }" />
                            <p v-if="form.errors.branch_code" class="mt-1 text-xs text-red-600">{{ form.errors.branch_code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name *</label>
                            <input v-model="form.branch_name" type="text" maxlength="255"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-300': form.errors.branch_name }" />
                            <p v-if="form.errors.branch_name" class="mt-1 text-xs text-red-600">{{ form.errors.branch_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input v-model="form.phone" type="text" maxlength="50"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="form.email" type="email" maxlength="255"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input v-model="form.address" type="text" maxlength="500"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input v-model="form.city" type="text" maxlength="255"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                            <input v-model="form.province" type="text" maxlength="255"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
                            <select v-model="form.manager_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">No Manager</option>
                                <option v-for="m in managers" :key="m.id" :value="m.id">{{ m.name }}</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.is_head_office" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700">Head Office</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>

                    <div v-if="form.errors.branch_code" class="text-xs text-red-600">{{ form.errors.branch_code }}</div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <Link :href="route('cms.branches.index')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            {{ form.processing ? 'Updating...' : 'Update Branch' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </CMSLayout>
</template>
