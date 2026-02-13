<template>
    <TransitionRoot :show="modelValue" as="template">
        <Dialog as="div" class="relative z-50" @close="close">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-4xl transform overflow-hidden rounded-lg bg-white shadow-xl transition-all">
                            <!-- Header -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                                <div>
                                    <DialogTitle class="text-lg font-semibold text-gray-900">
                                        Activity Details
                                    </DialogTitle>
                                    <p v-if="user" class="text-sm text-gray-600 mt-1">
                                        {{ user.name }} ({{ user.email || user.phone }})
                                    </p>
                                </div>
                                <button
                                    @click="close"
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                    aria-label="Close modal"
                                >
                                    <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                                </button>
                            </div>

                            <!-- Loading State -->
                            <div v-if="loading" class="px-6 py-12 text-center">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <p class="text-gray-600 mt-2">Loading activity details...</p>
                            </div>

                            <!-- Content -->
                            <div v-else-if="activityData" class="px-6 py-4">
                                <!-- Summary Stats -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div
                                        v-for="stat in activityData.summary"
                                        :key="stat.activity_type"
                                        class="bg-gray-50 rounded-lg p-4"
                                    >
                                        <p class="text-xs text-gray-600 mb-1">{{ formatActivityType(stat.activity_type) }}</p>
                                        <p class="text-xl font-bold text-gray-900">{{ stat.count }}</p>
                                        <p class="text-xs text-green-600">K{{ formatNumber(stat.credits) }} credits</p>
                                    </div>
                                </div>

                                <!-- Activity List -->
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <div
                                        v-for="activity in activityData.activities.data"
                                        :key="activity.id"
                                        class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ formatActivityType(activity.activity_type) }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        Cycle #{{ activity.cycle_number }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-900">{{ activity.description }}</p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ formatDateTime(activity.created_at) }}
                                                </p>
                                            </div>
                                            <div class="text-right ml-4">
                                                <p class="text-sm font-semibold text-green-600">
                                                    +K{{ formatNumber(activity.credits_awarded) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pagination -->
                                <div v-if="activityData.activities.last_page > 1" class="mt-4 flex justify-center gap-2">
                                    <button
                                        v-for="page in activityData.activities.last_page"
                                        :key="page"
                                        @click="loadPage(page)"
                                        :class="[
                                            'px-3 py-1 rounded text-sm',
                                            activityData.activities.current_page === page
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                                <button
                                    @click="close"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                                >
                                    Close
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface User {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
}

interface Activity {
    id: number;
    activity_type: string;
    description: string;
    credits_awarded: number;
    created_at: string;
    cycle_number: number;
}

interface ActivityData {
    activities: {
        data: Activity[];
        current_page: number;
        last_page: number;
    };
    summary: Array<{
        activity_type: string;
        count: number;
        credits: number;
    }>;
}

interface Props {
    modelValue: boolean;
    user: User | null;
    dateRange: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
}>();

const loading = ref(false);
const activityData = ref<ActivityData | null>(null);
const currentPage = ref(1);

const close = () => {
    emit('update:modelValue', false);
};

const loadUserDetails = async (page: number = 1) => {
    if (!props.user) return;

    loading.value = true;
    try {
        const response = await axios.get(route('admin.lgr.activity-report.user-details', props.user.id), {
            params: {
                date_range: props.dateRange,
                page,
            },
        });
        activityData.value = response.data;
        currentPage.value = page;
    } catch (error) {
        console.error('Failed to load user details:', error);
    } finally {
        loading.value = false;
    }
};

const loadPage = (page: number) => {
    loadUserDetails(page);
};

const formatActivityType = (type: string): string => {
    const types: Record<string, string> = {
        learning_module: 'Learning',
        event_attendance: 'Event',
        marketplace_purchase: 'Purchase',
        marketplace_sale: 'Sale',
        business_plan: 'Business Plan',
        referral_registration: 'Referral',
        social_sharing: 'Social Share',
        community_engagement: 'Community',
        quiz_completion: 'Quiz',
        platform_task: 'Task',
    };
    return types[type] || type;
};

const formatNumber = (num: number): string => {
    return new Intl.NumberFormat().format(num);
};

const formatDateTime = (date: string): string => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Watch for modal open and load data
watch(() => props.modelValue, (newValue) => {
    if (newValue && props.user) {
        loadUserDetails();
    } else {
        activityData.value = null;
        currentPage.value = 1;
    }
});
</script>
