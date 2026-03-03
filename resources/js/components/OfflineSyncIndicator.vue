<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="queuedActions.length > 0"
            class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 z-40 bg-white rounded-lg shadow-lg border border-gray-200 p-4"
        >
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div v-if="isOnline && isSyncing" class="animate-spin">
                        <ArrowPathIcon class="h-5 w-5 text-blue-600" />
                    </div>
                    <div v-else-if="!isOnline" class="relative">
                        <CloudArrowUpIcon class="h-5 w-5 text-amber-600" />
                        <div class="absolute -top-1 -right-1 w-2 h-2 bg-amber-600 rounded-full"></div>
                    </div>
                    <CheckCircleIcon v-else class="h-5 w-5 text-green-600" />
                </div>
                
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">
                        <span v-if="!isOnline">Offline Mode</span>
                        <span v-else-if="isSyncing">Syncing...</span>
                        <span v-else>Ready to Sync</span>
                    </h3>
                    <p class="text-xs text-gray-600 mb-2">
                        {{ queuedActions.length }} {{ queuedActions.length === 1 ? 'action' : 'actions' }} queued
                    </p>
                    
                    <div v-if="!isOnline" class="text-xs text-amber-700 bg-amber-50 rounded px-2 py-1 mb-2">
                        Changes will sync when you're back online
                    </div>
                    
                    <button
                        v-if="isOnline && !isSyncing"
                        @click="handleSync"
                        class="text-xs text-blue-600 hover:text-blue-700 font-medium"
                    >
                        Sync Now
                    </button>
                </div>
                
                <button
                    @click="showDetails = !showDetails"
                    class="flex-shrink-0 text-gray-400 hover:text-gray-600"
                    :aria-label="showDetails ? 'Hide details' : 'Show details'"
                >
                    <ChevronDownIcon 
                        class="h-5 w-5 transition-transform"
                        :class="{ 'rotate-180': showDetails }"
                    />
                </button>
            </div>
            
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 max-h-0"
                enter-to-class="opacity-100 max-h-96"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 max-h-96"
                leave-to-class="opacity-0 max-h-0"
            >
                <div v-if="showDetails" class="mt-3 pt-3 border-t border-gray-200">
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <div
                            v-for="action in queuedActions"
                            :key="action.id"
                            class="text-xs bg-gray-50 rounded p-2"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-medium text-gray-900">
                                    {{ action.description || action.method }}
                                </span>
                                <span class="text-gray-500">
                                    {{ formatTime(action.timestamp) }}
                                </span>
                            </div>
                            <div class="text-gray-600 truncate">
                                {{ action.endpoint }}
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { 
    ArrowPathIcon, 
    CloudArrowUpIcon, 
    CheckCircleIcon,
    ChevronDownIcon 
} from '@heroicons/vue/24/outline';
import { useOfflineSync } from '@/composables/useOfflineSync';

const { isOnline, queuedActions, isSyncing, triggerSync } = useOfflineSync();
const showDetails = ref(false);

const handleSync = async () => {
    await triggerSync();
};

const formatTime = (timestamp: number) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
    if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`;
    return date.toLocaleDateString();
};
</script>
