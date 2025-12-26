<script setup lang="ts">
/**
 * Footer Inspector Component
 * Settings panel for editing site-wide footer with drag-and-drop reordering
 */
import draggable from 'vuedraggable';
import { 
    XMarkIcon, 
    PhotoIcon, 
    TrashIcon, 
    Bars3Icon,
    ChevronUpIcon,
    ChevronDownIcon,
    PlusIcon,
    LinkIcon
} from '@heroicons/vue/24/outline';
import { PageLinkSelector } from '../common';

interface FooterLink {
    id: string;
    label: string;
    url: string;
}

interface SocialLink {
    id: string;
    platform: 'facebook' | 'twitter' | 'instagram' | 'linkedin' | 'youtube' | 'tiktok';
    url: string;
}

interface FooterColumn {
    id: string;
    title: string;
    links: FooterLink[];
}

interface Page {
    id: number;
    title: string;
    slug: string;
    isHomepage: boolean;
}

interface SiteFooter {
    logo: string;
    copyrightText: string;
    showSocialLinks: boolean;
    socialLinks: SocialLink[];
    columns: FooterColumn[];
    showNewsletter: boolean;
    newsletterTitle: string;
    backgroundColor: string;
    textColor: string;
    layout: string;
}

const props = defineProps<{
    footer: SiteFooter;
    pages: Page[];
    darkMode?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:footer', value: SiteFooter): void;
    (e: 'openMediaLibrary', target: string, field: string): void;
}>();

// Footer layout options
const footerLayoutOptions = [
    { id: 'columns', name: 'Columns', description: 'Multi-column layout' },
    { id: 'centered', name: 'Centered', description: 'Simple centered' },
    { id: 'split', name: 'Split', description: 'Brand left, links right' },
    { id: 'minimal', name: 'Minimal', description: 'Just copyright' },
    { id: 'stacked', name: 'Stacked', description: 'Vertically stacked' },
    { id: 'newsletter', name: 'Newsletter', description: 'Email signup focus' },
    { id: 'social', name: 'Social', description: 'Large social icons' },
    { id: 'contact', name: 'Contact', description: 'Contact info bar' },
];

const socialPlatforms = [
    { value: 'facebook', label: 'Facebook' },
    { value: 'twitter', label: 'Twitter/X' },
    { value: 'instagram', label: 'Instagram' },
    { value: 'linkedin', label: 'LinkedIn' },
    { value: 'youtube', label: 'YouTube' },
    { value: 'tiktok', label: 'TikTok' },
];

// Column management
const addFooterColumn = () => {
    props.footer.columns.push({
        id: `col-${Date.now()}`,
        title: 'New Column',
        links: [],
    });
};

const removeFooterColumn = (index: number) => {
    props.footer.columns.splice(index, 1);
};

const moveFooterColumn = (index: number, direction: 'up' | 'down') => {
    const newIndex = direction === 'up' ? index - 1 : index + 1;
    if (newIndex < 0 || newIndex >= props.footer.columns.length) return;
    const [item] = props.footer.columns.splice(index, 1);
    props.footer.columns.splice(newIndex, 0, item);
};

// Link management
const addFooterLink = (columnIndex: number) => {
    props.footer.columns[columnIndex].links.push({
        id: `link-${Date.now()}`,
        label: 'New Link',
        url: '#',
    });
};

const removeFooterLink = (columnIndex: number, linkIndex: number) => {
    props.footer.columns[columnIndex].links.splice(linkIndex, 1);
};

// Social link management
const addSocialLink = () => {
    props.footer.socialLinks.push({
        id: `social-${Date.now()}`,
        platform: 'facebook',
        url: '',
    });
};

const removeSocialLink = (index: number) => {
    props.footer.socialLinks.splice(index, 1);
};

const clearLogo = () => {
    props.footer.logo = '';
};
</script>

