<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { ChevronLeftIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import type { PageTemplate, NewPageForm, CreatePageStep } from '../../types';
import { pageTemplates, getTemplateIcon, findTemplate, getNewPageTemplates } from '../../config/pageTemplates';

const props = defineProps<{
    show: boolean;
    creating: boolean;
    error: string | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'create', form: NewPageForm): void;
}>();

const createPageStep = ref<CreatePageStep>('template');
const newPageForm = ref<NewPageForm>({ title: '', slug: '', showInNav: true, templateId: 'blank' });

const newPageTemplates = computed(() => getNewPageTemplates());

const selectTemplate = (templateId: string) => {
    newPageForm.value.templateId = templateId;
    const template = findTemplate(templateId);
    if (template && templateId !== 'blank') {
        newPageForm.value.title = template.isHomepage ? '' : template.name;
        newPageForm.value.slug = template.isHomepage ? '' : template.id;
    } else {
        newPageForm.value.title = '';
        newPageForm.value.slug = '';
    }
    createPageStep.value = 'details';
};

const backToTemplates = () => {
    createPageStep.value = 'template';
};

const generateSlug = (title: string) => {
    return title.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
};

watch(() => newPageForm.value.title, (title) => {
    if (title && !newPageForm.value.slug) {
        newPageForm.value.slug = generateSlug(title);
    }
});

watch(() => props.show, (show) => {
    if (show) {
        newPageForm.value = { title: '', slug: '', showInNav: true, templateId: 'blank' };
        createPageStep.value = 'template';
    }
});

const handleCreate = () => {
    emit('create', newPageForm.value);
};
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click="emit('close')">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col" @click.stop>
                <!-- Modal Header -->
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button
                            v-if="createPageStep === 'details'"
                            @click="backToTemplates"
                            class="p-1 hover:bg-gray-100 rounded-lg"
                            aria-label="Back to templates"
                        >
                            <ChevronLeftIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                        </button>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ createPageStep === 'template' ? 'Choose a Template' : 'Page Details' }}
                        </h2>
                    </div>
                    <button @click="emit('close')" class="p-1 hover:bg-gray-100 rounded-lg" aria-label="Close modal">
                        <XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>

                <!-- Step 1: Template Selection -->
                <div v-if="createPageStep === 'template'" class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                    <p class="text-sm text-gray-600 mb-4">Select a pre-designed template or start from scratch</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <button
                            v-for="template in newPageTemplates"
                            :key="template.id"
                            @click="selectTemplate(template.id)"
                            :class="[
                                'p-4 border-2 rounded-xl text-left transition-all hover:border-blue-400 hover:bg-blue-50/50',
                                newPageForm.templateId === template.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200',
                            ]"
                        >
                            <component :is="getTemplateIcon(template.iconType)" class="w-6 h-6 text-gray-500 mb-2" aria-hidden="true" />
                            <h3 class="font-medium text-gray-900 text-sm">{{ template.name }}</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ template.description }}</p>
                            <p class="text-xs text-gray-400 mt-2">
                                {{ template.sections.length === 0 ? 'Empty' : `${template.sections.length} sections` }}
                            </p>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Page Details -->
                <div v-else class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg flex items-center gap-3">
                        <component :is="getTemplateIcon(findTemplate(newPageForm.templateId)?.iconType || 'document')" class="w-6 h-6 text-gray-500" aria-hidden="true" />
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ findTemplate(newPageForm.templateId)?.name }}</p>
                            <p class="text-xs text-gray-500">{{ findTemplate(newPageForm.templateId)?.sections.length || 0 }} pre-built sections</p>
                        </div>
                    </div>

                    <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        {{ error }}
                    </div>

                    <form @submit.prevent="handleCreate" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
                            <input
                                v-model="newPageForm.title"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900"
                                placeholder="e.g., About Us"
                                :disabled="creating"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                            <input
                                v-model="newPageForm.slug"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900"
                                placeholder="about-us"
                                :disabled="creating"
                            />
                            <p class="text-xs text-gray-500 mt-1">This will be the URL path: /{{ newPageForm.slug || 'page-slug' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input v-model="newPageForm.showInNav" type="checkbox" id="showInNav" class="rounded border-gray-300" :disabled="creating" />
                            <label for="showInNav" class="text-sm text-gray-700">Show in navigation menu</label>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="backToTemplates" :disabled="creating" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                                Back
                            </button>
                            <button type="submit" :disabled="creating" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ creating ? 'Creating...' : 'Create Page' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </Teleport>
</template>
