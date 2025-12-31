<script setup lang="ts">
import { computed } from 'vue';
import { 
    UsersIcon, 
    ChartBarIcon, 
    TrophyIcon, 
    StarIcon,
    BuildingOfficeIcon,
    HeartIcon,
} from '@heroicons/vue/24/outline';

interface StatItem {
    number: string;
    suffix?: string;
    label: string;
    icon?: string;
}

interface Props {
    content: {
        layout?: string;
        title?: string;
        subtitle?: string;
        textAlign?: string;
        animated?: boolean;
        items?: StatItem[];
    };
    style?: {
        backgroundColor?: string;
        accentColor?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    content: () => ({
        layout: 'horizontal',
        title: 'Our Impact',
        subtitle: 'Numbers that speak for themselves',
        textAlign: 'center',
        animated: true,
        items: [
            { number: '500', suffix: '+', label: 'Happy Clients', icon: 'users' },
            { number: '1000', suffix: '+', label: 'Projects Completed', icon: 'chart' },
            { number: '50', suffix: '+', label: 'Awards Won', icon: 'trophy' },
            { number: '99', suffix: '%', label: 'Satisfaction Rate', icon: 'star' },
        ],
    }),
    style: () => ({
        backgroundColor: '#ffffff',
        accentColor: '#2563eb',
    }),
});

const iconMap: Record<string, any> = {
    users: UsersIcon,
    chart: ChartBarIcon,
    trophy: TrophyIcon,
    star: StarIcon,
    building: BuildingOfficeIcon,
    heart: HeartIcon,
};

const getIcon = (iconName?: string) => {
    return iconName ? iconMap[iconName] || ChartBarIcon : ChartBarIcon;
};

const layoutClass = computed(() => {
    switch (props.content.layout) {
        case 'grid':
            return 'grid grid-cols-2 md:grid-cols-4 gap-8';
        case 'centered':
            return 'flex flex-wrap justify-center gap-12';
        default: // horizontal
            return 'flex flex-wrap justify-between gap-8';
    }
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

            <!-- Stats Grid -->
            <div :class="layoutClass">
                <div
                    v-for="(item, index) in content.items"
                    :key="index"
                    class="text-center"
                    data-aos="fade-up"
                    :data-aos-delay="index * 100"
                >
                    <!-- Icon -->
                    <div 
                        v-if="item.icon"
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
                        :style="{ 
                            backgroundColor: style?.accentColor + '20',
                            color: style?.accentColor 
                        }"
                    >
                        <component :is="getIcon(item.icon)" class="h-8 w-8" aria-hidden="true" />
                    </div>

                    <!-- Number -->
                    <div class="flex items-baseline justify-center gap-1 mb-2">
                        <span 
                            class="text-4xl md:text-5xl font-bold"
                            :style="{ color: style?.accentColor }"
                        >
                            {{ item.number }}
                        </span>
                        <span 
                            v-if="item.suffix"
                            class="text-3xl md:text-4xl font-bold"
                            :style="{ color: style?.accentColor }"
                        >
                            {{ item.suffix }}
                        </span>
                    </div>

                    <!-- Label -->
                    <p class="text-gray-600 font-medium">
                        {{ item.label }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
