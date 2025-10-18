<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Notifications</h3>
                <button v-if="notifications.length > 0" 
                        @click="markAllAsRead"
                        class="text-sm text-blue-600 hover:text-blue-800">
                    Mark all as read
                </button>
            </div>

            <div class="space-y-4">
                <div v-for="notification in notifications" 
                     :key="notification.id"
                     :class="['p-4 rounded-lg border', notification.read_at ? 'bg-gray-50' : 'bg-blue-50 border-blue-100']">
                    <div class="flex items-start">
                        <!-- Notification Icon -->
                        <div class="flex-shrink-0">
                            <span v-if="notification.type === 'approval'" class="p-2 bg-green-100 rounded-full">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span v-else-if="notification.type === 'rejection'" class="p-2 bg-red-100 rounded-full">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>
                            <span v-else-if="notification.type === 'return'" class="p-2 bg-purple-100 rounded-full">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>

                        <!-- Notification Content -->
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ notification.message }}</p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-xs text-gray-500">{{ notification.created_at }}</span>
                                <button v-if="!notification.read_at"
                                        @click="markAsRead(notification)"
                                        class="text-xs text-blue-600 hover:text-blue-800">
                                    Mark as read
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="notifications.length === 0" class="text-center py-4 text-gray-500">
                    No new notifications
                </div>
            </div>

            <div v-if="notifications.length > 0" class="mt-4 text-center">
                <button @click="loadMore" 
                        :disabled="loading"
                        class="text-sm text-gray-600 hover:text-gray-800">
                    {{ loading ? 'Loading...' : 'Load more' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    notifications: {
        type: Array,
        required: true,
        default: () => []
    }
});

const loading = ref(false);

const markAsRead = (notification) => {
    router.patch(route('notifications.mark-as-read', notification.id));
};

const markAllAsRead = () => {
    router.patch(route('notifications.mark-all-as-read'));
};

const loadMore = () => {
    loading.value = true;
    router.get(route('notifications.index', { page: currentPage.value + 1 }), {}, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => loading.value = false
    });
};
</script>