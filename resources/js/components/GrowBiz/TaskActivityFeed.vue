<template>
    <div class="space-y-4">
        <div v-if="updates.length === 0" class="text-center py-8 text-gray-500">
            <ClockIcon class="h-12 w-12 mx-auto mb-2 text-gray-300" aria-hidden="true" />
            <p>No activity yet</p>
        </div>

        <div v-else class="relative">
            <!-- Timeline line -->
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200" aria-hidden="true" />

            <!-- Activity items -->
            <div v-for="update in updates" :key="update.id" class="relative flex gap-4 pb-4">
                <!-- Icon -->
                <div 
                    class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full"
                    :class="getIconBgClass(update.update_type)"
                >
                    <component 
                        :is="getIcon(update.update_type)" 
                        class="h-4 w-4"
                        :class="getIconColorClass(update.update_type)"
                        aria-hidden="true"
                    />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ getUpdateTitle(update) }}
                            </p>
                            <p v-if="update.notes" class="mt-1 text-sm text-gray-600">
                                {{ update.notes }}
                            </p>
                        </div>
                        <span class="text-xs text-gray-500 whitespace-nowrap">
                            {{ formatTime(update.created_at) }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        by {{ update.user?.name || 'System' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { 
    ClockIcon,
    ArrowPathIcon,
    ChartBarIcon,
    DocumentTextIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';

interface Update {
    id: number;
    update_type: 'status_change' | 'progress_update' | 'time_log' | 'note';
    formatted_type: string;
    old_status?: string;
    new_status?: string;
    old_progress?: number;
    new_progress?: number;
    hours_logged?: number;
    notes?: string;
    user?: { id: number; name: string };
    employee?: { id: number; name: string };
    created_at: string;
}

interface Props {
    updates: Update[];
}

defineProps<Props>();

const getIcon = (type: string) => {
    switch (type) {
        case 'status_change': return ArrowPathIcon;
        case 'progress_update': return ChartBarIcon;
        case 'time_log': return ClockIcon;
        case 'note': return DocumentTextIcon;
        default: return CheckCircleIcon;
    }
};

const getIconBgClass = (type: string): string => {
    switch (type) {
        case 'status_change': return 'bg-blue-100';
        case 'progress_update': return 'bg-green-100';
        case 'time_log': return 'bg-purple-100';
        case 'note': return 'bg-gray-100';
        default: return 'bg-gray-100';
    }
};

const getIconColorClass = (type: string): string => {
    switch (type) {
        case 'status_change': return 'text-blue-600';
        case 'progress_update': return 'text-green-600';
        case 'time_log': return 'text-purple-600';
        case 'note': return 'text-gray-600';
        default: return 'text-gray-600';
    }
};

const getUpdateTitle = (update: Update): string => {
    switch (update.update_type) {
        case 'status_change':
            return `Status changed from "${formatStatus(update.old_status)}" to "${formatStatus(update.new_status)}"`;
        case 'progress_update':
            return `Progress updated from ${update.old_progress}% to ${update.new_progress}%`;
        case 'time_log':
            return `Logged ${update.hours_logged} hour${update.hours_logged !== 1 ? 's' : ''}`;
        case 'note':
            return 'Note added';
        default:
            return update.formatted_type;
    }
};

const formatStatus = (status?: string): string => {
    if (!status) return 'Unknown';
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatTime = (dateStr: string): string => {
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString();
};
</script>
