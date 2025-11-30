<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    ArrowLeftIcon,
    PaperClipIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    categories: Record<string, string>;
}

const props = defineProps<Props>();

const form = useForm({
    subject: '',
    description: '',
    category: '',
    priority: 'medium',
    attachments: [] as string[],
});

const submit = () => {
    form.post(route('employee.portal.support.store'));
};

const priorities = [
    { value: 'low', label: 'Low', description: 'General questions, no urgency' },
    { value: 'medium', label: 'Medium', description: 'Standard request, normal timeline' },
    { value: 'high', label: 'High', description: 'Important issue affecting work' },
    { value: 'urgent', label: 'Urgent', description: 'Critical issue, immediate attention needed' },
];

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        low: 'border-gray-300 bg-gray-50',
        medium: 'border-blue-300 bg-blue-50',
        high: 'border-amber-300 bg-amber-50',
        urgent: 'border-red-300 bg-red-50',
    };
    return colors[priority] || 'border-gray-300 bg-gray-50';
};
</script>

<template>
    <Head title="Create Support Ticket" />

    <EmployeePortalLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('employee.portal.support.index')"
                    class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Support Ticket</h1>
                    <p class="text-gray-500 mt-1">Describe your issue and we'll help you resolve it</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select id="category" v-model="form.category"
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a category</option>
                        <option v-for="(label, key) in categories" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                    <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p>
                </div>

                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="subject" v-model="form.subject"
                        placeholder="Brief description of your issue"
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Priority
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label v-for="priority in priorities" :key="priority.value"
                            :class="[
                                'relative flex cursor-pointer rounded-lg border p-4 transition-colors',
                                form.priority === priority.value 
                                    ? getPriorityColor(priority.value) + ' ring-2 ring-blue-500'
                                    : 'border-gray-200 hover:bg-gray-50'
                            ]">
                            <input type="radio" v-model="form.priority" :value="priority.value" class="sr-only" />
                            <div>
                                <span class="block text-sm font-medium text-gray-900">{{ priority.label }}</span>
                                <span class="block text-xs text-gray-500 mt-1">{{ priority.description }}</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" v-model="form.description" rows="6"
                        placeholder="Please provide as much detail as possible about your issue..."
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </textarea>
                    <p class="mt-1 text-sm text-gray-500">Include any error messages, steps to reproduce, or relevant details.</p>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <Link :href="route('employee.portal.support.index')"
                        class="px-4 py-2 text-gray-700 hover:text-gray-900">
                        Cancel
                    </Link>
                    <button type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Creating...' : 'Create Ticket' }}
                    </button>
                </div>
            </form>
        </div>
    </EmployeePortalLayout>
</template>
