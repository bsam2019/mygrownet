<script setup lang="ts">
/**
 * PageLinkSelector Component
 * A user-friendly way to select internal pages or enter custom URLs
 * Makes linking to pages easy for non-technical users
 */
import { ref, computed, watch } from 'vue';
import {
    LinkIcon,
    DocumentIcon,
    GlobeAltIcon,
    ChevronDownIcon,
    HomeIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Page {
    id: number;
    title: string;
    slug: string;
    isHomepage: boolean;
}

const props = defineProps<{
    modelValue: string; // The URL value
    pages: Page[];
    placeholder?: string;
    label?: string;
    showLabel?: boolean;
    size?: 'sm' | 'md';
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const isOpen = ref(false);
const searchQuery = ref('');
const linkType = ref<'page' | 'custom' | 'anchor' | 'system'>('page');
const customUrl = ref('');

// Determine current link type from value
const detectLinkType = (url: string) => {
    if (!url) return 'page';
    if (url.startsWith('#')) return 'anchor';
    if (['/login', '/register', '/dashboard'].includes(url)) return 'system';
    if (url.startsWith('http') || url.startsWith('mailto:') || url.startsWith('tel:')) return 'custom';
    return 'page';
};

// Find selected page from URL
const selectedPage = computed(() => {
    if (!props.modelValue) return null;
    const url = props.modelValue;
    if (url === '/') {
        return props.pages.find(p => p.isHomepage) || null;
    }
    const slug = url.startsWith('/') ? url.slice(1) : url;
    return props.pages.find(p => p.slug === slug) || null;
});

// Filter pages by search
const filteredPages = computed(() => {
    if (!searchQuery.value) return props.pages;
    const query = searchQuery.value.toLowerCase();
    return props.pages.filter(p => 
        p.title.toLowerCase().includes(query) || 
        p.slug.toLowerCase().includes(query)
    );
});

// Common anchor links
const commonAnchors = [
    { label: 'Contact Section', value: '#contact' },
    { label: 'About Section', value: '#about' },
    { label: 'Services Section', value: '#services' },
    { label: 'Top of Page', value: '#top' },
];

// Auth/System links
const systemLinks = [
    { label: 'Login Page', value: '/login', icon: '🔐' },
    { label: 'Register Page', value: '/register', icon: '📝' },
    { label: 'Member Dashboard', value: '/dashboard', icon: '📊' },
];

// Initialize state from modelValue
watch(() => props.modelValue, (newVal) => {
    linkType.value = detectLinkType(newVal);
    if (linkType.value === 'custom') {
        customUrl.value = newVal;
    }
}, { immediate: true });

const selectPage = (page: Page) => {
    const url = page.isHomepage ? '/' : `/${page.slug}`;
    emit('update:modelValue', url);
    isOpen.value = false;
    searchQuery.value = '';
};

const selectAnchor = (anchor: string) => {
    emit('update:modelValue', anchor);
    isOpen.value = false;
};

const updateCustomUrl = () => {
    emit('update:modelValue', customUrl.value);
};

const clearSelection = () => {
    emit('update:modelValue', '');
    customUrl.value = '';
    isOpen.value = false;
};

const sizeClasses = computed(() => {
    return props.size === 'sm' 
        ? 'text-xs py-1 px-2' 
        : 'text-sm py-1.5 px-3';
});
</script>

<template>
    <div class="relative">
        <!-- Label -->
        <label v-if="showLabel && label" class="block text-xs text-gray-500 mb-1">
            {{ label }}
        </label>
        
        <!-- Main Button/Display -->
        <div class="relative">
            <button
                type="button"
                @click="isOpen = !isOpen"
                :class="[
                    'w-full flex items-center gap-2 border border-gray-300 rounded-lg bg-white hover:border-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-left',
                    sizeClasses
                ]"
            >
                <!-- Icon based on link type -->
                <component 
                    :is="selectedPage ? DocumentIcon : (modelValue?.startsWith('#') ? LinkIcon : GlobeAltIcon)" 
                    class="w-4 h-4 text-gray-400 flex-shrink-0" 
                    aria-hidden="true" 
                />
                
                <!-- Display value -->
                <span class="flex-1 truncate" :class="modelValue ? 'text-gray-900' : 'text-gray-400'">
                    <template v-if="selectedPage">
                        <span class="flex items-center gap-1">
                            <HomeIcon v-if="selectedPage.isHomepage" class="w-3 h-3" aria-hidden="true" />
                            {{ selectedPage.title }}
                        </span>
                    </template>
                    <template v-else-if="modelValue">
                        {{ modelValue }}
                    </template>
                    <template v-else>
                        {{ placeholder || 'Select a page or enter URL...' }}
                    </template>
                </span>
                
                <!-- Clear button -->
                <button
                    v-if="modelValue"
                    type="button"
                    @click.stop="clearSelection"
                    class="p-0.5 hover:bg-gray-100 rounded"
                    aria-label="Clear selection"
                >
                    <XMarkIcon class="w-3.5 h-3.5 text-gray-400" aria-hidden="true" />
                </button>
                
                <ChevronDownIcon 
                    :class="['w-4 h-4 text-gray-400 transition-transform flex-shrink-0', isOpen ? 'rotate-180' : '']" 
                    aria-hidden="true" 
                />
            </button>
        </div>
        
        <!-- Dropdown Panel -->
        <Teleport to="body">
            <div 
                v-if="isOpen" 
                class="fixed inset-0 z-50"
                @click="isOpen = false"
            >
                <div 
                    class="absolute bg-white rounded-xl shadow-xl border border-gray-200 w-72 max-h-80 overflow-hidden"
                    :style="{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)' }"
                    @click.stop
                >
                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200 bg-gray-50">
                        <button
                            type="button"
                            @click="linkType = 'page'"
                            :class="[
                                'flex-1 py-2 text-xs font-medium transition-colors',
                                linkType === 'page' ? 'text-blue-600 border-b-2 border-blue-600 bg-white' : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            <DocumentIcon class="w-4 h-4 mx-auto mb-0.5" aria-hidden="true" />
                            Pages
                        </button>
                        <button
                            type="button"
                            @click="linkType = 'system'"
                            :class="[
                                'flex-1 py-2 text-xs font-medium transition-colors',
                                linkType === 'system' ? 'text-blue-600 border-b-2 border-blue-600 bg-white' : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            <span class="block text-center mb-0.5">🔐</span>
                            Auth
                        </button>
                        <button
                            type="button"
                            @click="linkType = 'anchor'"
                            :class="[
                                'flex-1 py-2 text-xs font-medium transition-colors',
                                linkType === 'anchor' ? 'text-blue-600 border-b-2 border-blue-600 bg-white' : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            <LinkIcon class="w-4 h-4 mx-auto mb-0.5" aria-hidden="true" />
                            Sections
                        </button>
                        <button
                            type="button"
                            @click="linkType = 'custom'"
                            :class="[
                                'flex-1 py-2 text-xs font-medium transition-colors',
                                linkType === 'custom' ? 'text-blue-600 border-b-2 border-blue-600 bg-white' : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            <GlobeAltIcon class="w-4 h-4 mx-auto mb-0.5" aria-hidden="true" />
                            Custom
                        </button>
                    </div>
                    
                    <!-- Pages Tab -->
                    <div v-if="linkType === 'page'" class="p-2">
                        <!-- Search -->
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search pages..."
                            class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg mb-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        />
                        
                        <!-- Pages List -->
                        <div class="max-h-48 overflow-y-auto space-y-1">
                            <button
                                v-for="page in filteredPages"
                                :key="page.id"
                                type="button"
                                @click="selectPage(page)"
                                :class="[
                                    'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-colors',
                                    selectedPage?.id === page.id ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50 text-gray-700'
                                ]"
                            >
                                <HomeIcon v-if="page.isHomepage" class="w-4 h-4 text-gray-400" aria-hidden="true" />
                                <DocumentIcon v-else class="w-4 h-4 text-gray-400" aria-hidden="true" />
                                <span class="flex-1 text-sm truncate">{{ page.title }}</span>
                                <span class="text-xs text-gray-400">/{{ page.isHomepage ? '' : page.slug }}</span>
                            </button>
                            
                            <div v-if="filteredPages.length === 0" class="text-center py-4 text-gray-400 text-sm">
                                No pages found
                            </div>
                        </div>
                    </div>
                    
                    <!-- System/Auth Links Tab -->
                    <div v-if="linkType === 'system'" class="p-2">
                        <p class="text-xs text-gray-500 mb-2 px-1">Link to member authentication pages</p>
                        <div class="space-y-1">
                            <button
                                v-for="link in systemLinks"
                                :key="link.value"
                                type="button"
                                @click="selectAnchor(link.value)"
                                :class="[
                                    'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-colors',
                                    modelValue === link.value ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50 text-gray-700'
                                ]"
                            >
                                <span class="text-base">{{ link.icon }}</span>
                                <span class="flex-1 text-sm">{{ link.label }}</span>
                                <span class="text-xs text-gray-400">{{ link.value }}</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-3 px-1">These links connect to your site's member area</p>
                    </div>
                    
                    <!-- Anchors Tab -->
                    <div v-if="linkType === 'anchor'" class="p-2">
                        <p class="text-xs text-gray-500 mb-2 px-1">Link to a section on the current page</p>
                        <div class="space-y-1">
                            <button
                                v-for="anchor in commonAnchors"
                                :key="anchor.value"
                                type="button"
                                @click="selectAnchor(anchor.value)"
                                :class="[
                                    'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-colors',
                                    modelValue === anchor.value ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50 text-gray-700'
                                ]"
                            >
                                <LinkIcon class="w-4 h-4 text-gray-400" aria-hidden="true" />
                                <span class="flex-1 text-sm">{{ anchor.label }}</span>
                                <span class="text-xs text-gray-400">{{ anchor.value }}</span>
                            </button>
                        </div>
                        
                        <!-- Custom anchor input -->
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <label class="text-xs text-gray-500 mb-1 block">Custom anchor</label>
                            <div class="flex gap-2">
                                <input
                                    v-model="customUrl"
                                    type="text"
                                    placeholder="#section-name"
                                    class="flex-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500"
                                    @keyup.enter="updateCustomUrl"
                                />
                                <button
                                    type="button"
                                    @click="updateCustomUrl"
                                    class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                >
                                    Set
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Custom URL Tab -->
                    <div v-if="linkType === 'custom'" class="p-3">
                        <p class="text-xs text-gray-500 mb-2">Enter an external URL, email, or phone</p>
                        
                        <input
                            v-model="customUrl"
                            type="text"
                            placeholder="https://example.com"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg mb-3 focus:ring-1 focus:ring-blue-500"
                            @keyup.enter="updateCustomUrl"
                        />
                        
                        <!-- Quick options -->
                        <div class="space-y-2 mb-3">
                            <button
                                type="button"
                                @click="customUrl = 'mailto:'; $nextTick(() => ($refs.customInput as HTMLInputElement)?.focus())"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg text-left"
                            >
                                <span class="text-gray-400">📧</span>
                                Email link (mailto:)
                            </button>
                            <button
                                type="button"
                                @click="customUrl = 'tel:'; $nextTick(() => ($refs.customInput as HTMLInputElement)?.focus())"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg text-left"
                            >
                                <span class="text-gray-400">📞</span>
                                Phone link (tel:)
                            </button>
                        </div>
                        
                        <button
                            type="button"
                            @click="updateCustomUrl"
                            class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            Apply URL
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
