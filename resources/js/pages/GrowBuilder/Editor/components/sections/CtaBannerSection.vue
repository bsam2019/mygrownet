<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    content: {
        layout?: string;
        title?: string;
        subtitle?: string;
        image?: string;
        buttonText?: string;
        buttonLink?: string;
        secondaryButtonText?: string;
        secondaryButtonLink?: string;
    };
    style?: {
        backgroundColor?: string;
        textColor?: string;
        gradient?: boolean;
    };
}

const props = withDefaults(defineProps<Props>(), {
    content: () => ({
        layout: 'centered',
        title: 'Ready to Get Started?',
        subtitle: 'Join thousands of satisfied customers and transform your business today',
        buttonText: 'Get Started',
        buttonLink: '#contact',
        secondaryButtonText: 'Learn More',
        secondaryButtonLink: '#about',
    }),
    style: () => ({
        backgroundColor: '#2563eb',
        textColor: '#ffffff',
        gradient: false,
    }),
});

const backgroundStyle = computed(() => {
    if (props.style?.gradient) {
        return {
            background: `linear-gradient(135deg, ${props.style.backgroundColor} 0%, ${adjustColor(props.style.backgroundColor, -30)} 100%)`,
            color: props.style.textColor,
        };
    }
    return {
        backgroundColor: props.style?.backgroundColor,
        color: props.style?.textColor,
    };
});

// Helper to darken color for gradient
function adjustColor(color: string, amount: number): string {
    const hex = color.replace('#', '');
    const num = parseInt(hex, 16);
    const r = Math.max(0, Math.min(255, (num >> 16) + amount));
    const g = Math.max(0, Math.min(255, ((num >> 8) & 0x00FF) + amount));
    const b = Math.max(0, Math.min(255, (num & 0x0000FF) + amount));
    return `#${((r << 16) | (g << 8) | b).toString(16).padStart(6, '0')}`;
}
</script>

<template>
    <section 
        class="py-16 px-4"
        :style="backgroundStyle"
        data-aos="fade-up"
    >
        <!-- Centered Layout -->
        <div v-if="content.layout === 'centered'" class="max-w-4xl mx-auto text-center">
            <h2 
                class="text-3xl md:text-5xl font-bold mb-6"
                data-aos="fade-up"
            >
                {{ content.title }}
            </h2>
            <p 
                v-if="content.subtitle"
                class="text-lg md:text-xl mb-8 opacity-90"
                data-aos="fade-up"
                data-aos-delay="100"
            >
                {{ content.subtitle }}
            </p>
            <div 
                class="flex flex-col sm:flex-row gap-4 justify-center"
                data-aos="fade-up"
                data-aos-delay="200"
            >
                <a
                    v-if="content.buttonText"
                    :href="content.buttonLink"
                    class="inline-block px-8 py-4 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg"
                >
                    {{ content.buttonText }}
                </a>
                <a
                    v-if="content.secondaryButtonText"
                    :href="content.secondaryButtonLink"
                    class="inline-block px-8 py-4 border-2 font-semibold rounded-lg hover:bg-white/10 transition-all"
                    :style="{ borderColor: style?.textColor }"
                >
                    {{ content.secondaryButtonText }}
                </a>
            </div>
        </div>

        <!-- Split Layout -->
        <div v-else-if="content.layout === 'split'" class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div data-aos="fade-right">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">
                        {{ content.title }}
                    </h2>
                    <p v-if="content.subtitle" class="text-lg mb-6 opacity-90">
                        {{ content.subtitle }}
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 md:justify-end" data-aos="fade-left">
                    <a
                        v-if="content.buttonText"
                        :href="content.buttonLink"
                        class="inline-block px-8 py-4 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg text-center"
                    >
                        {{ content.buttonText }}
                    </a>
                    <a
                        v-if="content.secondaryButtonText"
                        :href="content.secondaryButtonLink"
                        class="inline-block px-8 py-4 border-2 font-semibold rounded-lg hover:bg-white/10 transition-all text-center"
                        :style="{ borderColor: style?.textColor }"
                    >
                        {{ content.secondaryButtonText }}
                    </a>
                </div>
            </div>
        </div>

        <!-- With Image Layout -->
        <div v-else class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-right">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">
                        {{ content.title }}
                    </h2>
                    <p v-if="content.subtitle" class="text-lg mb-6 opacity-90">
                        {{ content.subtitle }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a
                            v-if="content.buttonText"
                            :href="content.buttonLink"
                            class="inline-block px-8 py-4 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg text-center"
                        >
                            {{ content.buttonText }}
                        </a>
                        <a
                            v-if="content.secondaryButtonText"
                            :href="content.secondaryButtonLink"
                            class="inline-block px-8 py-4 border-2 font-semibold rounded-lg hover:bg-white/10 transition-all text-center"
                            :style="{ borderColor: style?.textColor }"
                        >
                            {{ content.secondaryButtonText }}
                        </a>
                    </div>
                </div>
                <div data-aos="fade-left">
                    <img
                        v-if="content.image"
                        :src="content.image"
                        :alt="content.title"
                        class="rounded-lg shadow-2xl w-full h-auto"
                    />
                    <div
                        v-else
                        class="bg-white/10 rounded-lg h-64 flex items-center justify-center"
                    >
                        <span class="text-white/50">Add an image</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
