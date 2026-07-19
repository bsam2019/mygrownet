<script setup lang="ts">
import { ClockIcon } from '@heroicons/vue/24/outline';

interface StatusHistoryItem {
    id: number;
    old_status: string | null;
    new_status: string;
    notes: string | null;
    created_at: string;
    changed_by: {
        user: {
            name: string;
        };
    };
}

interface Props {
    history: StatusHistoryItem[];
}

defineProps<Props>();

const formatStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status: string) => {
    const colors = {
        pending: 'text-gray-600',
        in_progress: 'text-blue-600',
        completed: 'text-green-600',
        cancelled: 'text-red-600',
    };
    return colors[status as keyof typeof colors] || 'text-gray-600';
};
</script>

<template>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Status History</h2>
        </div>
        <div class="p-6">
            <div v-if="history && history.length > 0" class="space-y-4">
                <div
                    v-for="item in history"
                    :key="item.id"
                    class="flex gap-4 pb-4 border-b border-gray-100 last:border-0 last:pb-0"
                >
                    <div class="flex-shrink-0">
                        <ClockIcon class="h-5 w-5 text-gray-400 mt-0.5" aria-hidden="true" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span v-if="item.old_status" :class="['font-medium', getStatusColor(item.old_status)]">
                                {{ formatStatus(item.old_status) }}
                            </span>
                            <span v-if="item.old_status" class="text-gray-400">→</span>
                            <span :class="['font-medium', getStatusColor(item.new_status)]">
                                {{ formatStatus(item.new_status) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            by {{ item.changed_by.user.name }}
                        </p>
                        <p v-if="item.notes" class="text-sm text-gray-500 mt-1 italic">
                            {{ item.notes }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ formatDate(item.created_at) }}
                        </p>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-500 text-center py-4">
                No status history available
            </p>
        </div>
    </div>
</template>
