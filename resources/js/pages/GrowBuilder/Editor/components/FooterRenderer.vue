<script setup lang="ts">
/**
 * Footer Renderer Component
 * Renders the site-wide footer preview in the editor canvas
 */

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
    layout: 'columns' | 'centered' | 'split' | 'minimal' | 'stacked' | 'newsletter' | 'social' | 'contact';
}

const props = defineProps<{
    footer: SiteFooter;
    siteName: string;
    logoText: string;
    isEditing: boolean;
}>();

const emit = defineEmits<{
    (e: 'click'): void;
}>();
</script>

<template>
    <div 
        class="relative group cursor-pointer"
        :class="isEditing ? 'ring-2 ring-blue-500 ring-inset' : 'hover:ring-1 hover:ring-blue-300 hover:ring-inset'"
        @click="emit('click')"
    >
        <!-- Footer Edit Indicator -->
        <div v-if="isEditing" class="absolute top-2 right-2 z-10 flex items-center gap-1 bg-white rounded-lg shadow-lg border border-gray-200 p-1">
            <span class="text-xs text-blue-600 font-medium px-2">Editing Footer</span>
        </div>
        
        <div 
            class="px-8 transition-all"
            :class="{
                'py-12': !footer.layout || footer.layout === 'columns' || footer.layout === 'stacked' || footer.layout === 'split' || footer.layout === 'social',
                'py-10': footer.layout === 'newsletter' || footer.layout === 'contact',
                'py-8': footer.layout === 'centered',
                'py-4': footer.layout === 'minimal'
            }"
            :style="{ backgroundColor: footer.backgroundColor, color: footer.textColor }"
        >
            <div class="max-w-4xl mx-auto">
                <!-- Columns Layout (default) -->
                <template v-if="!footer.layout || footer.layout === 'columns'">
                    <div v-if="footer.columns.length > 0" class="grid gap-8 mb-8" :style="{ gridTemplateColumns: `repeat(${Math.min(footer.columns.length + 1, 4)}, 1fr)` }">
                        <div>
                            <img v-if="footer.logo" :src="footer.logo" class="h-10 object-contain mb-4" alt="Logo" />
                            <h3 v-else class="font-bold text-lg mb-4">{{ logoText || siteName }}</h3>
                            <p class="text-sm opacity-80">Your trusted partner for growth and success.</p>
                        </div>
                        <div v-for="column in footer.columns" :key="column.id">
                            <h4 class="font-semibold mb-3">{{ column.title }}</h4>
                            <ul class="space-y-2">
                                <li v-for="link in column.links" :key="link.id">
                                    <span class="text-sm opacity-80 hover:opacity-100 cursor-pointer">{{ link.label }}</span>
                                </li>
                                <li v-if="column.links.length === 0" class="text-sm opacity-50 italic">No links yet</li>
                            </ul>
                        </div>
                    </div>
                    <div v-if="footer.showSocialLinks && footer.socialLinks.length > 0" class="flex items-center gap-3 mb-6">
                        <span v-for="social in footer.socialLinks" :key="social.id" class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 cursor-pointer">
                            <span class="text-xs uppercase">{{ social.platform.charAt(0) }}</span>
                        </span>
                    </div>
                    <div class="pt-6 border-t border-white/20 text-center">
                        <p class="text-sm opacity-60">{{ footer.copyrightText }}</p>
                    </div>
                </template>

                <!-- Centered Layout -->
                <template v-else-if="footer.layout === 'centered'">
                    <div class="text-center">
                        <img v-if="footer.logo" :src="footer.logo" class="h-10 object-contain mx-auto mb-4" alt="Logo" />
                        <h3 v-else class="font-bold text-lg mb-4">{{ logoText || siteName }}</h3>
                        <div v-if="footer.showSocialLinks && footer.socialLinks.length > 0" class="flex items-center justify-center gap-3 mb-4">
                            <span v-for="social in footer.socialLinks" :key="social.id" class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 cursor-pointer">
                                <span class="text-xs uppercase">{{ social.platform.charAt(0) }}</span>
                            </span>
                        </div>
                        <p class="text-sm opacity-60">{{ footer.copyrightText }}</p>
                    </div>
                </template>

                <!-- Split Layout -->
                <template v-else-if="footer.layout === 'split'">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <img v-if="footer.logo" :src="footer.logo" class="h-10 object-contain" alt="Logo" />
                            <span v-else class="font-bold text-lg">{{ logoText || siteName }}</span>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-6">
                            <template v-for="column in footer.columns" :key="column.id">
                                <span v-for="link in column.links" :key="link.id" class="text-sm opacity-80 hover:opacity-100 cursor-pointer">{{ link.label }}</span>
                            </template>
                        </div>
                        <div v-if="footer.showSocialLinks && footer.socialLinks.length > 0" class="flex items-center gap-2">
                            <span v-for="social in footer.socialLinks" :key="social.id" class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 cursor-pointer">
                                <span class="text-xs uppercase">{{ social.platform.charAt(0) }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-white/20 text-center">
                        <p class="text-sm opacity-60">{{ footer.copyrightText }}</p>
                    </div>
                </template>

                <!-- Minimal Layout -->
                <template v-else-if="footer.layout === 'minimal'">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <p class="text-sm opacity-60">{{ footer.copyrightText }}</p>
                        <div class="flex items-center gap-4">
                            <template v-for="column in footer.columns" :key="column.id">
                                <span v-for="link in column.links" :key="link.id" class="text-sm opacity-80 hover:opacity-100 cursor-pointer">{{ link.label }}</span>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Stacked Layout -->
                <template v-else-if="footer.layout === 'stacked'">
                    <div class="text-center">
                        <img v-if="footer.logo" :src="footer.logo" class="h-12 object-contain mx-auto mb-6" alt="Logo" />
                        <h3 v-else class="font-bold text-2xl mb-6">{{ logoText || siteName }}</h3>
                        <div class="flex flex-wrap items-center justify-center gap-6 mb-6">
                            <template v-for="column in footer.columns" :key="column.id">
                                <span v-for="link in column.links" :key="link.id" class="text-sm opacity-80 hover:opacity-100 cursor-pointer">{{ link.label }}</span>
                            </template>
                        </div>
                        <div v-if="footer.showSocialLinks && footer.socialLinks.length > 0" class="flex items-center justify-center gap-3 mb-6">
                            <span v-for="social in footer.socialLinks" :key="social.id" class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 cursor-pointer">
                                <span class="text-sm uppercase">{{ social.platform.charAt(0) }}</span>
                            </span>
                        </div>
                        <div class="pt-6 border-t border-white/20">
                            <p class="text-sm opacity-60">{{ footer.copyrightText }}</p>
                        </div>
                    </div>
                </template>

                <!-- Newsletter Layout -->
                <template v-else-if="footer.layout === 'newsletter'">
                    <div class="grid md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <img v-if="footer.logo" :src="footer.logo" class="h-10 object-contain mb-4" alt="Logo" />
                            <h3 v-else class="font-bold text-lg mb-4">{{ logoText || siteName }}</h3>
                            <p class="text-sm opacity-80 mb-4">Stay updated with our latest news and offers.</p>
                            <div v-if="footer.showSocialLinks && footer.socialLinks.length > 0" class="flex items-center gap-2">
                                <span v-for="social in footer.socialLinks" :key="social.id" class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 cursor-pointer">
                                    <span class="text-xs uppercase">{{ social.platform.charAt(0) }}</span>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-3">{{ footer.newsletterTitle || 'Subscribe to our newsletter' }}</h4>
                            <div class="flex gap-2">
                                <input type="email" placeholder="Enter your email" class="flex-1 px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-sm placeholder-white/50" />
                                <button class="px-4 py-2 bg-white text-gray-900 rounded-lg text-sm font-medium">Subscribe</button>
                            </div>
                        </div>
                    </div>
                    <div class="pt-6 border-t border-white/20 flex flex-col md:flex-row items-center justify-between gap-4">
                        <p class="text-sm opacity-60">{{ footer.copyrightText }}</p>
                        <div class="flex flex-wrap items-center gap-4">
                            <template v-for="column in footer.columns" :key="column.id">
                                <span v-for="link in column.links" :key="link.id" class="text-sm opacity-80 hover:opacity-100 cursor-pointer">{{ link.label }}</span>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Social Focus Layout -->
                <template v-else-if="footer.layout === 'social'">
                    <div class="text-center">
                        <img v-if="footer.logo" :src="footer.logo" class="h-12 object-contain mx-auto mb-4" alt="Logo" />
                        <h3 v-else class="font-bold text-2xl mb-2">{{ logoText || siteName }}</h3>
                        <p class="text-sm opacity-80 mb-8 max-w-md mx-auto">Connect with us on social media</p>
                        <div v-if="footer.showSocialLinks && footer.socialLinks.length > 0" class="flex items-center justify-center gap-6 mb-8">
                            <span v-for="social in footer.socialLinks" :key="social.id" class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center hover:bg-white/20 hover:scale-110 cursor-pointer transition-all">
                                <span class="text-lg font-bold uppercase">{{ social.platform.charAt(0) }}</span>
                            </span>
                        </div>
                        <div v-else class="flex items-center justify-center gap-6 mb-8">
                            <span class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center">
                                <span class="text-lg font-bold">F</span>
                            </span>
                            <span class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center">
                                <span class="text-lg font-bold">T</span>
                            </span>
                            <span class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center">
                                <span class="text-lg font-bold">I</span>
                            </span>
                            <span class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center">
                                <span class="text-lg font-bold">L</span>
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-6 mb-4">
                            <template v-for="column in footer.columns" :key="column.id">
                                <span v-for="link in column.links" :key="link.id" class="text-sm opacity-70 hover:opacity-100 cursor-pointer">{{ link.label }}</span>
                            </template>
                        </div>
                        <p class="text-sm opacity-50">{{ footer.copyrightText }}</p>
                    </div>
                </template>

                <!-- Contact Bar Layout -->
                <template v-else-if="footer.layout === 'contact'">
                    <div class="flex flex-wrap items-center justify-center gap-8 mb-6 pb-6 border-b border-white/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center">
                                <span class="text-sm">📞</span>
                            </div>
                            <div>
                                <p class="text-xs opacity-60 uppercase tracking-wider">Call Us</p>
                                <p class="text-sm font-medium">+260 97X XXX XXX</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center">
                                <span class="text-sm">✉️</span>
                            </div>
                            <div>
                                <p class="text-xs opacity-60 uppercase tracking-wider">Email</p>
                                <p class="text-sm font-medium">info@example.com</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center">
                                <span class="text-sm">📍</span>
                            </div>
                            <div>
                                <p class="text-xs opacity-60 uppercase tracking-wider">Location</p>
                                <p class="text-sm font-medium">Lusaka, Zambia</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <img v-if="footer.logo" :src="footer.logo" class="h-8 object-contain" alt="Logo" />
                            <span v-else class="font-bold">{{ logoText || siteName }}</span>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-4">
                            <template v-for="column in footer.columns" :key="column.id">
                                <span v-for="link in column.links" :key="link.id" class="text-sm opacity-70 hover:opacity-100 cursor-pointer">{{ link.label }}</span>
                            </template>
                        </div>
                        <div v-if="footer.showSocialLinks && footer.socialLinks.length > 0" class="flex items-center gap-2">
                            <span v-for="social in footer.socialLinks" :key="social.id" class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 cursor-pointer">
                                <span class="text-xs uppercase">{{ social.platform.charAt(0) }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/10 text-center">
                        <p class="text-xs opacity-50">{{ footer.copyrightText }}</p>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Hover overlay -->
        <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/5 transition-colors">
            <span class="opacity-0 group-hover:opacity-100 text-xs bg-blue-600 text-white px-2 py-1 rounded transition-opacity">
                Click to edit footer
            </span>
        </div>
    </div>
</template>
