<script setup lang="ts">
import { computed } from 'vue';

interface LogoItem {
    image: string;
    name: string;
    link?: string;
}

interface Props {
    content: {
        layout?: string;
        title?: string;
        subtitle?: string;
        textAlign?: string;
        grayscale?: boolean;
        items?: LogoItem[];
    };
    style?: {
        backgroundColor?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    content: () => ({
        layout: 'grid',
        title: 'Trusted by Leading Companies',
        subtitle: 'Join hundreds of businesses that trust us',
        textAlign: 'center',
        grayscale: true,
        items: [
            { image: '', name: 'Company 1', link: '' },
            { image: '', name: 'Company 2', link: '' },
            { image: '', name: 'Company 3', link: '' },
            { image: '', name: 'Company 4', link: '' },
            { image: '', name: 'Company 5', link: '' },
            { image: '', name: 'Company 6', link: '' },
        ],
    }),
    style: () => ({
        backgroundColor: '#f9fafb',
    }),
});

const textAlignClass = computed(() => {
    switch (props.content.textAlign) {
        case 'left':
            return 'text-left';
        case 'right':
            return 'text-right';
        default:
            return 'text-center';
    }
});

const grayscaleClass = computed(() => {
    return props.content.grayscale ? 'grayscale hover:grayscale-0' : '';
});
</script>

<template>
    <section 
        class="py-16 px-4"
        :style="{ backgroundColor: style?.backgroundColor }"
        data-aos="fade-up"
    >
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div v-if="content.title || content.subtitle" class="mb-12" :class="textAlignClass">
                <h2 
                    v-if="content.title" 
                    class="text-3xl md:text-4xl font-bold text-gray-900 mb-4"
                    data-aos="fade-up"
                >
                    {{ content.title }}
                </h2>
                <p 
                    v-if="content.subtitle" 
                    class="text-lg text-gray-600 max-w-2xl"
                    :class="{ 'mx-auto': content.textAlign === 'center' }"
                    data-aos="fade-up"
                    data-aos-delay="100"
                >
                    {{ content.subtitle }}
                </p>
            </div>

            <!-- Grid Layout -->
            <div v-if="content.layout === 'grid'" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8">
                <div
                    v-for="(item, index) in content.items"
                    :key="index"
                    class="flex items-center justify-center"
                    data-aos="fade-up"
                    :data-aos-delay="index * 50"
                >
                    <a
                        v-if="item.link"
                        :href="item.link"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block transition-all duration-300"
                        :class="grayscaleClass"
                    >
                        <img
                            v-if="item.image"
                            :src="item.image"
                            :alt="item.name"
                            class="h-12 w-auto object-contain"
                        />
                        <div
                            v-else
                            class="h-12 w-24 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-400"
                        >
                            Logo
                        </div>
                    </a>
                    <div v-else class="transition-all duration-300" :class="grayscaleClass">
                        <img
                            v-if="item.image"
                            :src="item.image"
                            :alt="item.name"
                            class="h-12 w-auto object-contain"
                        />
                        <div
                            v-else
                            class="h-12 w-24 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-400"
                        >
                            Logo
                        </div>
                    </div>
                </div>
            </div>

            <!-- Marquee Layout (Scrolling) -->
            <div v-else-if="content.layout === 'marquee'" class="overflow-hidden">
                <div class="flex animate-marquee gap-12">
                    <div
                        v-for="(item, index) in [...content.items, ...content.items]"
                        :key="index"
                        class="flex-shrink-0 transition-all duration-300"
                        :class="grayscaleClass"
                    >
                        <img
                            v-if="item.image"
                            :src="item.image"
                            :alt="item.name"
                            class="h-12 w-auto object-contain"
                        />
                        <div
                            v-else
                            class="h-12 w-24 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-400"
                        >
                            Logo
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row Layout -->
            <div v-else class="flex flex-wrap justify-center items-center gap-12">
                <div
                    v-for="(item, index) in content.items"
                    :key="index"
                    class="transition-all duration-300"
                    :class="grayscaleClass"
                    data-aos="fade-up"
                    :data-aos-delay="index * 50"
                >
                    <img
                        v-if="item.image"
                        :src="item.image"
                        :alt="item.name"
                        class="h-12 w-auto object-contain"
                    />
                    <div
                        v-else
                        class="h-12 w-24 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-400"
                    >
                        Logo
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes marquee {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

.animate-marquee {
    animation: marquee 30s linear infinite;
}

.animate-marquee:hover {
    animation-play-state: paused;
}
</style>
