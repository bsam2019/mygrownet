<script setup lang="ts">
/**
 * Navigation Renderer Component
 * Renders the site-wide navigation preview in the editor canvas
 */
import { Bars3Icon } from '@heroicons/vue/24/outline';

interface NavItem {
    id: string;
    label: string;
    url: string;
    pageId?: number;
    isExternal: boolean;
    children: NavItem[];
}

interface SiteNavigation {
    logoText: string;
    logo: string;
    navItems: NavItem[];
    showCta: boolean;
    ctaText: string;
    ctaLink: string;
    sticky: boolean;
    style: 'default' | 'centered' | 'split' | 'floating' | 'transparent' | 'dark' | 'sidebar' | 'mega';
    // Auth buttons
    showAuthButtons?: boolean;
    showLoginButton?: boolean;
    showRegisterButton?: boolean;
    loginText?: string;
    registerText?: string;
    loginStyle?: 'link' | 'outline' | 'solid';
    registerStyle?: 'link' | 'outline' | 'solid';
}

const props = defineProps<{
    navigation: SiteNavigation;
    siteName: string;
    isMobile: boolean;
    isEditing: boolean;
}>();

const emit = defineEmits<{
    (e: 'click'): void;
    (e: 'switchPage', pageId: number): void;
}>();

const handleNavClick = (event: Event, navItem: NavItem) => {
    // Always prevent default navigation in editor preview
    event.preventDefault();
    event.stopPropagation();
    
    console.log('NavigationRenderer: handleNavClick called', { 
        navItem: JSON.parse(JSON.stringify(navItem)), 
        hasPageId: !!navItem.pageId,
        pageId: navItem.pageId,
        label: navItem.label,
        url: navItem.url
    });
    
    // If it has a pageId, switch to that page in the editor
    if (navItem.pageId) {
        console.log('NavigationRenderer: Emitting switchPage event', navItem.pageId);
        emit('switchPage', navItem.pageId);
    } else {
        console.log('NavigationRenderer: No pageId found, cannot switch page. Full navItem:', navItem);
    }
    // Otherwise, it's an external link - do nothing in preview mode
    // (user can use Live preview mode for external links to work)
};
</script>