<template>
    <div :class="['flex-1 overflow-y-auto p-4 space-y-4', darkMode ? 'custom-scrollbar-dark' : 'custom-scrollbar']">
        <!-- Footer Layout Style -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Footer Layout</label>
            <div class="grid grid-cols-2 gap-2">
                <button
                    v-for="layout in footerLayoutOptions"
                    :key="layout.id"
                    @click="footer.layout = layout.id"
                    :class="[
                        'p-3 border-2 rounded-lg text-left transition-all',
                        footer.layout === layout.id || (!footer.layout && layout.id === 'columns') ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                    ]"
                >
                    <div class="text-xs font-medium text-gray-900">{{ layout.name }}</div>
                    <div class="text-xs text-gray-500">{{ layout.description }}</div>
                </button>
            </div>
        </div>

        <!-- Logo Upload -->
        <div class="border-t border-gray-200 pt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Footer Logo</label>
            <div v-if="footer.logo" class="relative mb-2">
                <img :src="footer.logo" class="h-12 object-contain rounded border border-gray-200 bg-gray-50 p-1" alt="Footer Logo" />
                <button @click="clearLogo" class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600" aria-label="Remove logo">
                    <XMarkIcon class="w-3 h-3" aria-hidden="true" />
                </button>
            </div>
            <button
                @click="emit('openMediaLibrary', 'footer', 'logo')"
                class="w-full flex items-center justify-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors"
            >
                <PhotoIcon class="w-5 h-5" aria-hidden="true" />
                <span class="text-sm">{{ footer.logo ? 'Change Logo' : 'Upload Logo' }}</span>
            </button>
        </div>

        <!-- Copyright Text -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Copyright Text</label>
            <input
                v-model="footer.copyrightText"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                placeholder="© 2024 Your Company. All rights reserved."
            />
        </div>

        <!-- Footer Columns with Drag & Drop -->
        <div class="border-t border-gray-200 pt-4">
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-gray-700">Footer Columns</label>
                <button @click="addFooterColumn" class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                    <PlusIcon class="w-3.5 h-3.5" aria-hidden="true" />
                    Add Column
                </button>
            </div>
            
            <!-- Drag hint -->
            <p v-if="footer.columns.length > 1" class="text-xs text-gray-400 mb-2 flex items-center gap-1">
                <Bars3Icon class="w-3.5 h-3.5" aria-hidden="true" />
                Drag to reorder columns
            </p>
            
            <!-- Draggable Columns -->
            <draggable
                :list="footer.columns"
                item-key="id"
                handle=".drag-handle"
                ghost-class="opacity-40"
                chosen-class="chosen-item"
                drag-class="dragging-item"
                :animation="200"
                class="space-y-3"
            >
                <template #item="{ element: column, index: colIdx }">
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-sm transition-all">
                        <!-- Column Header - Two rows for better fit -->
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <!-- Drag Handle -->
                                <div class="drag-handle cursor-grab active:cursor-grabbing p-1.5 bg-gray-100 hover:bg-blue-100 rounded transition-colors flex-shrink-0" title="Drag to reorder">
                                    <Bars3Icon class="w-3.5 h-3.5 text-gray-500 pointer-events-none" aria-hidden="true" />
                                </div>
                                <!-- Title Input -->
                                <input
                                    v-model="column.title"
                                    placeholder="Column Title"
                                    class="flex-1 min-w-0 px-2 py-1 border border-gray-300 rounded text-sm font-medium text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <!-- Action buttons - compact -->
                            <div class="flex items-center gap-0.5 flex-shrink-0">
                                <button 
                                    type="button"
                                    @click.stop="moveFooterColumn(colIdx, 'up')" 
                                    :disabled="colIdx === 0"
                                    class="p-1 hover:bg-gray-200 rounded disabled:opacity-30 disabled:cursor-not-allowed"
                                    title="Move up"
                                    :aria-label="`Move ${column.title} up`"
                                >
                                    <ChevronUpIcon class="w-3.5 h-3.5 text-gray-500" aria-hidden="true" />
                                </button>
                                <button 
                                    type="button"
                                    @click.stop="moveFooterColumn(colIdx, 'down')" 
                                    :disabled="colIdx === footer.columns.length - 1"
                                    class="p-1 hover:bg-gray-200 rounded disabled:opacity-30 disabled:cursor-not-allowed"
                                    title="Move down"
                                    :aria-label="`Move ${column.title} down`"
                                >
                                    <ChevronDownIcon class="w-3.5 h-3.5 text-gray-500" aria-hidden="true" />
                                </button>
                                <button 
                                    type="button"
                                    @click.stop="removeFooterColumn(colIdx)" 
                                    class="p-1 hover:bg-red-100 rounded" 
                                    title="Remove column"
                                    :aria-label="`Remove ${column.title} column`"
                                >
                                    <TrashIcon class="w-3.5 h-3.5 text-red-500" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                        
                        <!-- Column Links with Drag & Drop -->
                        <div class="pl-4">
                            <p v-if="column.links.length > 1" class="text-xs text-gray-400 mb-1 flex items-center gap-1">
                                <Bars3Icon class="w-3 h-3" aria-hidden="true" />
                                Drag to reorder links
                            </p>
                            <draggable
                                :list="column.links"
                                item-key="id"
                                handle=".link-drag-handle"
                                ghost-class="opacity-40"
                                chosen-class="bg-blue-50"
                                :animation="150"
                                class="space-y-2 mb-2"
                            >
                                <template #item="{ element: link, index: linkIdx }">
                                    <div class="bg-white rounded-lg p-2 border border-gray-100 hover:border-gray-200 transition-colors">
                                        <!-- Link header row -->
                                        <div class="flex items-center gap-1.5 mb-1.5">
                                            <div class="link-drag-handle cursor-grab active:cursor-grabbing p-1 bg-gray-100 hover:bg-blue-100 rounded flex-shrink-0" title="Drag to reorder">
                                                <Bars3Icon class="w-3 h-3 text-gray-400 pointer-events-none" aria-hidden="true" />
                                            </div>
                                            <input
                                                v-model="link.label"
                                                placeholder="Link label"
                                                class="flex-1 min-w-0 px-2 py-1 border border-gray-200 rounded text-xs text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                            />
                                            <button 
                                                type="button"
                                                @click.stop="removeFooterLink(colIdx, linkIdx)" 
                                                class="p-1 hover:bg-red-100 rounded flex-shrink-0" 
                                                title="Remove link"
                                                :aria-label="`Remove ${link.label} link`"
                                            >
                                                <XMarkIcon class="w-3 h-3 text-red-500" aria-hidden="true" />
                                            </button>
                                        </div>
                                        <!-- Link URL row -->
                                        <PageLinkSelector
                                            v-model="link.url"
                                            :pages="pages"
                                            placeholder="Select page..."
                                            size="sm"
                                        />
                                    </div>
                                </template>
                            </draggable>
                            <button @click="addFooterLink(colIdx)" class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700">
                                <PlusIcon class="w-3 h-3" aria-hidden="true" />
                                Add Link
                            </button>
                        </div>
                    </div>
                </template>
            </draggable>
            
            <!-- Empty State -->
            <div v-if="footer.columns.length === 0" class="text-center py-6 border-2 border-dashed border-gray-200 rounded-lg">
                <Bars3Icon class="w-8 h-8 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                <p class="text-gray-400 text-sm mb-2">No columns yet</p>
                <button @click="addFooterColumn" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Add your first column
                </button>
            </div>
        </div>

        <!-- Social Links with Drag & Drop -->
        <div class="border-t border-gray-200 pt-4">
            <div class="flex items-center gap-2 mb-3">
                <input v-model="footer.showSocialLinks" type="checkbox" id="showSocial" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <label for="showSocial" class="text-sm font-medium text-gray-700">Show Social Links</label>
            </div>
            <div v-if="footer.showSocialLinks">
                <p v-if="footer.socialLinks.length > 1" class="text-xs text-gray-400 mb-2 flex items-center gap-1">
                    <Bars3Icon class="w-3.5 h-3.5" aria-hidden="true" />
                    Drag to reorder
                </p>
                <draggable
                    :list="footer.socialLinks"
                    item-key="id"
                    handle=".social-drag-handle"
                    ghost-class="opacity-40"
                    chosen-class="bg-blue-50"
                    :animation="150"
                    class="space-y-2"
                >
                    <template #item="{ element: social, index: idx }">
                        <div class="flex items-center gap-2 group bg-gray-50 rounded-lg p-2 hover:bg-gray-100 transition-colors">
                            <div class="social-drag-handle cursor-grab active:cursor-grabbing p-1.5 bg-gray-100 hover:bg-blue-100 rounded flex-shrink-0" title="Drag to reorder">
                                <Bars3Icon class="w-4 h-4 text-gray-500 pointer-events-none" aria-hidden="true" />
                            </div>
                            <select
                                v-model="social.platform"
                                class="px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option v-for="platform in socialPlatforms" :key="platform.value" :value="platform.value">{{ platform.label }}</option>
                            </select>
                            <input
                                v-model="social.url"
                                placeholder="Profile URL"
                                class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <button @click="removeSocialLink(idx)" class="p-1 hover:bg-red-100 rounded" title="Remove">
                                <TrashIcon class="w-4 h-4 text-red-500" aria-hidden="true" />
                            </button>
                        </div>
                    </template>
                </draggable>
                <button @click="addSocialLink" class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 mt-2">
                    <PlusIcon class="w-3.5 h-3.5" aria-hidden="true" />
                    Add Social Link
                </button>
            </div>
        </div>

        <!-- Footer Colors -->
        <div class="border-t border-gray-200 pt-4">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Footer Colors</h4>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Background Color</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="color"
                            v-model="footer.backgroundColor"
                            class="w-10 h-10 rounded cursor-pointer border border-gray-200"
                        />
                        <input
                            v-model="footer.backgroundColor"
                            class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-sm font-mono text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Text Color</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="color"
                            v-model="footer.textColor"
                            class="w-10 h-10 rounded cursor-pointer border border-gray-200"
                        />
                        <input
                            v-model="footer.textColor"
                            class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-sm font-mono text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.drag-handle,
.link-drag-handle,
.social-drag-handle {
    touch-action: none;
}
.chosen-item {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
}
.dragging-item {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    transform: scale(1.02) rotate(1deg);
}
</style>
