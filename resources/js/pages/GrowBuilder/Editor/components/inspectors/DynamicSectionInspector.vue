<script setup lang="ts">
/**
 * Dynamic Section Inspector Component
 * Schema-driven settings panel that renders fields based on sectionSchemas.ts
 */
import { ref, computed } from 'vue';
import { XMarkIcon, PhotoIcon, TrashIcon, FilmIcon, PlusIcon, InformationCircleIcon, ExclamationTriangleIcon, CheckCircleIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';
import { getSectionSchema, type SchemaField, type SectionSchema, type ItemsField, type SelectField, type ButtonGroupField, type RangeField, type ColorField, type InfoField, type LinkField } from '../../config/sectionSchemas';
import IconPickerModal from '../modals/IconPickerModal.vue';
import VideoEmbedModal from '../modals/VideoEmbedModal.vue';

interface Section {
    id: string;
    type: string;
    content: Record<string, any>;
    style: Record<string, any>;
}

interface SectionType {
    type: string;
    name: string;
    icon: any;
    category: string;
    description: string;
}

const props = defineProps<{
    section: Section;
    sectionType: SectionType | null;
    activeTab: 'content' | 'style' | 'advanced';
    darkMode?: boolean;
    subdomain?: string;
}>();

const emit = defineEmits<{
    (e: 'update:activeTab', value: 'content' | 'style' | 'advanced'): void;
    (e: 'updateContent', field: string, value: any): void;
    (e: 'updateStyle', field: string, value: any): void;
    (e: 'openMediaLibrary', sectionId: string, field: string): void;
}>();

// Get schema for current section type
const schema = computed<SectionSchema | undefined>(() => getSectionSchema(props.section.type));

// Icon Picker State
const showIconPicker = ref(false);
const iconPickerTarget = ref<{ field: string; itemIndex?: number } | null>(null);

// Video Embed State
const showVideoEmbed = ref(false);
const videoEmbedTarget = ref<string | null>(null);

// Gradient presets
const gradientPresets = [
    { name: 'Blue-Purple', from: '#2563eb', to: '#7c3aed' },
    { name: 'Teal', from: '#059669', to: '#0891b2' },
    { name: 'Fire', from: '#dc2626', to: '#ea580c' },
    { name: 'Pink', from: '#7c3aed', to: '#db2777' },
    { name: 'Deep', from: '#1e3a8a', to: '#312e81' },
    { name: 'Ocean', from: '#065f46', to: '#1e40af' },
    { name: 'Warm', from: '#78350f', to: '#92400e' },
    { name: 'Dark', from: '#111827', to: '#374151' },
];

// Get fields for current tab
const currentFields = computed<SchemaField[]>(() => {
    if (!schema.value) return [];
    switch (props.activeTab) {
        case 'content': return schema.value.fields || [];
        case 'style': return schema.value.styleFields || [];
        case 'advanced': return schema.value.advancedFields || [];
        default: return [];
    }
});

// Check if field should be visible
const isFieldVisible = (field: SchemaField): boolean => {
    if (!field.condition) return true;
    return field.condition(props.section.content, props.section.style);
};

// Get value for a field
const getFieldValue = (field: SchemaField): any => {
    const value = props.activeTab === 'style' 
        ? props.section.style?.[field.key] 
        : props.section.content?.[field.key];
    
    // Return default value if no value is set and field has a default
    if (value === undefined || value === null || value === '') {
        if ('defaultValue' in field && field.defaultValue !== undefined) {
            return field.defaultValue;
        }
    }
    return value;
};

// Update field value
const updateField = (field: SchemaField, value: any) => {
    if (props.activeTab === 'style' || field.tab === 'style') {
        emit('updateStyle', field.key, value);
    } else {
        emit('updateContent', field.key, value);
    }
};

// Items management
const addItem = (field: ItemsField) => {
    const currentItems = getFieldValue(field) || [];
    const newItem: Record<string, any> = {};
    field.itemFields.forEach(itemField => { newItem[itemField.key] = ''; });
    updateField(field, [...currentItems, newItem]);
};

const removeItem = (field: ItemsField, index: number) => {
    const currentItems = [...(getFieldValue(field) || [])];
    currentItems.splice(index, 1);
    updateField(field, currentItems);
};

const updateItemField = (field: ItemsField, itemIndex: number, itemFieldKey: string, value: any) => {
    const currentItems = [...(getFieldValue(field) || [])];
    if (currentItems[itemIndex]) {
        currentItems[itemIndex] = { ...currentItems[itemIndex], [itemFieldKey]: value };
        updateField(field, currentItems);
    }
};

// Icon picker handlers
const openIconPicker = (field: string, itemIndex?: number) => {
    iconPickerTarget.value = { field, itemIndex };
    showIconPicker.value = true;
};

const handleIconSelect = (iconName: string, iconStyle: 'outline' | 'solid') => {
    if (iconPickerTarget.value) {
        const { field, itemIndex } = iconPickerTarget.value;
        if (itemIndex !== undefined) {
            const items = [...(props.section.content[field] || [])];
            if (items[itemIndex]) {
                items[itemIndex] = { ...items[itemIndex], icon: iconName, iconStyle };
                emit('updateContent', field, items);
            }
        } else {
            emit('updateContent', field, iconName);
        }
    }
    showIconPicker.value = false;
    iconPickerTarget.value = null;
};

// Video embed handlers
const openVideoEmbed = (field: string) => {
    videoEmbedTarget.value = field;
    showVideoEmbed.value = true;
};

const handleVideoSelect = (videoInfo: { platform: string; videoId: string; embedUrl: string; thumbnailUrl: string }) => {
    if (videoEmbedTarget.value) {
        emit('updateContent', videoEmbedTarget.value, videoInfo.embedUrl);
        emit('updateContent', `${videoEmbedTarget.value}Thumbnail`, videoInfo.thumbnailUrl);
        emit('updateContent', `${videoEmbedTarget.value}Platform`, videoInfo.platform);
    }
    showVideoEmbed.value = false;
    videoEmbedTarget.value = null;
};

// Info helpers
const getInfoIcon = (variant?: 'info' | 'warning' | 'success') => {
    switch (variant) {
        case 'warning': return ExclamationTriangleIcon;
        case 'success': return CheckCircleIcon;
        default: return InformationCircleIcon;
    }
};

const getInfoClasses = (variant?: 'info' | 'warning' | 'success') => {
    switch (variant) {
        case 'warning': return 'bg-amber-50 border-amber-200 text-amber-700';
        case 'success': return 'bg-green-50 border-green-200 text-green-700';
        default: return 'bg-blue-50 border-blue-200 text-blue-700';
    }
};
</script>

<template>
    <div class="flex flex-col h-full min-h-0">
        <!-- Inspector Tabs -->
        <div class="flex border-b border-gray-200 flex-shrink-0">
            <button @click="emit('update:activeTab', 'content')" :class="['flex-1 py-1.5 text-xs font-medium transition-colors border-b-2', activeTab === 'content' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700']">Content</button>
            <button @click="emit('update:activeTab', 'style')" :class="['flex-1 py-1.5 text-xs font-medium transition-colors border-b-2', activeTab === 'style' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700']">Style</button>
            <button @click="emit('update:activeTab', 'advanced')" :class="['flex-1 py-1.5 text-xs font-medium transition-colors border-b-2', activeTab === 'advanced' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700']">Advanced</button>
        </div>

        <!-- Inspector Content -->
        <div :class="['flex-1 overflow-y-auto p-2', darkMode ? 'custom-scrollbar-dark' : 'custom-scrollbar']">
            <div v-if="!schema" class="text-center py-8 text-gray-500">
                <p class="text-xs">No configuration available for this section type.</p>
            </div>

            <div v-else class="space-y-3">
                <template v-for="field in currentFields" :key="field.key">
                    <div v-if="isFieldVisible(field)">
                        <!-- Divider -->
                        <template v-if="field.type === 'divider'">
                            <div class="border-t border-gray-200 pt-2">
                                <span v-if="field.label" class="text-[10px] font-medium text-gray-500 uppercase tracking-wide">{{ field.label }}</span>
                            </div>
                        </template>

                        <!-- Info Box -->
                        <template v-else-if="field.type === 'info'">
                            <div :class="['p-2 rounded-md border text-xs flex items-start gap-2', getInfoClasses((field as InfoField).variant)]">
                                <component :is="getInfoIcon((field as InfoField).variant)" class="w-4 h-4 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                <span>{{ (field as InfoField).message }}</span>
                            </div>
                        </template>

                        <!-- Link -->
                        <template v-else-if="field.type === 'link'">
                            <a :href="(field as LinkField).url" :target="(field as LinkField).external ? '_blank' : undefined" class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 hover:underline">
                                {{ field.label }}
                                <ArrowTopRightOnSquareIcon v-if="(field as LinkField).external" class="w-3 h-3" aria-hidden="true" />
                            </a>
                        </template>

                        <!-- Text Input -->
                        <template v-else-if="field.type === 'text'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ field.label }}</label>
                                <input :value="getFieldValue(field)" @input="updateField(field, ($event.target as HTMLInputElement).value)" :placeholder="field.placeholder" class="w-full px-2 py-1.5 border border-gray-300 rounded-md text-xs text-gray-900" />
                            </div>
                        </template>

                        <!-- Textarea -->
                        <template v-else-if="field.type === 'textarea'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ field.label }}</label>
                                <textarea :value="getFieldValue(field)" @input="updateField(field, ($event.target as HTMLTextAreaElement).value)" :placeholder="field.placeholder" :rows="field.rows || 3" class="w-full px-2 py-1.5 border border-gray-300 rounded-md text-xs text-gray-900"></textarea>
                            </div>
                        </template>

                        <!-- Number -->
                        <template v-else-if="field.type === 'number'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ field.label }}</label>
                                <input type="number" :value="getFieldValue(field)" @input="updateField(field, parseFloat(($event.target as HTMLInputElement).value))" :min="field.min" :max="field.max" :step="field.step" class="w-full px-2 py-1.5 border border-gray-300 rounded-md text-xs text-gray-900" />
                            </div>
                        </template>

                        <!-- Select -->
                        <template v-else-if="field.type === 'select'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ field.label }}</label>
                                <select :value="getFieldValue(field)" @change="updateField(field, ($event.target as HTMLSelectElement).value)" class="w-full px-2 py-1.5 border border-gray-300 rounded-md text-xs text-gray-900">
                                    <option v-for="opt in (field as SelectField).options" :key="String(opt.value)" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                        </template>

                        <!-- Checkbox -->
                        <template v-else-if="field.type === 'checkbox'">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" :id="`field-${field.key}`" :checked="getFieldValue(field)" @change="updateField(field, ($event.target as HTMLInputElement).checked)" class="rounded border-gray-300 text-blue-600 w-3.5 h-3.5" />
                                <label :for="`field-${field.key}`" class="text-xs text-gray-700">{{ field.label }}</label>
                            </div>
                        </template>

                        <!-- Button Group -->
                        <template v-else-if="field.type === 'buttonGroup'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ field.label }}</label>
                                <div class="flex gap-1">
                                    <button v-for="opt in (field as ButtonGroupField).options" :key="String(opt.value)" @click="updateField(field, opt.value)" :class="['flex-1 px-2 py-1.5 rounded-md border-2 text-xs font-medium transition-colors', getFieldValue(field) === opt.value ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300']">{{ opt.label }}</button>
                                </div>
                            </div>
                        </template>

                        <!-- Range -->
                        <template v-else-if="field.type === 'range'">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-xs font-medium text-gray-700">{{ field.label }}</label>
                                    <span class="text-xs text-gray-500">{{ getFieldValue(field) || (field as RangeField).min }}{{ (field as RangeField).unit || '' }}</span>
                                </div>
                                <input type="range" :value="getFieldValue(field) || (field as RangeField).min" @input="updateField(field, parseInt(($event.target as HTMLInputElement).value))" :min="(field as RangeField).min" :max="(field as RangeField).max" :step="(field as RangeField).step || 1" class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                            </div>
                        </template>

                        <!-- Color -->
                        <template v-else-if="field.type === 'color'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ field.label }}</label>
                                <div class="flex items-center gap-1.5">
                                    <div class="relative w-8 h-8 flex-shrink-0">
                                        <input type="color" :value="getFieldValue(field) || '#ffffff'" @input="updateField(field, ($event.target as HTMLInputElement).value)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                        <div class="w-full h-full rounded border border-gray-300 shadow-sm" :style="{ backgroundColor: getFieldValue(field) || '#ffffff' }"></div>
                                    </div>
                                    <input :value="getFieldValue(field) || '#ffffff'" @input="updateField(field, ($event.target as HTMLInputElement).value)" class="flex-1 px-1.5 py-1 border border-gray-300 rounded text-xs font-mono text-gray-900" />
                                </div>
                                <div v-if="(field as ColorField).presets?.length" class="flex gap-1 mt-1.5">
                                    <button v-for="preset in (field as ColorField).presets" :key="preset" @click="updateField(field, preset)" class="w-5 h-5 rounded border border-gray-200 hover:scale-110 transition-transform" :style="{ backgroundColor: preset }" :title="preset"></button>
                                </div>
                            </div>
                        </template>

                        <!-- Image -->
                        <template v-else-if="field.type === 'image'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ field.label }}</label>
                                <div v-if="getFieldValue(field)" class="relative mb-1.5">
                                    <img :src="getFieldValue(field)" class="w-full h-20 object-cover rounded-md" />
                                    <button @click="updateField(field, '')" class="absolute top-1 right-1 p-0.5 bg-red-500 text-white rounded-full hover:bg-red-600" aria-label="Remove image">
                                        <XMarkIcon class="w-3 h-3" aria-hidden="true" />
                                    </button>
                                </div>
                                <button @click="emit('openMediaLibrary', section.id, field.key)" class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 border-2 border-dashed border-gray-300 rounded-md text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors">
                                    <PhotoIcon class="w-4 h-4" aria-hidden="true" />
                                    <span class="text-xs">{{ getFieldValue(field) ? 'Change' : 'Add Image' }}</span>
                                </button>
                            </div>
                        </template>

                        <!-- Video -->
                        <template v-else-if="field.type === 'video'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ field.label }}</label>
                                <div v-if="getFieldValue(field)" class="relative mb-1.5">
                                    <img :src="section.content[`${field.key}Thumbnail`] || 'https://via.placeholder.com/640x360?text=Video'" class="w-full h-20 object-cover rounded-md" />
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 rounded-md">
                                        <div class="w-8 h-8 bg-white/90 rounded-full flex items-center justify-center">
                                            <FilmIcon class="w-4 h-4 text-gray-900" aria-hidden="true" />
                                        </div>
                                    </div>
                                    <button @click="updateField(field, ''); emit('updateContent', `${field.key}Thumbnail`, '')" class="absolute top-1 right-1 p-0.5 bg-red-500 text-white rounded-full hover:bg-red-600" aria-label="Remove video">
                                        <XMarkIcon class="w-3 h-3" aria-hidden="true" />
                                    </button>
                                </div>
                                <button @click="openVideoEmbed(field.key)" class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 border-2 border-dashed border-gray-300 rounded-md text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors">
                                    <FilmIcon class="w-4 h-4" aria-hidden="true" />
                                    <span class="text-xs">{{ getFieldValue(field) ? 'Change Video' : 'Add Video' }}</span>
                                </button>
                            </div>
                        </template>

                        <!-- Background Type -->
                        <template v-else-if="field.type === 'backgroundType'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">{{ field.label }}</label>
                                <div class="flex gap-1.5">
                                    <button v-for="opt in (field as any).options" :key="opt" @click="updateField(field, opt)" :class="['flex-1 flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-md border-2 transition-colors text-xs', (!getFieldValue(field) && opt === 'image') || getFieldValue(field) === opt ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300']">
                                        <PhotoIcon v-if="opt === 'image'" class="w-3.5 h-3.5" aria-hidden="true" />
                                        <FilmIcon v-else-if="opt === 'video'" class="w-3.5 h-3.5" aria-hidden="true" />
                                        <span class="capitalize">{{ opt }}</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Overlay -->
                        <template v-else-if="field.type === 'overlay'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ field.label }}</label>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" :id="`overlay-${field.key}`" :checked="section.style?.overlayEnabled" @change="emit('updateStyle', 'overlayEnabled', ($event.target as HTMLInputElement).checked)" class="rounded border-gray-300 text-blue-600 w-3.5 h-3.5" />
                                        <label :for="`overlay-${field.key}`" class="text-xs text-gray-700">Enable Overlay</label>
                                    </div>
                                    <div v-if="section.style?.overlayEnabled" class="space-y-2 pl-5">
                                        <div class="flex items-center gap-1.5">
                                            <div class="relative w-6 h-6 flex-shrink-0">
                                                <input type="color" :value="section.style?.overlayColor || '#000000'" @input="emit('updateStyle', 'overlayColor', ($event.target as HTMLInputElement).value)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                                <div class="w-full h-full rounded border border-gray-300" :style="{ backgroundColor: section.style?.overlayColor || '#000000' }"></div>
                                            </div>
                                            <input :value="section.style?.overlayColor || '#000000'" @input="emit('updateStyle', 'overlayColor', ($event.target as HTMLInputElement).value)" class="flex-1 px-1.5 py-1 border border-gray-300 rounded text-xs font-mono text-gray-900" />
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-xs text-gray-600">Opacity</span>
                                                <span class="text-xs text-gray-500">{{ Math.round((section.style?.overlayOpacity || 0.5) * 100) }}%</span>
                                            </div>
                                            <input type="range" :value="(section.style?.overlayOpacity || 0.5) * 100" @input="emit('updateStyle', 'overlayOpacity', parseInt(($event.target as HTMLInputElement).value) / 100)" min="0" max="100" step="5" class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Gradient -->
                        <template v-else-if="field.type === 'gradient'">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ field.label }}</label>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" :id="`gradient-${field.key}`" :checked="section.style?.useGradient" @change="emit('updateStyle', 'useGradient', ($event.target as HTMLInputElement).checked)" class="rounded border-gray-300 text-blue-600 w-3.5 h-3.5" />
                                        <label :for="`gradient-${field.key}`" class="text-xs text-gray-700">Use Gradient</label>
                                    </div>
                                    <div v-if="section.style?.useGradient" class="space-y-2 pl-5">
                                        <div class="flex gap-2">
                                            <div class="flex-1">
                                                <span class="text-[10px] text-gray-500">From</span>
                                                <div class="flex items-center gap-1 mt-0.5">
                                                    <div class="relative w-6 h-6 flex-shrink-0">
                                                        <input type="color" :value="section.style?.gradientFrom || '#2563eb'" @input="emit('updateStyle', 'gradientFrom', ($event.target as HTMLInputElement).value)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                                        <div class="w-full h-full rounded border border-gray-300" :style="{ backgroundColor: section.style?.gradientFrom || '#2563eb' }"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <span class="text-[10px] text-gray-500">To</span>
                                                <div class="flex items-center gap-1 mt-0.5">
                                                    <div class="relative w-6 h-6 flex-shrink-0">
                                                        <input type="color" :value="section.style?.gradientTo || '#7c3aed'" @input="emit('updateStyle', 'gradientTo', ($event.target as HTMLInputElement).value)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                                        <div class="w-full h-full rounded border border-gray-300" :style="{ backgroundColor: section.style?.gradientTo || '#7c3aed' }"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-1">
                                            <button v-for="preset in gradientPresets" :key="preset.name" @click="emit('updateStyle', 'gradientFrom', preset.from); emit('updateStyle', 'gradientTo', preset.to)" class="w-8 h-5 rounded border border-gray-200 hover:scale-105 transition-transform" :style="{ background: `linear-gradient(to right, ${preset.from}, ${preset.to})` }" :title="preset.name"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Items (repeatable fields) -->
                        <template v-else-if="field.type === 'items'">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-medium text-gray-700">{{ field.label }}</label>
                                    <button @click="addItem(field as ItemsField)" class="flex items-center gap-0.5 text-xs text-blue-600 hover:text-blue-700">
                                        <PlusIcon class="w-3.5 h-3.5" aria-hidden="true" />
                                        <span>Add</span>
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="(item, itemIndex) in (getFieldValue(field) || [])" :key="itemIndex" class="p-2 bg-gray-50 rounded-md border border-gray-200">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-[10px] font-medium text-gray-500">Item {{ itemIndex + 1 }}</span>
                                            <button @click="removeItem(field as ItemsField, itemIndex)" class="p-0.5 text-red-500 hover:text-red-600" aria-label="Remove item">
                                                <TrashIcon class="w-3.5 h-3.5" aria-hidden="true" />
                                            </button>
                                        </div>
                                        <div class="space-y-1.5">
                                            <template v-for="itemField in (field as ItemsField).itemFields" :key="itemField.key">
                                                <!-- Item Text -->
                                                <div v-if="itemField.type === 'text'">
                                                    <label class="block text-[10px] text-gray-600 mb-0.5">{{ itemField.label }}</label>
                                                    <input :value="item[itemField.key]" @input="updateItemField(field as ItemsField, itemIndex, itemField.key, ($event.target as HTMLInputElement).value)" :placeholder="itemField.placeholder" class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-gray-900" />
                                                </div>
                                                <!-- Item Textarea -->
                                                <div v-else-if="itemField.type === 'textarea'">
                                                    <label class="block text-[10px] text-gray-600 mb-0.5">{{ itemField.label }}</label>
                                                    <textarea :value="item[itemField.key]" @input="updateItemField(field as ItemsField, itemIndex, itemField.key, ($event.target as HTMLTextAreaElement).value)" :placeholder="itemField.placeholder" rows="2" class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-gray-900"></textarea>
                                                </div>
                                                <!-- Item Image -->
                                                <div v-else-if="itemField.type === 'image'">
                                                    <label class="block text-[10px] text-gray-600 mb-0.5">{{ itemField.label }}</label>
                                                    <div v-if="item[itemField.key]" class="relative mb-1">
                                                        <img :src="item[itemField.key]" class="w-full h-12 object-cover rounded" />
                                                        <button @click="updateItemField(field as ItemsField, itemIndex, itemField.key, '')" class="absolute top-0.5 right-0.5 p-0.5 bg-red-500 text-white rounded-full hover:bg-red-600" aria-label="Remove image">
                                                            <XMarkIcon class="w-2.5 h-2.5" aria-hidden="true" />
                                                        </button>
                                                    </div>
                                                    <button @click="emit('openMediaLibrary', section.id, `${field.key}.${itemIndex}.${itemField.key}`)" class="w-full flex items-center justify-center gap-1 px-2 py-1 border border-dashed border-gray-300 rounded text-gray-500 hover:border-blue-400 hover:text-blue-600 text-[10px]">
                                                        <PhotoIcon class="w-3 h-3" aria-hidden="true" />
                                                        <span>{{ item[itemField.key] ? 'Change' : 'Add' }}</span>
                                                    </button>
                                                </div>
                                                <!-- Item Icon -->
                                                <div v-else-if="itemField.type === 'icon'">
                                                    <label class="block text-[10px] text-gray-600 mb-0.5">{{ itemField.label }}</label>
                                                    <button @click="openIconPicker(field.key, itemIndex)" class="w-full flex items-center justify-center gap-1 px-2 py-1 border border-dashed border-gray-300 rounded text-gray-500 hover:border-blue-400 hover:text-blue-600 text-[10px]">
                                                        <span>{{ item.icon || 'Select Icon' }}</span>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Icon Picker Modal -->
    <IconPickerModal v-model:show="showIconPicker" @select="handleIconSelect" />

    <!-- Video Embed Modal -->
    <VideoEmbedModal v-model:show="showVideoEmbed" @select="handleVideoSelect" />
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 2px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 2px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }
.custom-scrollbar-dark::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-dark::-webkit-scrollbar-track { background: #374151; border-radius: 2px; }
.custom-scrollbar-dark::-webkit-scrollbar-thumb { background: #6b7280; border-radius: 2px; }
.custom-scrollbar-dark::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>
