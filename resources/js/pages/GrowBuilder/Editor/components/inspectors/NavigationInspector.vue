<script setup lang="ts">
/**
 * Navigation Inspector Component
 * Settings panel for editing site-wide navigation with drag-and-drop reordering
 */
import draggable from 'vuedraggable';
import { 
    XMarkIcon, 
    PhotoIcon, 
    TrashIcon, 
    Bars3Icon,
    ChevronUpIcon,
    ChevronDownIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';
import { PageLinkSelector } from '../common';

interface NavItem {
    id: string;
    label: string;
    url: string;
    pageId?: number | null;
    isExternal: boolean;
    children: NavItem[];
}

interface Page {
    id: number;
    title: string;
    slug: string;
    isHomepage: boolean;
}

interface SiteNavigation {
    logoText: string;
    logo: string;
    navItems: NavItem[];
    showCta: boolean;
    ctaText: string;
    ctaLink: string;
    sticky: boolean;
    style: string;
    // Auth buttons
    showAuthButtons: boolean;
    showLoginButton: boolean;
    showRegisterButton: boolean;
    loginText: string;
    registerText: string;
    loginStyle: 'link' | 'outline' | 'solid';
    registerStyle: 'link' | 'outline' | 'solid';
}

const props = defineProps<{
    navigation: SiteNavigation;
    pages: Page[];
    darkMode?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:navigation', value: SiteNavigation): void;
    (e: 'openMediaLibrary', target: string, field: string): void;
}>();

// Navigation style options
const navStyleOptions = [
    { id: 'default', name: 'Default', description: 'Clean & simple' },
    { id: 'centered', name: 'Centered', description: 'Logo in middle' },
    { id: 'split', name: 'Split', description: 'Links both sides' },
    { id: 'floating', name: 'Floating', description: 'Rounded & shadow' },
    { id: 'transparent', name: 'Transparent', description: 'Overlay on hero' },
    { id: 'dark', name: 'Dark', description: 'Dark background' },
    { id: 'sidebar', name: 'Sidebar', description: 'Hamburger menu' },
    { id: 'mega', name: 'Mega Menu', description: 'Dropdown columns' },
];

const addNavItem = () => {
    props.navigation.navItems.push({
        id: `nav-${Date.now()}`,
        label: 'New Link',
        url: '#',
        pageId: null,
        isExternal: false,
        children: [],
    });
};

const removeNavItem = (index: number) => {
    props.navigation.navItems.splice(index, 1);
};

const moveNavItem = (index: number, direction: 'up' | 'down') => {
    const newIndex = direction === 'up' ? index - 1 : index + 1;
    if (newIndex < 0 || newIndex >= props.navigation.navItems.length) return;
    const [item] = props.navigation.navItems.splice(index, 1);
    props.navigation.navItems.splice(newIndex, 0, item);
};

const updateNavItemUrl = (navItem: NavItem, url: string) => {
    navItem.url = url;
    // Check if URL matches a page and update pageId
    if (url === '/') {
        const homepage = props.pages.find(p => p.isHomepage);
        navItem.pageId = homepage?.id || null;
    } else if (url.startsWith('/')) {
        const slug = url.slice(1);
        const page = props.pages.find(p => p.slug === slug);
        navItem.pageId = page?.id || null;
    } else {
        navItem.pageId = null;
    }
};

const clearLogo = () => {
    props.navigation.logo = '';
};
</script>

<template>
    <div :class="['flex-1 overflow-y-auto p-4 space-y-4', darkMode ? 'custom-scrollbar-dark' : 'custom-scrollbar']">
        <!-- Logo Upload -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
            <div v-if="navigation.logo" class="relative mb-2">
                <img :src="navigation.logo" class="h-12 object-contain rounded border border-gray-200 bg-gray-50 p-1" alt="Logo" />
                <button @click="clearLogo" class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600" aria-label="Remove logo">
                    <XMarkIcon class="w-3 h-3" aria-hidden="true" />
                </button>
            </div>
            <button
                @click="emit('openMediaLibrary', 'navigation', 'logo')"
                class="w-full flex items-center justify-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors"
            >
                <PhotoIcon class="w-5 h-5" aria-hidden="true" />
                <span class="text-sm">{{ navigation.logo ? 'Change Logo' : 'Upload Logo' }}</span>
            </button>
        </div>

        <!-- Logo Text -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Logo Text (fallback)</label>
            <input
                v-model="navigation.logoText"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                placeholder="Your Business Name"
            />
            <p class="text-xs text-gray-400 mt-1">Shown when no logo image is set</p>
        </div>
        
        <!-- Navigation Links with Drag & Drop -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-gray-700">Navigation Links</label>
                <button @click="addNavItem" class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                    <PlusIcon class="w-3.5 h-3.5" aria-hidden="true" />
                    Add Link
                </button>
            </div>
            
            <!-- Drag hint -->
            <p v-if="navigation.navItems.length > 1" class="text-xs text-gray-400 mb-2 flex items-center gap-1">
                <Bars3Icon class="w-3.5 h-3.5" aria-hidden="true" />
                Drag to reorder links
            </p>
            
            <!-- Draggable Navigation Items -->
            <draggable
                :list="navigation.navItems"
                item-key="id"
                handle=".drag-handle"
                ghost-class="opacity-40"
                chosen-class="chosen-item"
                drag-class="dragging-item"
                :animation="200"
                class="space-y-2"
            >
                <template #item="{ element: navItem, index: idx }">
                    <div class="group p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-sm transition-all">
                        <!-- Header with drag handle and controls -->
                        <div class="flex items-center gap-2 mb-2">
                            <!-- Drag Handle - Always visible, larger touch target -->
                            <div 
                                class="drag-handle cursor-grab active:cursor-grabbing p-2 -m-1 bg-gray-100 hover:bg-blue-100 rounded transition-colors flex-shrink-0 touch-none select-none" 
                                title="Drag to reorder"
                            >
                                <Bars3Icon class="w-4 h-4 text-gray-500 pointer-events-none" aria-hidden="true" />
                            </div>
                            
                            <!-- Label Input -->
                            <input
                                v-model="navItem.label"
                                placeholder="Label"
                                class="flex-1 min-w-0 px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            />
                            
                            <!-- Move Up/Down Buttons -->
                            <div class="flex items-center flex-shrink-0">
                                <button 
                                    @click="moveNavItem(idx, 'up')" 
                                    :disabled="idx === 0"
                                    class="p-1 hover:bg-gray-200 rounded disabled:opacity-30 disabled:cursor-not-allowed"
                                    :aria-label="`Move ${navItem.label} up`"
                                    title="Move up"
                                >
                                    <ChevronUpIcon class="w-4 h-4 text-gray-500" aria-hidden="true" />
                                </button>
                                <button 
                                    @click="moveNavItem(idx, 'down')" 
                                    :disabled="idx === navigation.navItems.length - 1"
                                    class="p-1 hover:bg-gray-200 rounded disabled:opacity-30 disabled:cursor-not-allowed"
                                    :aria-label="`Move ${navItem.label} down`"
                                    title="Move down"
                                >
                                    <ChevronDownIcon class="w-4 h-4 text-gray-500" aria-hidden="true" />
                                </button>
                            </div>
                            
                            <!-- Delete Button -->
                            <button 
                                @click="removeNavItem(idx)" 
                                class="p-1 hover:bg-red-100 rounded transition-colors flex-shrink-0"
                                :aria-label="`Remove ${navItem.label}`"
                                title="Remove link"
                            >
                                <TrashIcon class="w-4 h-4 text-red-500" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <!-- Page Link Selector -->
                        <PageLinkSelector
                            :model-value="navItem.url"
                            @update:model-value="updateNavItemUrl(navItem, $event)"
                            :pages="pages"
                            placeholder="Select page or enter URL..."
                            size="sm"
                        />
                        
                        <!-- External Link Toggle -->
                        <div class="flex items-center gap-2 mt-2">
                            <input 
                                v-model="navItem.isExternal" 
                                type="checkbox" 
                                :id="`external-${navItem.id}`" 
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label :for="`external-${navItem.id}`" class="text-xs text-gray-500">Open in new tab</label>
                        </div>
                    </div>
                </template>
            </draggable>
            
            <!-- Empty State -->
            <div v-if="navigation.navItems.length === 0" class="text-center py-6 border-2 border-dashed border-gray-200 rounded-lg">
                <Bars3Icon class="w-8 h-8 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                <p class="text-gray-400 text-sm mb-2">No navigation links yet</p>
                <button @click="addNavItem" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Add your first link
                </button>
            </div>
        </div>

        <!-- CTA Button -->
        <div class="border-t border-gray-200 pt-4">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Call to Action Button</h4>
            <div class="flex items-center gap-2 mb-3">
                <input v-model="navigation.showCta" type="checkbox" id="showCta" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <label for="showCta" class="text-sm text-gray-700">Show CTA button</label>
            </div>
            <div v-if="navigation.showCta" class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Button Text</label>
                    <input
                        v-model="navigation.ctaText"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Contact Us"
                    />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Button Link</label>
                    <PageLinkSelector
                        v-model="navigation.ctaLink"
                        :pages="pages"
                        placeholder="Select page or enter URL..."
                        size="sm"
                    />
                </div>
            </div>
        </div>

        <!-- Auth Buttons (Login/Register) -->
        <div class="border-t border-gray-200 pt-4">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Member Access Buttons</h4>
            <p class="text-xs text-gray-400 mb-3">Show login and register buttons for your site's member area</p>
            
            <div class="flex items-center gap-2 mb-3">
                <input v-model="navigation.showAuthButtons" type="checkbox" id="showAuth" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <label for="showAuth" class="text-sm text-gray-700">Enable member access buttons</label>
            </div>
            
            <div v-if="navigation.showAuthButtons" class="space-y-4">
                <!-- Login Button -->
                <div class="p-3 bg-gray-50 rounded-lg space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-medium text-gray-600">Login Button</label>
                        <div class="flex items-center gap-2">
                            <input 
                                v-model="navigation.showLoginButton" 
                                type="checkbox" 
                                id="showLoginBtn" 
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-3.5 w-3.5"
                            />
                            <label for="showLoginBtn" class="text-xs text-gray-500">Show</label>
                        </div>
                    </div>
                    <div v-if="navigation.showLoginButton !== false" class="space-y-2">
                        <input
                            v-model="navigation.loginText"
                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Login"
                        />
                        <div class="flex gap-2">
                            <button
                                v-for="style in ['link', 'outline', 'solid']"
                                :key="style"
                                @click="navigation.loginStyle = style as 'link' | 'outline' | 'solid'"
                                :class="[
                                    'flex-1 py-1.5 text-xs rounded transition-all',
                                    navigation.loginStyle === style || (!navigation.loginStyle && style === 'link')
                                        ? 'bg-blue-100 text-blue-700 font-medium'
                                        : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                {{ style.charAt(0).toUpperCase() + style.slice(1) }}
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-xs text-gray-400 italic">Login button is hidden</p>
                </div>
                
                <!-- Register Button -->
                <div class="p-3 bg-gray-50 rounded-lg space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-medium text-gray-600">Register Button</label>
                        <div class="flex items-center gap-2">
                            <input 
                                v-model="navigation.showRegisterButton" 
                                type="checkbox" 
                                id="showRegisterBtn" 
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-3.5 w-3.5"
                            />
                            <label for="showRegisterBtn" class="text-xs text-gray-500">Show</label>
                        </div>
                    </div>
                    <div v-if="navigation.showRegisterButton !== false" class="space-y-2">
                        <input
                            v-model="navigation.registerText"
                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Sign Up"
                        />
                        <div class="flex gap-2">
                            <button
                                v-for="style in ['link', 'outline', 'solid']"
                                :key="style"
                                @click="navigation.registerStyle = style as 'link' | 'outline' | 'solid'"
                                :class="[
                                    'flex-1 py-1.5 text-xs rounded transition-all',
                                    navigation.registerStyle === style || (!navigation.registerStyle && style === 'solid')
                                        ? 'bg-blue-100 text-blue-700 font-medium'
                                        : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                {{ style.charAt(0).toUpperCase() + style.slice(1) }}
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-xs text-gray-400 italic">Register button is hidden</p>
                </div>
            </div>
        </div>

        <!-- Sticky & Style -->
        <div class="border-t border-gray-200 pt-4">
            <div class="flex items-center gap-2 mb-4">
                <input v-model="navigation.sticky" type="checkbox" id="stickyNav" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <label for="stickyNav" class="text-sm text-gray-700">Sticky navigation</label>
            </div>
            
            <!-- Navigation Style -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Navigation Style</label>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        v-for="style in navStyleOptions"
                        :key="style.id"
                        @click="navigation.style = style.id"
                        :class="[
                            'p-3 border-2 rounded-lg text-left transition-all',
                            navigation.style === style.id || (!navigation.style && style.id === 'default') ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                        ]"
                    >
                        <div class="text-xs font-medium text-gray-900">{{ style.name }}</div>
                        <div class="text-xs text-gray-500">{{ style.description }}</div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.drag-handle {
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
