<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Category {
    value: string;
    label: string;
}

const props = defineProps<{
    categories: Category[];
}>();

const form = useForm({
    category: '',
    subject: '',
    description: '',
    priority: 'medium',
});

const priorities = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
];

const goBack = () => {
    router.get(route('growfinance.support.index'));
};

const submitTicket = () => {
    form.post(route('growfinance.support.store'));
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Create Ticket - GrowFinance" />

        <div class="p-4 lg:p-6 max-w-2xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <button
                    @click="goBack"
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                    aria-label="Go back to tickets"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Create Support Ticket</h1>
                    <p class="text-sm text-gray-500">Describe your issue and we'll help you</p>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <form @submit.prevent="submitTicket" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select
                            v-model="form.category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="">Select a category</option>
                            <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                {{ cat.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.category" class="text-sm text-red-600 mt-1">{{ form.errors.category }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input
                            v-model="form.subject"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Brief description of your issue"
                        />
                        <p v-if="form.errors.subject" class="text-sm text-red-600 mt-1">{{ form.errors.subject }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <div class="flex gap-3">
                            <label
                                v-for="p in priorities"
                                :key="p.value"
                                :class="[
                                    'flex-1 flex items-center justify-center px-4 py-2 border rounded-lg cursor-pointer transition-colors',
                                    form.priority === p.value
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                        : 'border-gray-300 hover:bg-gray-50'
                                ]"
                            >
                                <input
                                    type="radio"
                                    v-model="form.priority"
                                    :value="p.value"
                                    class="sr-only"
                                />
                                <span class="text-sm font-medium">{{ p.label }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="5"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Please describe your issue in detail..."
                        />
                        <p v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="goBack"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                        >
                            Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
