<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    tasks: Array<{
        id: string;
        typeLabel: string;
        description: string;
        dueDate: string;
        status: string;
        recurrence: string;
        isOverdue: boolean;
        daysUntilDue: number;
    }>;
}>();
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Compliance - PrimeEdge Advisory" />
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Compliance Tasks</h1>
            <p class="text-gray-600 mt-1">Track regulatory deadlines and compliance obligations.</p>
        </div>
        <div v-if="tasks?.length" class="space-y-4">
            <div v-for="task in tasks" :key="task.id" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h3 class="font-semibold text-gray-900">{{ task.typeLabel }}</h3>
                            <span class="text-xs px-2 py-1 rounded-full font-medium" :class="task.isOverdue ? 'bg-red-100 text-red-800' : task.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : task.daysUntilDue <= 7 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600'">{{ task.isOverdue ? 'Overdue' : task.status }}</span>
                            <span class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700">{{ task.recurrence }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ task.description }}</p>
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                            <span>Due: {{ task.dueDate }}</span>
                            <span v-if="task.daysUntilDue > 0 && !task.isOverdue" class="text-amber-600">{{ task.daysUntilDue }} days remaining</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <p v-else class="text-center text-gray-500 py-12">No compliance tasks yet. Tasks will appear once you have active engagements.</p>
    </PrimeEdgeAppLayout>
</template>
