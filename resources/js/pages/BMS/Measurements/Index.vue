<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import {
    PlusIcon, MagnifyingGlassIcon, RulerIcon,
    CheckCircleIcon, ClockIcon, DocumentTextIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: CMSLayout });

interface Customer { id: number; name: string; }
interface Item { id: number; type: string; total_area: number; }
interface Measurement {
    id: number;
    measurement_number: string;
    project_name: string;
    location: string | null;
    measurement_date: string;
    status: 'draft' | 'completed' | 'quoted';
    customer: Customer;
    items: Item[];
}
interface Paginated<T> { data: T[]; links: any[]; meta: any; }

const props = defineProps<{
    measurements: Paginated<Measurement>;
    summary: { total: number; draft: number; completed: number; quoted: number };
    customers: Customer[];
    filters: { status?: string; customer_id?: string; search?: string };
}>();

const filters = ref({ ...props.filters });

function applyFilters() {
    router.get(route('cms.measurements.index'), filters.value, { preserveState: true, replace: true });
}

function clearFilters() {
    filters.value = {};
    applyFilters();
}

const statusConfig: Record<string, { label: string; class: string }> = {
    draft:     { label: 'Draft',     class: 'bg-gray-100 text-gray-700' },
    completed: { label: 'Completed', class: 'bg-blue-100 text-blue-700' },
    quoted:    { label: 'Quoted',    class: 'bg-green-100 text-green-700' },
};

function totalArea(items: Item[]) {
    return items.reduce((s, i) => s + Number(i.total_area), 0).toFixed(2);
}

function fmt(date: string) {
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="Measurements - CMS" />

    <div>
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Measurements</h1>
                <p class="mt-1 text-sm text-gray-500">Site measurements for aluminium fabrication</p>
            </div>
            <Link
                :href="route('cms.measurements.create')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center gap-2"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                <span>New Measurement</span>
            </Link>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ summary.total }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">Draft</p>
                <p class="text-2xl font-bold text-gray-500">{{ summary.draft }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">Completed</p>
                <p class="text-2xl font-bold text-blue-600">{{ summary.completed }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">Quoted</p>
                <p class="text-2xl font-bold text-green-600">{{ summary.quoted }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" aria-hidden="true" />
                    <input
                        v-model="filters.search"
                        @input="applyFilters"
                        type="text"
                        placeholder="Search project, number…"
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                <select
                    v-model="filters.status"
                    @change="applyFilters"
                    class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="completed">Completed</option>
                    <option value="quoted">Quoted</option>
                </select>
                <select
                    v-model="filters.customer_id"
                    @change="applyFilters"
                    class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Customers</option>
                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>
        </div>

        <!-- Table (desktop) -->
        <div class="hidden md:block bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Number</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Area</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="m in measurements.data" :key="m.id" class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm font-mono font-medium text-gray-900">{{ m.measurement_number }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ m.project_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ m.customer?.name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ fmt(m.measurement_date) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ m.items?.length ?? 0 }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ totalArea(m.items ?? []) }} m²</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium" :class="statusConfig[m.status]?.class">
                                {{ statusConfig[m.status]?.label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('cms.measurements.show', m.id)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</Link>
                        </td>
                    </tr>
                    <tr v-if="measurements.data.length === 0">
                        <td colspan="8" class="px-4 py-12 text-center text-sm text-gray-400">
                            No measurements found.
                            <Link :href="route('cms.measurements.create')" class="text-blue-600 ml-1">Create one</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Cards (mobile) -->
        <div class="md:hidden space-y-3">
            <Link
                v-for="m in measurements.data"
                :key="m.id"
                :href="route('cms.measurements.show', m.id)"
                class="block bg-white rounded-xl border border-gray-100 shadow-sm p-4"
            >
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ m.project_name }}</p>
                        <p class="text-xs text-gray-500 font-mono">{{ m.measurement_number }}</p>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium flex-shrink-0" :class="statusConfig[m.status]?.class">
                        {{ statusConfig[m.status]?.label }}
                    </span>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-2 pt-2 border-t border-gray-50">
                    <span>{{ m.customer?.name }}</span>
                    <span>{{ totalArea(m.items ?? []) }} m² · {{ m.items?.length ?? 0 }} items</span>
                </div>
            </Link>
            <div v-if="measurements.data.length === 0" class="bg-white rounded-xl border border-gray-100 p-8 text-center text-sm text-gray-400">
                No measurements found.
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="measurements.meta?.last_page > 1" class="mt-4 flex justify-center gap-1">
            <Link
                v-for="link in measurements.links"
                :key="link.label"
                :href="link.url ?? '#'"
                v-html="link.label"
                class="px-3 py-1.5 rounded text-sm border"
                :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
            />
        </div>
    </div>
</template>
