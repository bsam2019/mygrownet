<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref } from 'vue';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { PlusIcon, ChevronDownIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

interface Plan {
    id: number;
    type: string;
    title: string;
    description: string | null;
    start_date: string | null;
    end_date: string | null;
    status: string;
    sort_order: number;
    children: Plan[];
    created_by: { name: string } | null;
}

const props = defineProps<{
    plans: Plan[];
}>();

const expanded = ref<Set<number>>(new Set());

function toggle(id: number) {
    if (expanded.value.has(id)) {
        expanded.value.delete(id);
    } else {
        expanded.value.add(id);
    }
}

const typeColors: Record<string, string> = {
    strategic: 'bg-purple-100 text-purple-800',
    business: 'bg-blue-100 text-blue-800',
    operational: 'bg-amber-100 text-amber-800',
    schedule: 'bg-green-100 text-green-800',
};

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-600',
    active: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-600',
    archived: 'bg-red-100 text-red-600',
};

function hasChildren(plan: Plan): boolean {
    return plan.children && plan.children.length > 0;
}
</script>

<template>
    <Head title="Plans" />

    <CMSLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Plans</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Strategic plans, business plans, operational plans, and work schedules
                    </p>
                </div>
                <Link
                    :href="route('cms.plans.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    New Plan
                </Link>
            </div>

            <div v-if="plans.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500 text-sm">No plans yet.</p>
                <p class="text-gray-400 text-xs mt-1">Create your first plan to get started.</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow overflow-hidden">
                <div v-for="plan in plans" :key="plan.id">
                    <div
                        class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition cursor-pointer border-b border-gray-100"
                        @click="toggle(plan.id)"
                    >
                        <button class="flex-shrink-0 w-5 h-5 flex items-center justify-center">
                            <ChevronDownIcon v-if="hasChildren(plan) && expanded.has(plan.id)" class="h-4 w-4 text-gray-400" />
                            <ChevronRightIcon v-else-if="hasChildren(plan)" class="h-4 w-4 text-gray-400" />
                            <span v-else class="w-4" />
                        </button>

                        <Link
                            :href="route('cms.plans.show', plan.id)"
                            class="flex-1 min-w-0"
                            @click.stop
                        >
                            <p class="text-sm font-medium text-gray-900 truncate">{{ plan.title }}</p>
                            <p v-if="plan.description" class="text-xs text-gray-500 truncate">{{ plan.description }}</p>
                        </Link>

                        <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${typeColors[plan.type] || 'bg-gray-100 text-gray-600'}`">
                            {{ plan.type }}
                        </span>

                        <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${statusColors[plan.status] || 'bg-gray-100 text-gray-600'}`">
                            {{ plan.status }}
                        </span>

                        <Link
                            :href="route('cms.plans.edit', plan.id)"
                            class="text-xs text-indigo-600 hover:text-indigo-800"
                            @click.stop
                        >
                            Edit
                        </Link>
                    </div>

                    <div v-if="hasChildren(plan) && expanded.has(plan.id)">
                        <div
                            v-for="child in plan.children"
                            :key="child.id"
                            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition border-b border-gray-100"
                            :style="{ paddingLeft: '48px' }"
                        >
                            <Link :href="route('cms.plans.show', child.id)" class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ child.title }}</p>
                            </Link>

                            <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${typeColors[child.type] || 'bg-gray-100 text-gray-600'}`">
                                {{ child.type }}
                            </span>

                            <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${statusColors[child.status] || 'bg-gray-100 text-gray-600'}`">
                                {{ child.status }}
                            </span>

                            <Link
                                :href="route('cms.plans.edit', child.id)"
                                class="text-xs text-indigo-600 hover:text-indigo-800"
                            >
                                Edit
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
