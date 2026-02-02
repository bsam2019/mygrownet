<template>
    <TransitionRoot :show="modelValue" as="template">
        <Dialog as="div" class="relative z-50" @close="$emit('update:modelValue', false)">
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
                        <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all">
                            <DialogTitle class="text-lg font-semibold text-gray-900 mb-4">
                                Card Statistics
                            </DialogTitle>

                            <div v-if="card" class="space-y-4">
                                <!-- Card Info -->
                                <div class="flex items-center gap-3 pb-4 border-b">
                                    <img
                                        :src="card.image_url"
                                        :alt="card.title"
                                        class="h-16 w-28 object-cover rounded"
                                    />
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ card.title }}</h3>
                                        <p class="text-sm text-gray-500">{{ card.slug }}</p>
                                    </div>
                                </div>

                                <!-- Statistics -->
                                <div v-if="statistics" class="grid grid-cols-2 gap-4">
                                    <!-- Total Shares -->
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <div class="flex items-center gap-2 mb-1">
                                            <ShareIcon class="h-5 w-5 text-blue-600" />
                                            <span class="text-sm font-medium text-gray-600">Total Shares</span>
                                        </div>
                                        <div class="text-2xl font-bold text-blue-600">
                                            {{ statistics.total_shares }}
                                        </div>
                                    </div>

                                    <!-- Total Views -->
                                    <div class="bg-green-50 rounded-lg p-4">
                                        <div class="flex items-center gap-2 mb-1">
                                            <EyeIcon class="h-5 w-5 text-green-600" />
                                            <span class="text-sm font-medium text-gray-600">Total Views</span>
                                        </div>
                                        <div class="text-2xl font-bold text-green-600">
                                            {{ statistics.total_views }}
                                        </div>
                                    </div>

                                    <!-- Today's Shares -->
                                    <div class="bg-purple-50 rounded-lg p-4">
                                        <div class="flex items-center gap-2 mb-1">
                                            <TrendingUpIcon class="h-5 w-5 text-purple-600" />
                                            <span class="text-sm font-medium text-gray-600">Today's Shares</span>
                                        </div>
                                        <div class="text-2xl font-bold text-purple-600">
                                            {{ statistics.today_shares }}
                                        </div>
                                    </div>

                                    <!-- Unique Sharers -->
                                    <div class="bg-orange-50 rounded-lg p-4">
                                        <div class="flex items-center gap-2 mb-1">
                                            <UsersIcon class="h-5 w-5 text-orange-600" />
                                            <span class="text-sm font-medium text-gray-600">Unique Sharers</span>
                                        </div>
                                        <div class="text-2xl font-bold text-orange-600">
                                            {{ statistics.unique_sharers }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Engagement Rate -->
                                <div v-if="statistics" class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-600">Engagement Rate</span>
                                        <span class="text-sm font-bold text-gray-900">
                                            {{ engagementRate }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div
                                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                            :style="{ width: `${Math.min(engagementRate, 100)}%` }"
                                        ></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Shares per view ratio
                                    </p>
                                </div>

                                <!-- Loading State -->
                                <div v-else class="text-center py-8">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                    <p class="text-sm text-gray-600 mt-2">Loading statistics...</p>
                                </div>
                            </div>

                            <!-- Close Button -->
                            <div class="mt-6 flex justify-end">
                                <button
                                    @click="$emit('update:modelValue', false)"
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
import { computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue';
import { ShareIcon, EyeIcon, TrendingUpIcon, UsersIcon } from 'lucide-vue-next';

interface Card {
    id: number;
    title: string;
    slug: string;
    image_url: string;
}

interface Statistics {
    total_shares: number;
    total_views: number;
    today_shares: number;
    unique_sharers: number;
}

const props = defineProps<{
    modelValue: boolean;
    card: Card | null;
    statistics: Statistics | null;
}>();

defineEmits(['update:modelValue']);

const engagementRate = computed(() => {
    if (!props.statistics || props.statistics.total_views === 0) {
        return 0;
    }
    return Math.round((props.statistics.total_shares / props.statistics.total_views) * 100);
});
</script>
