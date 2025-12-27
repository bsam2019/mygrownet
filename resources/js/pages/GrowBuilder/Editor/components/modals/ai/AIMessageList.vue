<template>
    <div 
        ref="containerRef"
        class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 space-y-4 scroll-smooth"
        :class="darkMode ? 'bg-gray-900/50' : 'bg-gray-50/50'"
    >
        <TransitionGroup
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-for="message in messages"
                :key="message.id"
                class="flex gap-3 group"
                :class="message.role === 'user' ? 'flex-row-reverse' : ''"
            >
                <!-- Avatar -->
                <div 
                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                    :class="message.role === 'user' 
                        ? 'bg-blue-600' 
                        : 'bg-gradient-to-br from-violet-500 to-indigo-600'"
                >
                    <UserIcon v-if="message.role === 'user'" class="w-4 h-4 text-white" aria-hidden="true" />
                    <SparklesIcon v-else class="w-4 h-4 text-white" aria-hidden="true" />
                </div>

                <!-- Message Content -->
                <div 
                    class="flex-1 max-w-[85%] sm:max-w-[75%]"
                    :class="message.role === 'user' ? 'flex flex-col items-end' : ''"
                >
                    <div
                        class="rounded-2xl px-4 py-3 text-sm leading-relaxed"
                        :class="[
                            message.role === 'user'
                                ? 'bg-blue-600 text-white rounded-br-md'
                                : darkMode 
                                    ? 'bg-gray-800 text-gray-200 rounded-bl-md' 
                                    : 'bg-white text-gray-800 rounded-bl-md shadow-sm border border-gray-100'
                        ]"
                    >
                        <!-- Markdown-like rendering -->
                        <div 
                            v-html="formatMessage(message.content)" 
                            class="prose-sm max-w-none"
                            :class="message.role === 'user' 
                                ? 'text-white prose-invert' 
                                : darkMode 
                                    ? 'text-gray-200 prose-invert' 
                                    : 'text-gray-800'"
                        />
                        
                        <!-- Content Preview for generated content -->
                        <div 
                            v-if="message.type === 'content' && message.data?.content"
                            class="mt-3 pt-3 border-t"
                            :class="darkMode ? 'border-gray-700' : 'border-gray-200'"
                        >
                            <ContentPreview 
                                :content="message.data.content" 
                                :section-type="message.data.sectionType"
                                :dark-mode="darkMode"
                            />
                        </div>

                        <!-- Color Palette Preview -->
                        <div 
                            v-if="message.type === 'colors' && message.data?.palette"
                            class="mt-3 pt-3 border-t flex gap-2"
                            :class="darkMode ? 'border-gray-700' : 'border-gray-200'"
                        >
                            <div 
                                v-for="(color, name) in message.data.palette" 
                                :key="name"
                                class="flex-1 text-center"
                            >
                                <div 
                                    class="w-full h-8 rounded-lg mb-1 border"
                                    :style="{ backgroundColor: color }"
                                    :class="darkMode ? 'border-gray-600' : 'border-gray-200'"
                                />
                                <span class="text-[10px] capitalize opacity-70">{{ name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Message Actions -->
                    <div 
                        v-if="message.role === 'assistant'"
                        class="flex flex-col gap-2 mt-2"
                    >
                        <!-- Apply button - prominent for actionable messages -->
                        <div v-if="isActionableMessage(message)" class="flex items-center gap-2">
                            <button
                                @click="handleApply(message)"
                                class="flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg transition-all shadow-sm bg-gradient-to-r from-purple-600 to-indigo-600 text-white hover:from-purple-700 hover:to-indigo-700 hover:shadow-md active:scale-95"
                            >
                                <CheckIcon class="w-4 h-4" aria-hidden="true" />
                                {{ appliedMessages.has(message.id) ? 'Applied ✓' : getApplyButtonText(message) }}
                            </button>
                            <span v-if="appliedMessages.has(message.id)" class="text-xs text-green-600 dark:text-green-400">
                                {{ getAppliedText(message) }}
                            </span>
                        </div>
                        <!-- Other actions -->
                        <div class="flex items-center gap-1" :class="darkMode ? 'text-gray-500' : 'text-gray-400'">
                            <button
                                @click="$emit('copy', message.content)"
                                class="flex items-center gap-1 px-2 py-1.5 text-xs rounded-lg transition-colors"
                                :class="darkMode 
                                    ? 'hover:bg-gray-800 hover:text-gray-300' 
                                    : 'hover:bg-gray-100 hover:text-gray-600'"
                                title="Copy text"
                            >
                                <ClipboardIcon class="w-3.5 h-3.5" aria-hidden="true" />
                                <span class="hidden sm:inline">Copy</span>
                            </button>
                            <button
                                @click="$emit('regenerate', message.id)"
                                class="flex items-center gap-1 px-2 py-1.5 text-xs rounded-lg transition-colors"
                                :class="darkMode 
                                    ? 'hover:bg-gray-800 hover:text-gray-300' 
                                    : 'hover:bg-gray-100 hover:text-gray-600'"
                                title="Regenerate response"
                            >
                                <ArrowPathIcon class="w-3.5 h-3.5" aria-hidden="true" />
                                <span class="hidden sm:inline">Retry</span>
                            </button>
                        </div>
                    </div>

                    <!-- Timestamp -->
                    <span 
                        class="text-[10px] mt-1 px-1"
                        :class="darkMode ? 'text-gray-600' : 'text-gray-400'"
                    >
                        {{ formatTime(message.timestamp) }}
                    </span>
                </div>
            </div>
        </TransitionGroup>

        <!-- Typing Indicator -->
        <div v-if="isTyping" class="flex gap-3">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center">
                <SparklesIcon class="w-4 h-4 text-white" aria-hidden="true" />
            </div>
            <div 
                class="rounded-2xl rounded-bl-md px-4 py-3"
                :class="darkMode ? 'bg-gray-800' : 'bg-white shadow-sm border border-gray-100'"
            >
                <div class="flex gap-1">
                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-bounce" style="animation-delay: 0ms" />
                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-bounce" style="animation-delay: 150ms" />
                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-bounce" style="animation-delay: 300ms" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue';
import { SparklesIcon, UserIcon, CheckIcon, ClipboardIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import ContentPreview from './ContentPreview.vue';

interface Message {
    id: string;
    role: 'user' | 'assistant' | 'system';
    content: string;
    timestamp: Date;
    type?: 'text' | 'content' | 'colors' | 'seo' | 'style' | 'section' | 'navigation' | 'footer' | 'page';
    data?: any;
}

const props = defineProps<{
    messages: Message[];
    isTyping: boolean;
    darkMode?: boolean;
}>();

const emit = defineEmits<{
    'apply-content': [data: any];
    copy: [text: string];
    regenerate: [messageId: string];
}>();

const containerRef = ref<HTMLElement | null>(null);
const appliedMessages = ref<Set<string>>(new Set());

// Check if message is actionable (has Apply button)
const isActionableMessage = (message: Message): boolean => {
    const actionableTypes = ['content', 'colors', 'style', 'section', 'navigation', 'footer', 'page'];
    return actionableTypes.includes(message.type || '') && message.data;
};

// Get appropriate button text based on message type
const getApplyButtonText = (message: Message): string => {
    const buttonTexts: Record<string, string> = {
        'content': 'Apply Content',
        'colors': 'Apply Colors',
        'style': 'Apply Style',
        'section': 'Add Section',
        'navigation': 'Update Navigation',
        'footer': 'Update Footer',
        'page': 'Create Page',
    };
    return buttonTexts[message.type || ''] || 'Apply';
};

// Get confirmation text after applying
const getAppliedText = (message: Message): string => {
    const appliedTexts: Record<string, string> = {
        'content': 'Content applied!',
        'colors': 'Colors applied!',
        'style': 'Style applied!',
        'section': 'Section added!',
        'navigation': 'Navigation updated!',
        'footer': 'Footer updated!',
        'page': 'Page created!',
    };
    return appliedTexts[message.type || ''] || 'Applied!';
};

// Handle apply with visual feedback
const handleApply = (message: Message) => {
    emit('apply-content', message.data);
    appliedMessages.value.add(message.id);
    
    // Reset after 3 seconds
    setTimeout(() => {
        appliedMessages.value.delete(message.id);
    }, 3000);
};

// Format message with basic markdown
const formatMessage = (content: string) => {
    return content
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`(.*?)`/g, '<code class="px-1 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-sm">$1</code>')
        .replace(/\n\n/g, '</p><p class="mt-2">')
        .replace(/\n• /g, '</p><p class="mt-1 pl-3">• ')
        .replace(/\n/g, '<br>');
};

// Format timestamp
const formatTime = (date: Date) => {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

// Scroll to bottom
const scrollToBottom = async () => {
    await nextTick();
    if (containerRef.value) {
        containerRef.value.scrollTop = containerRef.value.scrollHeight;
    }
};

defineExpose({ scrollToBottom });
</script>
