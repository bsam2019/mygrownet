<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowTrendingUpIcon, ArrowTrendingDownIcon } from '@heroicons/vue/24/outline';

interface Props {
    name: string;
    value: number | string;
    previousValue?: number;
    icon: any;
    href: string;
    color?: 'blue' | 'green' | 'violet' | 'amber' | 'pink' | 'indigo';
    prefix?: string;
    suffix?: string;
    sparklineData?: number[];
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    color: 'violet',
    prefix: '',
    suffix: '',
    loading: false,
});

const displayValue = ref(0);
const isAnimating = ref(true);

const colorClasses = computed(() => {
    const colors = {
        blue: { bg: 'bg-blue-500', light: 'bg-blue-50', text: 'text-blue-600', ring: 'ring-blue-200' },
        green: { bg: 'bg-emerald-500', light: 'bg-emerald-50', text: 'text-emerald-600', ring: 'ring-emerald-200' },
        violet: { bg: 'bg-violet-500', light: 'bg-violet-50', text: 'text-violet-600', ring: 'ring-violet-200' },
        amber: { bg: 'bg-amber-500', light: 'bg-amber-50', text: 'text-amber-600', ring: 'ring-amber-200' },
        pink: { bg: 'bg-pink-500', light: 'bg-pink-50', text: 'text-pink-600', ring: 'ring-pink-200' },
        indigo: { bg: 'bg-indigo-500', light: 'bg-indigo-50', text: 'text-indigo-600', ring: 'ring-indigo-200' },
    };
    return colors[props.color];
});

const percentChange = computed(() => {
    if (typeof props.value !== 'number' || !props.previousValue) return null;
    if (props.previousValue === 0) return props.value > 0 ? 100 : 0;
    return Math.round(((props.value - props.previousValue) / props.previousValue) * 100);
});

const sparklinePath = computed(() => {
    if (!props.sparklineData?.length) return '';
    const data = props.sparklineData;
    const max = Math.max(...data);
    const min = Math.min(...data);
    const range = max - min || 1;
    const width = 100;
    const height = 24;
    const points = data.map((val, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - ((val - min) / range) * height;
        return `${x},${y}`;
    });
    return `M${points.join(' L')}`;
});

// Animate number on mount
onMounted(() => {
    if (typeof props.value === 'number') {
        const duration = 1000;
        const start = performance.now();
        const animate = (now: number) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            displayValue.value = Math.round(eased * (props.value as number));
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                isAnimating.value = false;
            }
        };
        requestAnimationFrame(animate);
    }
});
</script>

<template>
    <Link
        :href="href"
        :class="[
            'group relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 p-5 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700',
            'hover:shadow-md hover:ring-2 transition-all duration-300',
            `hover:${colorClasses.ring}`
        ]"
    >
        <!-- Loading skeleton -->
        <div v-if="loading" class="animate-pulse">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-slate-200"></div>
                <div class="flex-1">
                    <div class="h-3 w-16 bg-slate-200 rounded mb-2"></div>
                    <div class="h-6 w-24 bg-slate-200 rounded"></div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div v-else>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div :class="[colorClasses.bg, 'rounded-xl p-2.5 shadow-lg shadow-slate-200 group-hover:scale-110 transition-transform duration-300']">
                        <component :is="icon" class="h-5 w-5 text-white" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ name }}</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white tabular-nums">
                            {{ prefix }}{{ typeof value === 'number' ? displayValue.toLocaleString() : value }}{{ suffix }}
                        </p>
                    </div>
                </div>

                <!-- Trend indicator -->
                <div v-if="percentChange !== null" :class="[
                    'flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium',
                    percentChange >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400'
                ]">
                    <component
                        :is="percentChange >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
                        class="h-3.5 w-3.5"
                        aria-hidden="true"
                    />
                    {{ percentChange >= 0 ? '+' : '' }}{{ percentChange }}%
                </div>
            </div>

            <!-- Sparkline -->
            <div v-if="sparklineData?.length" class="mt-4 h-6">
                <svg class="w-full h-full" viewBox="0 0 100 24" preserveAspectRatio="none">
                    <defs>
                        <linearGradient :id="`gradient-${name}`" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" :class="colorClasses.text" style="stop-opacity: 0.3" />
                            <stop offset="100%" :class="colorClasses.text" style="stop-opacity: 0" />
                        </linearGradient>
                    </defs>
                    <!-- Area fill -->
                    <path
                        :d="`${sparklinePath} L100,24 L0,24 Z`"
                        :fill="`url(#gradient-${name})`"
                        class="opacity-50"
                    />
                    <!-- Line -->
                    <path
                        :d="sparklinePath"
                        fill="none"
                        :class="colorClasses.text"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>
        </div>

        <!-- Hover gradient overlay -->
        <div :class="[
            'absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none',
            'bg-gradient-to-br from-transparent via-transparent to-slate-50'
        ]"></div>
    </Link>
</template>
