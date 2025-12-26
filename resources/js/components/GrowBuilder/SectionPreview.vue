<script setup lang="ts">
import { computed } from 'vue';
import {
    SparklesIcon,
    PhotoIcon,
    CheckBadgeIcon,
    StarIcon,
    ShoppingBagIcon,
} from '@heroicons/vue/24/outline';

interface Section {
    type: string;
    content: Record<string, any>;
    style?: Record<string, any>;
}

const props = defineProps<{
    section: Section;
    isSelected?: boolean;
    compact?: boolean;
}>();

const bgColor = computed(() => props.section.style?.backgroundColor || '#ffffff');
const textColor = computed(() => props.section.style?.textColor || '#111827');
const paddingTop = computed(() => (props.section.style?.paddingTop || 0) + 'px');
const paddingBottom = computed(() => (props.section.style?.paddingBottom || 0) + 'px');
</script>

<template>
    <div
        :style="{
            backgroundColor: bgColor,
            color: textColor,
            paddingTop: paddingTop,
            paddingBottom: paddingBottom,
        }"
        class="transition-all"
    >
        <!-- Hero Section -->
        <div v-if="section.type === 'hero'" class="py-20 px-6 text-center relative">
            <div v-if="section.content.image" class="absolute inset-0 opacity-20">
                <img :src="section.content.image" class="w-full h-full object-cover" alt="" />
            </div>
            <div class="relative">
                <h1 class="text-3xl md:text-5xl font-bold mb-4">{{ section.content.title || 'Welcome' }}</h1>
                <p class="text-lg md:text-xl opacity-80 mb-8 max-w-2xl mx-auto">{{ section.content.subtitle || 'Your subtitle here' }}</p>
                <button v-if="section.content.buttonText" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium shadow-md hover:bg-blue-700 transition-colors">
                    {{ section.content.buttonText }}
                </button>
            </div>
        </div>

        <!-- About Section -->
        <div v-else-if="section.type === 'about'" class="py-16 px-6">
            <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold mb-6">{{ section.content.title || 'About Us' }}</h2>
                    <p class="text-lg opacity-80 leading-relaxed">{{ section.content.description || 'Tell your story here...' }}</p>
                </div>
                <div v-if="section.content.image" class="aspect-video bg-gray-100 rounded-2xl overflow-hidden shadow-lg">
                    <img :src="section.content.image" class="w-full h-full object-cover" alt="" />
                </div>
                <div v-else class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center">
                    <PhotoIcon class="w-12 h-12 text-gray-300" aria-hidden="true" />
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div v-else-if="section.type === 'services'" class="py-16 px-6">
            <h2 class="text-3xl font-bold text-center mb-12">{{ section.content.title || 'Our Services' }}</h2>
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div
                    v-for="(item, i) in (section.content.items || []).slice(0, 6)"
                    :key="i"
                    class="p-6 bg-white/50 rounded-2xl text-center shadow-sm hover:shadow-md transition-shadow"
                >
                    <div class="w-14 h-14 bg-blue-100 rounded-xl mx-auto mb-4 flex items-center justify-center">
                        <SparklesIcon class="w-7 h-7 text-blue-600" aria-hidden="true" />
                    </div>
                    <h3 class="font-semibold text-lg mb-2">{{ item.title || `Service ${i + 1}` }}</h3>
                    <p class="text-sm opacity-70">{{ item.description || 'Description' }}</p>
                </div>
            </div>
        </div>

        <!-- Gallery Section -->
        <div v-else-if="section.type === 'gallery'" class="py-16 px-6">
            <h2 class="text-3xl font-bold text-center mb-12">{{ section.content.title || 'Gallery' }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-w-5xl mx-auto">
                <div
                    v-for="(img, i) in (section.content.images || []).slice(0, 6)"
                    :key="i"
                    class="aspect-square bg-gray-100 rounded-xl overflow-hidden shadow-sm"
                >
                    <img v-if="img" :src="img" class="w-full h-full object-cover" alt="" />
                </div>
                <div
                    v-for="i in Math.max(0, 3 - (section.content.images?.length || 0))"
                    :key="'placeholder-' + i"
                    class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center"
                >
                    <PhotoIcon class="w-8 h-8 text-gray-300" aria-hidden="true" />
                </div>
            </div>
        </div>

        <!-- Testimonials Section -->
        <div v-else-if="section.type === 'testimonials'" class="py-16 px-6">
            <h2 class="text-3xl font-bold text-center mb-12">{{ section.content.title || 'Testimonials' }}</h2>
            <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                <div
                    v-for="(item, i) in (section.content.items || []).slice(0, 4)"
                    :key="i"
                    class="p-6 bg-white/50 rounded-2xl shadow-sm"
                >
                    <div class="flex gap-1 mb-4">
                        <StarIcon v-for="s in 5" :key="s" class="w-5 h-5 text-yellow-400 fill-current" aria-hidden="true" />
                    </div>
                    <p class="italic mb-4 opacity-80">"{{ item.text || 'Great service!' }}"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full"></div>
                        <div>
                            <p class="font-medium">{{ item.name || 'Customer' }}</p>
                            <p class="text-sm opacity-60">{{ item.role || 'Customer' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div v-else-if="section.type === 'contact'" class="py-16 px-6">
            <h2 class="text-3xl font-bold text-center mb-4">{{ section.content.title || 'Contact Us' }}</h2>
            <p class="text-center opacity-80 mb-12 max-w-xl mx-auto">{{ section.content.description || 'Get in touch' }}</p>
            <div v-if="section.content.showForm" class="max-w-md mx-auto space-y-4">
                <input type="text" placeholder="Your Name" class="w-full px-4 py-3 border border-gray-200 rounded-xl" disabled />
                <input type="email" placeholder="Your Email" class="w-full px-4 py-3 border border-gray-200 rounded-xl" disabled />
                <textarea placeholder="Your Message" rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl resize-none" disabled></textarea>
                <button class="w-full py-3 bg-blue-600 text-white rounded-xl font-medium">Send Message</button>
            </div>
        </div>

        <!-- CTA Section -->
        <div v-else-if="section.type === 'cta'" class="py-20 px-6 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">{{ section.content.title || 'Ready to Start?' }}</h2>
            <p class="text-lg opacity-80 mb-8 max-w-xl mx-auto">{{ section.content.description || 'Contact us today' }}</p>
            <button class="px-10 py-4 bg-blue-600 text-white rounded-xl font-medium shadow-lg hover:bg-blue-700 transition-colors">
                {{ section.content.buttonText || 'Get Started' }}
            </button>
        </div>

        <!-- Text Section -->
        <div v-else-if="section.type === 'text'" class="py-12 px-6">
            <div class="max-w-3xl mx-auto prose prose-lg" v-html="section.content.content || '<p>Enter your text here...</p>'"></div>
        </div>

        <!-- Features Section -->
        <div v-else-if="section.type === 'features'" class="py-16 px-6">
            <h2 class="text-3xl font-bold text-center mb-12">{{ section.content.title || 'Features' }}</h2>
            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <div
                    v-for="(item, i) in (section.content.items || []).slice(0, 6)"
                    :key="i"
                    class="flex gap-4"
                >
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <CheckBadgeIcon class="w-6 h-6 text-green-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-1">{{ item.title || `Feature ${i + 1}` }}</h3>
                        <p class="opacity-70">{{ item.description || 'Description' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Section -->
        <div v-else-if="section.type === 'pricing'" class="py-16 px-6">
            <h2 class="text-3xl font-bold text-center mb-12">{{ section.content.title || 'Pricing' }}</h2>
            <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div
                    v-for="(plan, i) in (section.content.plans || []).slice(0, 3)"
                    :key="i"
                    :class="[
                        'p-8 rounded-2xl text-center transition-all',
                        i === 1 ? 'bg-blue-600 text-white shadow-xl scale-105' : 'bg-white shadow-md'
                    ]"
                >
                    <h3 class="font-semibold text-xl mb-2">{{ plan.name || `Plan ${i + 1}` }}</h3>
                    <p :class="['text-4xl font-bold mb-6', i === 1 ? 'text-white' : 'text-blue-600']">{{ plan.price || 'K0' }}</p>
                    <ul class="text-sm space-y-3 mb-8">
                        <li v-for="(feature, j) in (plan.features || []).slice(0, 5)" :key="j" class="flex items-center gap-2 justify-center">
                            <CheckBadgeIcon :class="['w-5 h-5', i === 1 ? 'text-blue-200' : 'text-green-500']" aria-hidden="true" />
                            {{ feature }}
                        </li>
                    </ul>
                    <button :class="[
                        'w-full py-3 rounded-xl font-medium transition-colors',
                        i === 1 ? 'bg-white text-blue-600 hover:bg-blue-50' : 'bg-blue-600 text-white hover:bg-blue-700'
                    ]">
                        Choose Plan
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div v-else-if="section.type === 'products'" class="py-16 px-6">
            <h2 class="text-3xl font-bold text-center mb-12">{{ section.content.title || 'Our Products' }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div
                    v-for="(product, i) in (section.content.products || []).slice(0, 6)"
                    :key="i"
                    class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-shadow"
                >
                    <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                        <ShoppingBagIcon class="w-12 h-12 text-gray-300" aria-hidden="true" />
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium">{{ product.name || `Product ${i + 1}` }}</h3>
                        <p class="text-blue-600 font-semibold">K{{ ((product.price || 0) / 100).toFixed(2) }}</p>
                    </div>
                </div>
                <div
                    v-for="i in Math.max(0, 3 - (section.content.products?.length || 0))"
                    :key="'placeholder-' + i"
                    class="bg-white rounded-2xl shadow-sm overflow-hidden"
                >
                    <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                        <ShoppingBagIcon class="w-12 h-12 text-gray-300" aria-hidden="true" />
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-400">Product {{ i }}</h3>
                        <p class="text-gray-300 font-semibold">K0.00</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fallback -->
        <div v-else class="py-16 px-6 text-center bg-gray-50">
            <p class="text-gray-500 capitalize text-lg">{{ section.type }} Section</p>
            <p class="text-sm text-gray-400">Click to edit</p>
        </div>
    </div>
</template>

<style scoped>
.prose {
    max-width: none;
}

.prose p {
    margin-bottom: 1em;
}

.prose h1, .prose h2, .prose h3 {
    font-weight: 700;
    margin-bottom: 0.5em;
}
</style>
