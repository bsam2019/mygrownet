<template>
    <div 
        class="px-3 py-2 border-t flex-shrink-0"
        :class="darkMode ? 'border-gray-800 bg-gray-900' : 'border-gray-100 bg-white'"
    >
        <!-- Input Area -->
        <div class="flex items-end gap-2">
            <div class="flex-1 relative">
                <textarea
                    ref="inputRef"
                    :value="modelValue"
                    @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
                    @keydown.enter.exact.prevent="handleSend"
                    :placeholder="isAvailable ? 'Ask me anything...' : 'AI not configured'"
                    :disabled="!isAvailable || isLoading"
                    rows="1"
                    class="w-full px-3 py-2 rounded-xl border text-sm resize-none transition-all focus:outline-none focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="darkMode 
                        ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500 focus:border-purple-500 focus:ring-purple-500/20' 
                        : 'bg-gray-50 border-gray-200 text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:ring-purple-500/20'"
                    style="max-height: 80px; min-height: 36px;"
                />
            </div>

            <!-- Send Button -->
            <button
                @click="handleSend"
                :disabled="!modelValue.trim() || isLoading || !isAvailable"
                class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                :class="modelValue.trim() && isAvailable
                    ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white hover:from-purple-700 hover:to-indigo-700 shadow-lg shadow-purple-500/25'
                    : darkMode 
                        ? 'bg-gray-800 text-gray-500' 
                        : 'bg-gray-100 text-gray-400'"
                aria-label="Send message"
            >
                <ArrowPathIcon v-if="isLoading" class="w-4 h-4 animate-spin" aria-hidden="true" />
                <PaperAirplaneIcon v-else class="w-4 h-4" aria-hidden="true" />
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { PaperAirplaneIcon, ArrowPathIcon } from '@heroicons/vue/24/solid';

const props = defineProps<{
    modelValue: string;
    darkMode?: boolean;
    isLoading: boolean;
    isAvailable: boolean;
    suggestions?: string[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    send: [];
    suggestion: [text: string];
}>();

const inputRef = ref<HTMLTextAreaElement | null>(null);

const handleSend = () => {
    if (props.modelValue.trim() && !props.isLoading && props.isAvailable) {
        emit('send');
    }
};

// Auto-resize textarea
watch(() => props.modelValue, async () => {
    await nextTick();
    if (inputRef.value) {
        inputRef.value.style.height = 'auto';
        inputRef.value.style.height = Math.min(inputRef.value.scrollHeight, 80) + 'px';
    }
});
</script>
