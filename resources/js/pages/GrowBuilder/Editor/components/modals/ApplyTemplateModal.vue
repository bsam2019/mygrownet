<script setup lang="ts">
import { XMarkIcon } from '@heroicons/vue/24/outline';
import type { Page, PageTemplate } from '../../types';
import { pageTemplates, getTemplateIcon } from '../../config/pageTemplates';

const props = defineProps<{
    show: boolean;
    activePage: Page | null;
    applying: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'apply', templateId: string): void;
}>();

const handleApply = (templateId: string) => {
    emit('apply', templateId);
};
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click="emit('close')">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col" @click.stop>
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Apply Template</h2>
                        <p class="text-sm text-gray-500">Replace current page content with a template</p>
                    </div>
                    <button @click="emit('close')" class="p-1 hover:bg-gray-100 rounded-lg" aria-label="Close modal">
                        <XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                    <!-- Warning -->
                    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-sm text-amber-800">
                            <strong>Warning:</strong> This will replace all existing sections on "{{ activePage?.title }}". This action cannot be undone.
                        </p>
                    </div>

                    <!-- Template Categories -->
                    <div v-if="activePage?.isHomepage" class="mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Home Page Templates</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
                            <button
                                v-for="template in pageTemplates.filter(t => t.isHomepage)"
                                :key="template.id"
                                @click.stop.prevent="handleApply(template.id)"
                                :disabled="applying"
                                type="button"
                                class="p-4 border-2 border-gray-200 rounded-xl text-left transition-all hover:border-blue-400 hover:bg-blue-50/50 disabled:opacity-50"
                            >
                                <component :is="getTemplateIcon(template.iconType)" class="w-6 h-6 text-gray-500 mb-2 pointer-events-none" aria-hidden="true" />
                                <h3 class="font-medium text-gray-900 text-sm pointer-events-none">{{ template.name }}</h3>
                                <p class="text-xs text-gray-500 mt-1 pointer-events-none">{{ template.description }}</p>
                                <p class="text-xs text-gray-400 mt-2 pointer-events-none">{{ template.sections.length }} sections</p>
                            </button>
                        </div>
                    </div>

                    <p class="text-sm font-medium text-gray-700 mb-2">{{ activePage?.isHomepage ? 'Other Templates' : 'Page Templates' }}</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <button
                            v-for="template in pageTemplates.filter(t => !t.isHomepage)"
                            :key="template.id"
                            @click.stop.prevent="handleApply(template.id)"
                            :disabled="applying"
                            type="button"
                            class="p-4 border-2 border-gray-200 rounded-xl text-left transition-all hover:border-blue-400 hover:bg-blue-50/50 disabled:opacity-50"
                        >
                            <component :is="getTemplateIcon(template.iconType)" class="w-6 h-6 text-gray-500 mb-2 pointer-events-none" aria-hidden="true" />
                            <h3 class="font-medium text-gray-900 text-sm pointer-events-none">{{ template.name }}</h3>
                            <p class="text-xs text-gray-500 mt-1 pointer-events-none">{{ template.description }}</p>
                            <p class="text-xs text-gray-400 mt-2 pointer-events-none">
                                {{ template.sections.length === 0 ? 'Empty' : `${template.sections.length} sections` }}
                            </p>
                        </button>
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 bg-gray-50">
                    <button
                        @click="emit('close')"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
