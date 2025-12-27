<template>
    <div
        class="flex-shrink-0 border-b"
        :class="darkMode ? 'border-gray-800 bg-gray-900' : 'border-gray-100 bg-white'"
    >
        <!-- Top row: Logo, title, close -->
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-3">
                <!-- AI Logo -->
                <div class="relative">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 via-purple-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/25">
                        <SparklesIcon class="w-4 h-4 text-white" aria-hidden="true" />
                    </div>
                    <div 
                        class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2"
                        :class="[
                            isAvailable ? 'bg-emerald-500' : 'bg-amber-500',
                            darkMode ? 'border-gray-900' : 'border-white'
                        ]"
                    />
                </div>
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold flex items-center gap-2" :class="darkMode ? 'text-white' : 'text-gray-900'">
                        AI Assistant
                        <span 
                            v-if="provider"
                            class="text-[9px] font-medium px-1.5 py-0.5 rounded-full uppercase tracking-wide"
                            :class="darkMode ? 'bg-gray-800 text-gray-400' : 'bg-gray-100 text-gray-500'"
                        >
                            {{ provider }}
                        </span>
                    </h2>
                    <p class="text-[11px] truncate max-w-[180px]" :class="darkMode ? 'text-gray-500' : 'text-gray-500'">
                        {{ contextSummary || (isAvailable ? 'Ready to help' : 'Not configured') }}
                    </p>
                </div>
            </div>

            <!-- Close Button -->
            <button
                @click="$emit('close')"
                class="p-1.5 rounded-lg transition-colors"
                :class="darkMode ? 'hover:bg-gray-800 text-gray-400 hover:text-gray-200' : 'hover:bg-gray-100 text-gray-500 hover:text-gray-700'"
                aria-label="Close AI Assistant"
            >
                <XMarkIcon class="w-5 h-5" aria-hidden="true" />
            </button>
        </div>

        <!-- View Tabs - always visible -->
        <div class="flex items-center gap-1 px-3 pb-2">
            <button
                v-for="view in views"
                :key="view.id"
                @click="$emit('change-view', view.id)"
                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
                :class="[
                    activeView === view.id
                        ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-sm' 
                        : darkMode
                            ? 'text-gray-400 hover:text-gray-300 hover:bg-gray-800'
                            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
                ]"
            >
                <component :is="view.icon" class="w-3.5 h-3.5" aria-hidden="true" />
                {{ view.label }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { SparklesIcon, XMarkIcon, ChatBubbleLeftRightIcon, DocumentTextIcon, WrenchScrewdriverIcon } from '@heroicons/vue/24/outline';

defineProps<{
    darkMode?: boolean;
    isAvailable: boolean;
    provider?: string;
    activeView: 'chat' | 'generate' | 'tools';
    contextSummary?: string;
}>();

defineEmits<{
    close: [];
    'change-view': [view: 'chat' | 'generate' | 'tools'];
}>();

const views = [
    { id: 'chat', label: 'Chat', icon: ChatBubbleLeftRightIcon },
    { id: 'generate', label: 'Generate', icon: DocumentTextIcon },
    { id: 'tools', label: 'Tools', icon: WrenchScrewdriverIcon },
];
</script>
