<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

interface Plan {
    id: number; business_name: string; industry: string | null; status: string;
    legal_structure: string | null; country: string | null; current_step: number;
    completed_at: string | null; created_at: string; updated_at: string;
}

const props = defineProps<{ plans: { data: Plan[]; meta?: any }; userTier: string }>();

const createNew = () => router.get(route('cms.business-plans.create'));
const viewPlan = (id: number) => router.get(route('cms.business-plans.show', id));
const editPlan = (id: number) => router.get(route('cms.business-plans.create', { plan: id }));
const deletePlan = (id: number, name: string) => {
    if (confirm(`Delete "${name}"? This cannot be undone.`)) {
        router.delete(route('cms.business-plans.delete', id));
    }
};

const formatDate = (d: string) => d ? new Date(d).toLocaleDateString() : '-';
const statusClass = (s: string) => s === 'completed' ? 'bg-green-100 text-green-800' : s === 'draft' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800';
</script>

<template>
    <Head title="Business Plans" />
    <CMSLayout>
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Business Plans</h1>
                    <p class="text-sm text-gray-600 mt-1">Create and manage your business plans</p>
                </div>
                <button @click="createNew" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    New Plan
                </button>
            </div>

            <div v-if="plans.data.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <h3 class="text-lg font-medium text-gray-600 mb-2">No business plans yet</h3>
                <p class="text-sm text-gray-500 mb-6">Create your first business plan to get started</p>
                <button @click="createNew" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">Create Business Plan</button>
            </div>

            <div v-else class="space-y-4">
                <div v-for="plan in plans.data" :key="plan.id" class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ plan.business_name }}</h3>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="statusClass(plan.status)">{{ plan.status }}</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3 text-sm">
                                <div><p class="text-xs text-gray-500">Industry</p><p class="font-medium text-gray-800">{{ plan.industry || 'N/A' }}</p></div>
                                <div><p class="text-xs text-gray-500">Structure</p><p class="font-medium text-gray-800">{{ plan.legal_structure || 'N/A' }}</p></div>
                                <div><p class="text-xs text-gray-500">Created</p><p class="font-medium text-gray-800">{{ formatDate(plan.created_at) }}</p></div>
                                <div><p class="text-xs text-gray-500">Step</p><p class="font-medium text-gray-800">{{ plan.current_step || 1 }}/10</p></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            <button v-if="plan.status === 'completed'" @click="viewPlan(plan.id)" class="px-3 py-1.5 text-sm text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">View</button>
                            <button @click="editPlan(plan.id)" class="px-3 py-1.5 text-sm text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100">Edit</button>
                            <button @click="deletePlan(plan.id, plan.business_name)" class="px-3 py-1.5 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="plans.meta?.last_page > 1" class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-600">Page {{ plans.meta.current_page }} of {{ plans.meta.last_page }}</p>
                <div class="flex gap-2">
                    <Link v-if="plans.meta.prev_page_url" :href="plans.meta.prev_page_url" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">&larr; Previous</Link>
                    <Link v-if="plans.meta.next_page_url" :href="plans.meta.next_page_url" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Next &rarr;</Link>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
