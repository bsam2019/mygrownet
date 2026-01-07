<script setup lang="ts">
import { computed } from 'vue';
import { BoltIcon } from '@heroicons/vue/24/outline';
import type { Section } from '../types';

const props = defineProps<{
    sections: Section[];
    darkMode?: boolean;
}>();

// Estimate page weight and load time based on content
const speedAnalysis = computed(() => {
    let estimatedKB = 50; // Base HTML/CSS/JS
    let imageCount = 0;
    let heavySections = 0;
    
    props.sections.forEach(section => {
        // Base weight per section type
        const sectionWeights: Record<string, number> = {
            hero: 150,      // Usually has large background image
            gallery: 200,   // Multiple images
            testimonials: 80,
            products: 120,  // Product images
            team: 100,      // Team photos
            video: 50,      // Just embed code, video loads separately
            blog: 80,
            about: 100,     // Often has image
            services: 40,
            features: 40,
            pricing: 30,
            contact: 20,
            faq: 20,
            cta: 30,
            stats: 20,
            text: 10,
            divider: 5,
            map: 30,
            'page-header': 80,
        };
        
        estimatedKB += sectionWeights[section.type] || 30;
        
        // Count images in content
        const content = section.content || {};
        if (content.backgroundImage || content.image) imageCount++;
        if (content.images?.length) imageCount += content.images.length;
        if (content.items?.length) {
            content.items.forEach((item: any) => {
                if (item.image) imageCount++;
            });
        }
        
        // Heavy sections
        if (['hero', 'gallery', 'products', 'video'].includes(section.type)) {
            heavySections++;
        }
    });
    
    // Estimate load time (assuming 3G connection ~1.5 Mbps)
    // 1.5 Mbps = 187.5 KB/s
    const loadTimeSeconds = estimatedKB / 150; // Slightly pessimistic
    
    // Calculate score (0-100)
    let score = 100;
    score -= Math.min(30, props.sections.length * 2); // Penalty for many sections
    score -= Math.min(25, imageCount * 3);            // Penalty for images
    score -= Math.min(20, heavySections * 5);         // Penalty for heavy sections
    score -= Math.min(15, Math.max(0, estimatedKB - 500) / 50); // Penalty for large pages
    score = Math.max(0, Math.min(100, Math.round(score)));
    
    // Rating
    let rating: 'fast' | 'moderate' | 'slow';
    let color: string;
    let label: string;
    
    if (score >= 70) {
        rating = 'fast';
        color = 'text-green-500';
        label = 'Fast';
    } else if (score >= 40) {
        rating = 'moderate';
        color = 'text-yellow-500';
        label = 'Moderate';
    } else {
        rating = 'slow';
        color = 'text-red-500';
        label = 'Slow';
    }
    
    return {
        score,
        rating,
        color,
        label,
        estimatedKB: Math.round(estimatedKB),
        loadTime: loadTimeSeconds.toFixed(1),
        imageCount,
        sectionCount: props.sections.length,
        tips: generateTips(score, imageCount, heavySections, props.sections.length),
    };
});

function generateTips(score: number, images: number, heavy: number, sections: number): string[] {
    const tips: string[] = [];
    
    if (images > 5) {
        tips.push('Consider reducing the number of images');
    }
    if (heavy > 3) {
        tips.push('Too many heavy sections (gallery, video)');
    }
    if (sections > 10) {
        tips.push('Consider splitting into multiple pages');
    }
    if (score >= 70) {
        tips.push('Your page is well optimized!');
    }
    
    return tips.slice(0, 2);
}
</script>

<template>
    <div class="relative group">
        <!-- Speed Badge -->
        <div 
            :class="[
                'flex items-center gap-1.5 px-2 py-1 rounded-lg cursor-help transition-colors',
                darkMode ? 'hover:bg-gray-800' : 'hover:bg-gray-100'
            ]"
            :title="`Page Speed: ${speedAnalysis.label}`"
        >
            <BoltIcon :class="['w-4 h-4', speedAnalysis.color]" aria-hidden="true" />
            <span :class="['text-xs font-medium', speedAnalysis.color]">
                {{ speedAnalysis.score }}
            </span>
        </div>
        
        <!-- Tooltip/Dropdown -->
        <div :class="[
            'absolute right-0 top-full mt-2 w-64 rounded-xl shadow-xl border p-4 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50',
            darkMode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'
        ]">
            <!-- Header -->
            <div class="flex items-center justify-between mb-3">
                <span :class="['text-sm font-semibold', darkMode ? 'text-white' : 'text-gray-900']">
                    Page Speed
                </span>
                <span :class="['text-sm font-bold', speedAnalysis.color]">
                    {{ speedAnalysis.label }}
                </span>
            </div>
            
            <!-- Score Bar -->
            <div class="mb-4">
                <div :class="['h-2 rounded-full overflow-hidden', darkMode ? 'bg-gray-700' : 'bg-gray-200']">
                    <div 
                        class="h-full rounded-full transition-all duration-500"
                        :class="{
                            'bg-green-500': speedAnalysis.rating === 'fast',
                            'bg-yellow-500': speedAnalysis.rating === 'moderate',
                            'bg-red-500': speedAnalysis.rating === 'slow',
                        }"
                        :style="{ width: speedAnalysis.score + '%' }"
                    ></div>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-2 gap-2 mb-3">
                <div :class="['text-center p-2 rounded-lg', darkMode ? 'bg-gray-700' : 'bg-gray-50']">
                    <div :class="['text-lg font-bold', darkMode ? 'text-white' : 'text-gray-900']">
                        ~{{ speedAnalysis.loadTime }}s
                    </div>
                    <div :class="['text-xs', darkMode ? 'text-gray-400' : 'text-gray-500']">
                        Est. Load Time
                    </div>
                </div>
                <div :class="['text-center p-2 rounded-lg', darkMode ? 'bg-gray-700' : 'bg-gray-50']">
                    <div :class="['text-lg font-bold', darkMode ? 'text-white' : 'text-gray-900']">
                        ~{{ speedAnalysis.estimatedKB }}KB
                    </div>
                    <div :class="['text-xs', darkMode ? 'text-gray-400' : 'text-gray-500']">
                        Page Size
                    </div>
                </div>
            </div>
            
            <!-- Details -->
            <div :class="['text-xs space-y-1 mb-3', darkMode ? 'text-gray-400' : 'text-gray-500']">
                <div class="flex justify-between">
                    <span>Sections</span>
                    <span :class="darkMode ? 'text-gray-300' : 'text-gray-700'">{{ speedAnalysis.sectionCount }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Images</span>
                    <span :class="darkMode ? 'text-gray-300' : 'text-gray-700'">{{ speedAnalysis.imageCount }}</span>
                </div>
            </div>
            
            <!-- Tips -->
            <div v-if="speedAnalysis.tips.length" :class="['pt-3 border-t', darkMode ? 'border-gray-700' : 'border-gray-200']">
                <div :class="['text-xs font-medium mb-1', darkMode ? 'text-gray-300' : 'text-gray-700']">
                    Tips
                </div>
                <ul :class="['text-xs space-y-1', darkMode ? 'text-gray-400' : 'text-gray-500']">
                    <li v-for="tip in speedAnalysis.tips" :key="tip" class="flex items-start gap-1">
                        <span>•</span>
                        <span>{{ tip }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
