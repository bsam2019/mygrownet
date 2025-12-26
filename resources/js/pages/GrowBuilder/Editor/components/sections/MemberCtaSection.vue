<script setup lang="ts">
/**
 * Member CTA Section Preview Component
 * Displays a membership signup call-to-action in the editor
 */
import { CheckIcon, UserPlusIcon } from '@heroicons/vue/24/outline';

defineProps<{
    content: {
        title: string;
        subtitle?: string;
        description?: string;
        benefits?: string[];
        loginText?: string;
        registerText?: string;
        registerButtonStyle?: 'solid' | 'outline';
        showLoginLink?: boolean;
        backgroundColor?: string;
        textColor?: string;
    };
    style?: {
        backgroundColor?: string;
    };
}>();
</script>

<template>
    <div 
        class="py-16 px-6"
        :style="{ 
            backgroundColor: style?.backgroundColor || content.backgroundColor || '#1e40af',
            color: content.textColor || '#ffffff'
        }"
    >
        <div class="max-w-4xl mx-auto text-center">
            <!-- Icon -->
            <div class="w-16 h-16 mx-auto mb-6 bg-white/20 rounded-full flex items-center justify-center">
                <UserPlusIcon class="w-8 h-8" aria-hidden="true" />
            </div>
            
            <!-- Title -->
            <h2 class="text-3xl font-bold mb-3">{{ content.title || 'Join Our Community' }}</h2>
            
            <!-- Subtitle -->
            <p v-if="content.subtitle" class="text-xl opacity-90 mb-4">{{ content.subtitle }}</p>
            
            <!-- Description -->
            <p v-if="content.description" class="text-lg opacity-80 mb-8 max-w-2xl mx-auto">
                {{ content.description }}
            </p>
            
            <!-- Benefits -->
            <div v-if="content.benefits?.length" class="flex flex-wrap justify-center gap-4 mb-8">
                <div 
                    v-for="(benefit, idx) in content.benefits" 
                    :key="idx"
                    class="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-full"
                >
                    <CheckIcon class="w-4 h-4" aria-hidden="true" />
                    <span class="text-sm">{{ benefit }}</span>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <!-- Register Button -->
                <button 
                    :class="[
                        'px-8 py-3 font-semibold rounded-lg transition-colors',
                        content.registerButtonStyle === 'outline' 
                            ? 'border-2 border-white hover:bg-white/10' 
                            : 'bg-white text-blue-600 hover:bg-gray-100'
                    ]"
                >
                    {{ content.registerText || 'Sign Up Now' }}
                </button>
                
                <!-- Login Link -->
                <a 
                    v-if="content.showLoginLink !== false"
                    href="#"
                    class="text-sm opacity-80 hover:opacity-100 underline"
                >
                    {{ content.loginText || 'Already a member? Login' }}
                </a>
            </div>
        </div>
    </div>
</template>
