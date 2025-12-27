<template>
    <div class="space-y-2">
        <div class="flex items-center gap-2 text-xs font-medium opacity-70">
            <DocumentTextIcon class="w-3.5 h-3.5" aria-hidden="true" />
            {{ sectionType }} content
        </div>
        
        <div 
            class="p-3 rounded-lg text-xs space-y-1.5"
            :class="darkMode ? 'bg-gray-900/50' : 'bg-gray-50'"
        >
            <div v-if="content.title" class="font-semibold">
                {{ content.title }}
            </div>
            <div v-if="content.subtitle" class="opacity-80">
                {{ content.subtitle }}
            </div>
            <div v-if="content.content" class="opacity-70 line-clamp-3">
                {{ content.content }}
            </div>
            <div v-if="content.buttonText" class="mt-2">
                <span class="px-2 py-1 rounded text-[10px] bg-purple-500/20 text-purple-400">
                    {{ content.buttonText }}
                </span>
            </div>
            <div v-if="content.items?.length" class="mt-2 space-y-1">
                <div v-for="(item, i) in content.items.slice(0, 3)" :key="i" class="flex items-center gap-2">
                    <span class="w-1 h-1 rounded-full bg-purple-500" />
                    <span class="opacity-80">{{ item.title || item }}</span>
                </div>
                <div v-if="content.items.length > 3" class="text-[10px] opacity-50">
                    +{{ content.items.length - 3 }} more items
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { DocumentTextIcon } from '@heroicons/vue/24/outline';

defineProps<{
    content: any;
    sectionType: string;
    darkMode?: boolean;
}>();
</script>