<template>
    <div 
        class="relative group"
        :class="[
            isEditing ? 'ring-2 ring-blue-500 ring-inset' : 'hover:ring-1 hover:ring-blue-300 hover:ring-inset',
            navigation.style === 'transparent' ? 'absolute top-0 left-0 right-0 z-10' : '',
            navigation.style === 'floating' ? 'm-3 rounded-xl shadow-lg' : 'border-b border-gray-200'
        ]"
        @click.self="emit('click')"
    >
        <!-- Nav Edit Indicator -->
        <div v-if="isEditing" class="absolute top-2 right-2 z-10 flex items-center gap-1 bg-white rounded-lg shadow-lg border border-gray-200 p-1">
            <span class="text-xs text-blue-600 font-medium px-2">Editing Navigation</span>
        </div>
        
        <div 
            :class="[
                'transition-all',
                isMobile ? 'py-3 px-4' : 'py-4 px-6',
                {
                    'bg-white': !navigation.style || navigation.style === 'default' || navigation.style === 'centered' || navigation.style === 'split' || navigation.style === 'mega',
                    'bg-transparent': navigation.style === 'transparent',
                    'bg-gray-900': navigation.style === 'dark' || navigation.style === 'sidebar',
                    'bg-white/95 backdrop-blur-sm rounded-xl': navigation.style === 'floating'
                }
            ]"
        >
            <!-- Mobile Navigation Preview -->
            <div v-if="isMobile" class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img v-if="navigation.logo" :src="navigation.logo" class="h-6 object-contain" alt="Logo" />
                    <span 
                        v-else 
                        class="font-bold text-sm"
                        :class="{
                            'text-gray-900': !navigation.style || navigation.style === 'default' || navigation.style === 'floating' || navigation.style === 'mega',
                            'text-white': navigation.style === 'transparent' || navigation.style === 'dark'
                        }"
                    >
                        {{ navigation.logoText || siteName }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Auth Buttons (Mobile) -->
                    <template v-if="navigation.showAuthButtons">
                        <span 
                            v-if="navigation.showLoginButton !== false"
                            class="text-xs cursor-pointer"
                            :class="{
                                'text-gray-600': !navigation.style || navigation.style === 'default' || navigation.style === 'floating',
                                'text-white/90': navigation.style === 'transparent' || navigation.style === 'dark'
                            }"
                        >
                            {{ navigation.loginText || 'Login' }}
                        </span>
                    </template>
                    <button 
                        v-if="navigation.showCta" 
                        class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                        :class="{
                            'bg-blue-600 text-white': !navigation.style || navigation.style === 'default' || navigation.style === 'floating' || navigation.style === 'mega',
                            'bg-white text-gray-900': navigation.style === 'transparent' || navigation.style === 'dark'
                        }"
                    >
                        {{ navigation.ctaText || 'Contact' }}
                    </button>
                    <div 
                        class="p-1.5 rounded"
                        :class="{
                            'text-gray-600': !navigation.style || navigation.style === 'default' || navigation.style === 'floating',
                            'text-white': navigation.style === 'transparent' || navigation.style === 'dark'
                        }"
                    >
                        <Bars3Icon class="w-5 h-5" aria-hidden="true" />
                    </div>
                </div>
            </div>

            <!-- Desktop: Default, Dark, Transparent, Floating, Mega Styles -->
            <div 
                v-else-if="!navigation.style || navigation.style === 'default' || navigation.style === 'transparent' || navigation.style === 'dark' || navigation.style === 'floating' || navigation.style === 'mega'"
                class="flex items-center justify-between"
            >
                <div class="flex items-center gap-2" @click="emit('click')">
                    <img v-if="navigation.logo" :src="navigation.logo" class="h-8 object-contain cursor-pointer" alt="Logo" />
                    <span 
                        v-else 
                        class="font-bold text-lg cursor-pointer"
                        :class="{
                            'text-gray-900': !navigation.style || navigation.style === 'default' || navigation.style === 'floating' || navigation.style === 'mega',
                            'text-white': navigation.style === 'transparent' || navigation.style === 'dark'
                        }"
                    >
                        {{ navigation.logoText || siteName }}
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <template v-for="(navItem, idx) in navigation.navItems" :key="navItem.id || idx">
                        <a 
                            href="javascript:void(0)"
                            @click="handleNavClick($event, navItem)"
                            class="text-sm transition-colors"
                            :class="{
                                'text-gray-600 hover:text-blue-600': !navigation.style || navigation.style === 'default' || navigation.style === 'floating' || navigation.style === 'mega',
                                'text-white/90 hover:text-white': navigation.style === 'transparent' || navigation.style === 'dark'
                            }"
                        >
                            {{ navItem.label }}
                            <span v-if="navigation.style === 'mega'" class="text-xs ml-0.5">▼</span>
                        </a>
                    </template>
                    <span v-if="navigation.navItems.length === 0" class="text-sm text-gray-400 italic">No nav items</span>
                    
                    <!-- Auth Buttons -->
                    <template v-if="navigation.showAuthButtons">
                        <span 
                            v-if="navigation.showLoginButton !== false"
                            class="text-sm cursor-pointer"
                            :class="[
                                navigation.loginStyle === 'solid' ? 'px-3 py-1.5 bg-gray-100 text-gray-900 rounded-lg' :
                                navigation.loginStyle === 'outline' ? 'px-3 py-1.5 border border-gray-300 text-gray-700 rounded-lg' :
                                {
                                    'text-gray-600 hover:text-gray-900': !navigation.style || navigation.style === 'default' || navigation.style === 'floating' || navigation.style === 'mega',
                                    'text-white/90 hover:text-white': navigation.style === 'transparent' || navigation.style === 'dark'
                                }
                            ]"
                        >
                            {{ navigation.loginText || 'Login' }}
                        </span>
                        <span 
                            v-if="navigation.showRegisterButton !== false"
                            class="text-sm cursor-pointer"
                            :class="[
                                navigation.registerStyle === 'outline' ? 'px-3 py-1.5 border border-blue-600 text-blue-600 rounded-lg' :
                                navigation.registerStyle === 'link' ? 'text-blue-600 hover:text-blue-700' :
                                'px-3 py-1.5 bg-blue-600 text-white rounded-lg'
                            ]"
                        >
                            {{ navigation.registerText || 'Sign Up' }}
                        </span>
                    </template>
                    
                    <button 
                        v-if="navigation.showCta" 
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        :class="{
                            'bg-blue-600 text-white hover:bg-blue-700': !navigation.style || navigation.style === 'default' || navigation.style === 'floating' || navigation.style === 'mega',
                            'bg-white text-gray-900 hover:bg-gray-100': navigation.style === 'transparent' || navigation.style === 'dark'
                        }"
                    >
                        {{ navigation.ctaText || 'Contact Us' }}
                    </button>
                </div>
            </div>

            <!-- Centered Style -->
            <div v-else-if="navigation.style === 'centered'" class="flex flex-col items-center gap-3">
                <div class="flex items-center gap-2" @click="emit('click')">
                    <img v-if="navigation.logo" :src="navigation.logo" class="h-10 object-contain cursor-pointer" alt="Logo" />
                    <span v-else class="font-bold text-xl text-gray-900 cursor-pointer">{{ navigation.logoText || siteName }}</span>
                </div>
                <div class="flex items-center gap-6">
                    <template v-for="(navItem, idx) in navigation.navItems" :key="navItem.id || idx">
                        <a 
                            href="javascript:void(0)" 
                            @click="handleNavClick($event, navItem)"
                            class="text-sm text-gray-600 hover:text-blue-600 transition-colors"
                        >
                            {{ navItem.label }}
                        </a>
                    </template>
                    <!-- Auth Buttons -->
                    <template v-if="navigation.showAuthButtons">
                        <span 
                            v-if="navigation.showLoginButton !== false"
                            class="text-sm cursor-pointer"
                            :class="[
                                navigation.loginStyle === 'solid' ? 'px-3 py-1.5 bg-gray-100 text-gray-900 rounded-lg' :
                                navigation.loginStyle === 'outline' ? 'px-3 py-1.5 border border-gray-300 text-gray-700 rounded-lg' :
                                'text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            {{ navigation.loginText || 'Login' }}
                        </span>
                        <span 
                            v-if="navigation.showRegisterButton !== false"
                            class="text-sm cursor-pointer"
                            :class="[
                                navigation.registerStyle === 'outline' ? 'px-3 py-1.5 border border-blue-600 text-blue-600 rounded-lg' :
                                navigation.registerStyle === 'link' ? 'text-blue-600 hover:text-blue-700' :
                                'px-3 py-1.5 bg-blue-600 text-white rounded-lg'
                            ]"
                        >
                            {{ navigation.registerText || 'Sign Up' }}
                        </span>
                    </template>
                    <button v-if="navigation.showCta" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg">
                        {{ navigation.ctaText || 'Contact Us' }}
                    </button>
                </div>
            </div>

            <!-- Split Style (logo center, links both sides) -->
            <div v-else-if="navigation.style === 'split'" class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <template v-for="(navItem, idx) in navigation.navItems.slice(0, Math.ceil(navigation.navItems.length / 2))" :key="navItem.id || idx">
                        <a 
                            href="javascript:void(0)" 
                            @click="handleNavClick($event, navItem)"
                            class="text-sm text-gray-600 hover:text-blue-600 transition-colors"
                        >
                            {{ navItem.label }}
                        </a>
                    </template>
                </div>
                <div class="flex items-center gap-2">
                    <img v-if="navigation.logo" :src="navigation.logo" class="h-10 object-contain" alt="Logo" />
                    <span v-else class="font-bold text-xl text-gray-900">{{ navigation.logoText || siteName }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <template v-for="(navItem, idx) in navigation.navItems.slice(Math.ceil(navigation.navItems.length / 2))" :key="navItem.id || idx">
                        <a 
                            href="javascript:void(0)" 
                            @click="handleNavClick($event, navItem)"
                            class="text-sm text-gray-600 hover:text-blue-600 transition-colors"
                        >
                            {{ navItem.label }}
                        </a>
                    </template>
                    <!-- Auth Buttons -->
                    <template v-if="navigation.showAuthButtons">
                        <span 
                            v-if="navigation.showLoginButton !== false"
                            class="text-sm cursor-pointer"
                            :class="[
                                navigation.loginStyle === 'solid' ? 'px-3 py-1.5 bg-gray-100 text-gray-900 rounded-lg' :
                                navigation.loginStyle === 'outline' ? 'px-3 py-1.5 border border-gray-300 text-gray-700 rounded-lg' :
                                'text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            {{ navigation.loginText || 'Login' }}
                        </span>
                        <span 
                            v-if="navigation.showRegisterButton !== false"
                            class="text-sm cursor-pointer"
                            :class="[
                                navigation.registerStyle === 'outline' ? 'px-3 py-1.5 border border-blue-600 text-blue-600 rounded-lg' :
                                navigation.registerStyle === 'link' ? 'text-blue-600 hover:text-blue-700' :
                                'px-3 py-1.5 bg-blue-600 text-white rounded-lg'
                            ]"
                        >
                            {{ navigation.registerText || 'Sign Up' }}
                        </span>
                    </template>
                    <button v-if="navigation.showCta" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg">
                        {{ navigation.ctaText || 'Contact Us' }}
                    </button>
                </div>
            </div>

            <!-- Sidebar Style (hamburger menu) -->
            <div v-else-if="navigation.style === 'sidebar'" class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button class="p-2 hover:bg-white/10 rounded-lg">
                        <Bars3Icon class="w-6 h-6 text-white" aria-hidden="true" />
                    </button>
                    <img v-if="navigation.logo" :src="navigation.logo" class="h-8 object-contain" alt="Logo" />
                    <span v-else class="font-bold text-lg text-white">{{ navigation.logoText || siteName }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Auth Buttons -->
                    <template v-if="navigation.showAuthButtons">
                        <span 
                            v-if="navigation.showLoginButton !== false"
                            class="text-sm text-white/90 hover:text-white cursor-pointer"
                        >
                            {{ navigation.loginText || 'Login' }}
                        </span>
                        <span 
                            v-if="navigation.showRegisterButton !== false"
                            class="text-sm px-3 py-1.5 bg-white text-gray-900 rounded-lg cursor-pointer"
                        >
                            {{ navigation.registerText || 'Sign Up' }}
                        </span>
                    </template>
                    <button v-if="navigation.showCta" class="px-4 py-2 bg-white text-gray-900 text-sm font-medium rounded-lg">
                        {{ navigation.ctaText || 'Contact Us' }}
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Hover overlay - positioned to not block navigation links -->
        <div class="absolute top-2 left-2 z-10 pointer-events-none">
            <span class="opacity-0 group-hover:opacity-100 text-xs bg-blue-600 text-white px-2 py-1 rounded transition-opacity shadow-lg">
                Click to edit navigation
            </span>
        </div>
    </div>
</template>
