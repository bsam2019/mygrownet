<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    XMarkIcon,
    BellIcon,
    ClipboardDocumentListIcon,
    CalendarDaysIcon,
    FlagIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    action_url?: string;
    created_at: string;
}

interface Props {
    notifications: Notification[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    dismiss: [id: number];
    click: [notification: Notification];
}>();

const getIcon = (type: string) => {
    const icons: Record<string, any> = {
        task_assigned: ClipboardDocumentListIcon,
        task_completed: CheckCircleIcon,
        goal_reminder: FlagIcon,
        time_off_approved: CalendarDaysIcon,
        time_off_rejected: ExclamationCircleIcon,
        time_off_cancelled: CalendarDaysIcon,
        announcement: BellIcon,
    };
    return icons[type] || BellIcon;
};

const getIconColor = (type: string) => {
    const colors: Record<string, string> = {
        task_assigned: 'text-blue-500 bg-blue-100',
        task_completed: 'text-green-500 bg-green-100',
        goal_reminder: 'text-amber-500 bg-amber-100',
        time_off_approved: 'text-green-500 bg-green-100',
        time_off_rejected: 'text-red-500 bg-red-100',
        time_off_cancelled: 'text-gray-500 bg-gray-100',
        announcement: 'text-purple-500 bg-purple-100',
    };
    return colors[type] || 'text-gray-500 bg-gray-100';
};

const handleClick = (notification: Notification) => {
    emit('click', notification);
    emit('dismiss', notification.id);
};
</script>

<template>
    <div class="fixed bottom-4 right-4 z-50 space-y-3 max-w-sm">
        <TransitionGroup
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform translate-x-full opacity-0"
            enter-to-class="transform translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform translate-x-0 opacity-100"
            leave-to-class="transform translate-x-full opacity-0"
        >
            <div
                v-for="notification in notifications"
                :key="notification.id"
                class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden"
            >
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <!-- Icon -->
                        <div :class="['p-2 rounded-full', getIconColor(notification.type)]">
                            <component 
                                :is="getIcon(notification.type)" 
                                class="h-5 w-5" 
                                aria-hidden="true" 
                            />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ notification.title }}
                            </p>
                            <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">
                                {{ notification.message }}
                            </p>
                        </div>

                        <!-- Close button -->
                        <button
                            @click.stop="emit('dismiss', notification.id)"
                            class="p-1 rounded-full hover:bg-gray-100 transition-colors"
                            aria-label="Dismiss notification"
                        >
                            <XMarkIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Action button -->
                    <div v-if="notification.action_url" class="mt-3 flex justify-end">
                        <Link
                            :href="notification.action_url"
                            @click="handleClick(notification)"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700"
                        >
                            View details →
                        </Link>
                    </div>
                </div>

                <!-- Progress bar for auto-dismiss -->
                <div class="h-1 bg-gray-100">
                    <div 
                        class="h-full bg-blue-500 animate-shrink-width"
                        style="animation-duration: 5s;"
                    ></div>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
@keyframes shrink-width {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}

.animate-shrink-width {
    animation: shrink-width linear forwards;
}
</style>
