<template>
    <GrowBizLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Page Header -->
            <div class="flex items-center gap-3 mb-6">
                <Link 
                    :href="route('growbiz.tasks.index')" 
                    class="p-2 -ml-2 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition-colors"
                    aria-label="Back to tasks"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">New Task</h1>
            </div>

            <div class="max-w-2xl mx-auto">
                <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm p-5">
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                            <input id="title" v-model="form.title" type="text" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter task title" />
                            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" v-model="form.description" rows="4"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Describe the task..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700">Priority *</label>
                                <select id="priority" v-model="form.priority" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option v-for="priority in priorities" :key="priority.value" :value="priority.value">
                                        {{ priority.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                                <input id="due_date" v-model="form.due_date" type="date" :min="minDate"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>

                        <div v-if="employees.length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <label v-for="employee in employees" :key="employee.id"
                                    class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                    :class="{ 'border-blue-500 bg-blue-50': form.assigned_to.includes(employee.id) }">
                                    <input type="checkbox" :value="employee.id" v-model="form.assigned_to"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                    <span class="ml-2 text-sm text-gray-700">{{ employee.name }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-end gap-3">
                        <Link :href="route('growbiz.tasks.index')" class="w-full sm:w-auto px-6 py-3 text-center text-gray-600 bg-gray-100 rounded-xl font-medium hover:bg-gray-200 active:bg-gray-300 transition-colors">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="w-full sm:w-auto px-6 py-3 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 transition-colors">
                            {{ form.processing ? 'Creating...' : 'Create Task' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GrowBizLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { useToast } from '@/composables/useToast';

interface Employee { id: number; name: string; }
interface Props {
    employees: Employee[];
    priorities: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();
const { toast } = useToast();

const form = useForm({
    title: '',
    description: '',
    priority: 'medium',
    due_date: '',
    assigned_to: [] as number[],
    estimated_hours: null as number | null,
});

const minDate = computed(() => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return tomorrow.toISOString().split('T')[0];
});

const submit = () => {
    form.post(route('growbiz.tasks.store'), {
        onSuccess: () => {
            toast.success('Task created successfully');
        },
        onError: () => {
            toast.error('Failed to create task. Please check the form.');
        }
    });
};
</script>
