<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import Pagination from '@/components/StockFlow/Pagination.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref } from 'vue';

const { route } = useStockflowRoute();

interface LogEntry {
    id: number;
    user_name: string;
    event: string;
    context: string;
    description: string;
    properties: Record<string, any> | null;
    created_at: string;
}

interface PaginatedLogs {
    data: LogEntry[];
    links: { url: string | null; label: string; active: boolean }[];
    meta: { current_page: number; last_page: number; total: number; from: number; to: number };
}

interface Props {
    logs: PaginatedLogs;
    events: string[];
    filters: { event?: string };
}

const props = defineProps<Props>();
const selectedEvent = ref(props.filters.event || '');

const filterByEvent = () => {
    router.get(route('stockflow.sub.activity-log.index'), { event: selectedEvent.value || undefined }, { preserveState: true });
};

const truncate = (text: string, max = 80) => {
    if (!text || text.length <= max) return text;
    return text.substring(0, max) + '...';
};
</script>

<template>
    <StockFlowLayout title="Activity Log">
        <Head title="Activity Log - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Activity Log</h1>
                </div>

                <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700">Filter by event:</label>
                        <select v-model="selectedEvent" @change="filterByEvent" class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All events</option>
                            <option v-for="event in events" :key="event" :value="event">{{ event }}</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date/Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ log.created_at }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ log.user_name }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                        {{ log.event }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" :title="JSON.stringify(log.properties) || log.description">
                                    {{ log.description || truncate(JSON.stringify(log.properties), 100) }}
                                </td>
                            </tr>
                            <tr v-if="!logs.data?.length">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">No activity log entries found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="logs.links" :meta="logs.meta" />
            </div>
        </div>
    </StockFlowLayout>
</template>
