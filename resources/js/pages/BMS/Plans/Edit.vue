<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref } from 'vue';
import BMSLayout from '@/Layouts/BMSLayout.vue';
import { toast } from '@/utils/bizboost-toast';

const props = defineProps<{
    plan: {
        id: number;
        type: string;
        title: string;
        description: string | null;
        start_date: string | null;
        end_date: string | null;
        status: string;
        sort_order: number;
    };
}>();

const form = ref({
    type: props.plan.type,
    title: props.plan.title,
    description: props.plan.description ?? '',
    start_date: props.plan.start_date ?? '',
    end_date: props.plan.end_date ?? '',
    status: props.plan.status,
    sort_order: props.plan.sort_order,
});

const submitting = ref(false);

function submit() {
    if (!form.value.title.trim()) {
        toast.warning('Validation', 'Title is required');
        return;
    }
    submitting.value = true;
    router.put(route('bms.plans.update', props.plan.id), form.value, {
        onSuccess: () => {
            toast.success('Updated', 'Plan updated successfully');
        },
        onError: () => {
            toast.error('Failed', 'Could not update plan');
            submitting.value = false;
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
}

const typeOptions = [
    { value: 'strategic', label: 'Strategic Plan' },
    { value: 'business', label: 'Business Plan' },
    { value: 'operational', label: 'Operational Plan' },
    { value: 'schedule', label: 'Work Schedule' },
];
</script>

<template>
    <Head title="Edit Plan" />

    <BMSLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <Link :href="route('bms.plans.index')" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Plans</Link>
                <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Plan</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Type</label>
                    <select
                        v-model="form.type"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input
                            id="start_date"
                            v-model="form.start_date"
                            type="date"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input
                            id="end_date"
                            v-model="form.end_date"
                            type="date"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <input
                            id="sort_order"
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-gray-100">
                    <Link
                        :href="route('bms.plans.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                    >
                        {{ submitting ? 'Saving...' : 'Update Plan' }}
                    </button>
                </div>
            </form>
        </div>
    </BMSLayout>
</template>
