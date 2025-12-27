<template>
    <div 
        class="px-3 py-2 border-t flex-shrink-0"
        :class="darkMode ? 'border-gray-800 bg-gray-900/30' : 'border-gray-100 bg-gray-50/50'"
    >
        <!-- Inline suggestions as chips -->
        <div class="flex flex-wrap gap-1.5">
            <button
                v-for="suggestion in displaySuggestions"
                :key="suggestion"
                @click="$emit('action', suggestion)"
                class="px-2.5 py-1 text-xs rounded-full border transition-all hover:scale-[1.02]"
                :class="darkMode 
                    ? 'border-gray-700 text-gray-300 hover:border-purple-500 hover:bg-purple-500/10 hover:text-purple-300' 
                    : 'border-gray-200 text-gray-600 hover:border-purple-400 hover:bg-purple-50 hover:text-purple-600'"
            >
                {{ suggestion }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    darkMode?: boolean;
    context: {
        siteName: string;
        sectionType?: string;
        hasContent?: boolean;
    };
    suggestions?: string[];
}>();

defineEmits<{
    action: [prompt: string];
}>();

// Combine provided suggestions with context-based ones
const displaySuggestions = computed(() => {
    if (props.suggestions && props.suggestions.length > 0) {
        return props.suggestions.slice(0, 4);
    }
    
    const type = props.context.sectionType;
    const defaults = ['Generate hero content', 'Suggest colors', 'Write headline'];
    
    if (type) {
        const contextual: Record<string, string[]> = {
            hero: ['Improve headline', 'Add subtitle', 'Better CTA'],
            about: ['Make it personal', 'Add values', 'Shorten text'],
            services: ['Add descriptions', 'List benefits'],
            features: ['Highlight benefits', 'Add icons'],
            cta: ['Make urgent', 'Add proof'],
            contact: ['Add hours', 'Include map'],
            testimonials: ['Add reviews', 'Include photos'],
        };
        return contextual[type] || defaults;
    }
    
    return defaults;
});
</script>
