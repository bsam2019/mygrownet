<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref } from 'vue';
import BMSLayout from '@/Layouts/BMSLayout.vue';
import { toast } from '@/utils/bizboost-toast';

const form = ref({
    type: 'strategic',
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    status: 'draft',
    sort_order: 0,
});

const submitting = ref(false);

function submit() {
    if (!form.value.title.trim()) {
        toast.warning('Validation', 'Title is required');
        return;
    }
    submitting.value = true;
    router.post(route('bms.plans.store'), form.value, {
        onSuccess: () => {
            toast.success('Created', 'Plan created successfully');
        },
        onError: () => {
            toast.error('Failed', 'Could not create plan');
            submitting.value = false;
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
}

const typeOptions = [
    { value: 'strategic', label: 'Strategic Plan', desc: '3-5 year vision, mission, and strategic objectives' },
    { value: 'business', label: 'Business Plan', desc: 'Annual revenue targets, growth initiatives, hiring' },
    { value: 'operational', label: 'Operational Plan', desc: 'Quarterly department goals, resource allocation' },
    { value: 'schedule', label: 'Work Schedule', desc: 'Weekly task and shift assignments' },
];
</script>

<template>
    <Head title="Create Plan" />

    <BMSLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <Link :href="route('bms.plans.index')" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Plans</Link>
                <h1 class="text-2xl font-bold text-gray-900 mt-2">New Plan</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Type</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="opt in typeOptions"
                            :key="opt.value"
                            type="button"
                            @click="form.type = opt.value"
                            :class="[
                                'p-3 rounded-lg border text-left transition',
                                form.type === opt.value
                                    ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            <p class="text-sm font-medium text-gray-900">{{ opt.label }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ opt.desc }}</p>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="e.g. 2026 Strategic Growth Plan"
                    />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Vision, mission, objectives, or key details..."
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
                        {{ submitting ? 'Creating...' : 'Create Plan' }}
                    </button>
                </div>
            </form>
        </div>
    </BMSLayout>
</template>
