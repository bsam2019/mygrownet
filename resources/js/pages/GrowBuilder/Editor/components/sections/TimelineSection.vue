<script setup lang="ts">
import { computed } from 'vue';
import { 
    StarIcon,
    RocketLaunchIcon,
    TrophyIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

interface TimelineItem {
    year: string;
    title: string;
    description?: string;
    icon?: string;
    image?: string;
}

interface Props {
    content: {
        layout?: string;
        title?: string;
        subtitle?: string;
        textAlign?: string;
        items?: TimelineItem[];
    };
    style?: {
        backgroundColor?: string;
        lineColor?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    content: () => ({
        layout: 'vertical',
        title: 'Our Journey',
        subtitle: 'Milestones that shaped our success',
        textAlign: 'center',
        items: [
            { year: '2020', title: 'Company Founded', description: 'Started with a vision to transform the industry', icon: 'star' },
            { year: '2021', title: 'First Major Client', description: 'Secured partnership with leading organization', icon: 'rocket' },
            { year: '2022', title: 'Award Winning', description: 'Recognized as industry leader', icon: 'trophy' },
            { year: '2023', title: 'Expansion', description: 'Opened new offices and doubled team size', icon: 'sparkles' },
        ],
    }),
    style: () => ({
        backgroundColor: '#ffffff',
        lineColor: '#2563eb',
    }),
});

const iconMap: Record<string, any> = {
    star: StarIcon,
    rocket: RocketLaunchIcon,
    trophy: TrophyIcon,
    sparkles: SparklesIcon,
};

const getIcon = (iconName?: string) => {
    return iconName ? iconMap[iconName] || StarIcon : StarIcon;
};

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
</script>

<template>
    <section 
        class="py-16 px-4"
        :style="{ backgroundColor: style?.backgroundColor }"
        data-aos="fade-up"
    >
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div v-if="content.title || content.subtitle" class="mb-16" :class="textAlignClass">
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

            <!-- Vertical Timeline -->
            <div v-if="content.layout === 'vertical'" class="relative max-w-3xl mx-auto">
                <!-- Timeline Line -->
                <div 
                    class="absolute left-8 top-0 bottom-0 w-0.5"
                    :style="{ backgroundColor: style?.lineColor }"
                ></div>

                <!-- Timeline Items -->
                <div class="space-y-12">
                    <div
                        v-for="(item, index) in content.items"
                        :key="index"
                        class="relative pl-20"
                        data-aos="fade-left"
                        :data-aos-delay="index * 100"
                    >
                        <!-- Icon Circle -->
                        <div 
                            class="absolute left-0 w-16 h-16 rounded-full flex items-center justify-center"
                            :style="{ 
                                backgroundColor: style?.lineColor,
                                color: '#ffffff'
                            }"
                        >
                            <component 
                                v-if="item.icon"
                                :is="getIcon(item.icon)" 
                                class="h-8 w-8" 
                                aria-hidden="true" 
                            />
                        </div>

                        <!-- Content -->
                        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                            <span 
                                class="inline-block px-3 py-1 text-sm font-semibold rounded-full mb-3"
                                :style="{ 
                                    backgroundColor: style?.lineColor + '20',
                                    color: style?.lineColor 
                                }"
                            >
                                {{ item.year }}
                            </span>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                {{ item.title }}
                            </h3>
                            <p v-if="item.description" class="text-gray-600">
                                {{ item.description }}
                            </p>
                            <img 
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.title"
                                class="mt-4 rounded-lg w-full h-48 object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horizontal Timeline -->
            <div v-else-if="content.layout === 'horizontal'" class="relative">
                <!-- Timeline Line -->
                <div 
                    class="absolute top-8 left-0 right-0 h-0.5 hidden md:block"
                    :style="{ backgroundColor: style?.lineColor }"
                ></div>

                <!-- Timeline Items -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div
                        v-for="(item, index) in content.items"
                        :key="index"
                        class="relative text-center"
                        data-aos="fade-up"
                        :data-aos-delay="index * 100"
                    >
                        <!-- Icon Circle -->
                        <div 
                            class="relative z-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                            :style="{ 
                                backgroundColor: style?.lineColor,
                                color: '#ffffff'
                            }"
                        >
                            <component 
                                v-if="item.icon"
                                :is="getIcon(item.icon)" 
                                class="h-8 w-8" 
                                aria-hidden="true" 
                            />
                        </div>

                        <!-- Content -->
                        <span 
                            class="inline-block px-3 py-1 text-sm font-semibold rounded-full mb-3"
                            :style="{ 
                                backgroundColor: style?.lineColor + '20',
                                color: style?.lineColor 
                            }"
                        >
                            {{ item.year }}
                        </span>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            {{ item.title }}
                        </h3>
                        <p v-if="item.description" class="text-sm text-gray-600">
                            {{ item.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Zigzag Timeline -->
            <div v-else class="relative max-w-5xl mx-auto">
                <!-- Timeline Line -->
                <div 
                    class="absolute left-1/2 top-0 bottom-0 w-0.5 transform -translate-x-1/2 hidden md:block"
                    :style="{ backgroundColor: style?.lineColor }"
                ></div>

                <!-- Timeline Items -->
                <div class="space-y-12">
                    <div
                        v-for="(item, index) in content.items"
                        :key="index"
                        :class="[
                            'relative grid grid-cols-1 md:grid-cols-2 gap-8',
                            index % 2 === 0 ? 'md:text-right' : 'md:flex-row-reverse'
                        ]"
                        data-aos="fade-up"
                        :data-aos-delay="index * 100"
                    >
                        <!-- Content (Left or Right) -->
                        <div :class="index % 2 === 0 ? 'md:pr-12' : 'md:pl-12 md:col-start-2'">
                            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                                <span 
                                    class="inline-block px-3 py-1 text-sm font-semibold rounded-full mb-3"
                                    :style="{ 
                                        backgroundColor: style?.lineColor + '20',
                                        color: style?.lineColor 
                                    }"
                                >
                                    {{ item.year }}
                                </span>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">
                                    {{ item.title }}
                                </h3>
                                <p v-if="item.description" class="text-gray-600">
                                    {{ item.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Icon Circle (Center) -->
                        <div 
                            class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 w-16 h-16 rounded-full flex items-center justify-center hidden md:flex"
                            :style="{ 
                                backgroundColor: style?.lineColor,
                                color: '#ffffff'
                            }"
                        >
                            <component 
                                v-if="item.icon"
                                :is="getIcon(item.icon)" 
                                class="h-8 w-8" 
                                aria-hidden="true" 
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
