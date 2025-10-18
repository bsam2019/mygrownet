<template>
    <div class="relative overflow-hidden rounded-lg p-4 sm:p-6" :class="[bgColor, { 'cursor-pointer transform hover:scale-105 transition-transform': clickable }]">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-xs sm:text-sm text-white/80 font-medium mb-1">{{ title }}</p>
                <p class="text-lg sm:text-2xl font-bold text-white">{{ formattedValue }}</p>
                <div v-if="trend !== undefined" class="mt-2">
                    <TrendIndicator :value="trend" class="text-white text-xs sm:text-sm" />
                </div>
            </div>
            <div class="flex-shrink-0 ml-4">
                <component
                    :is="iconComponent"
                    class="h-8 w-8 sm:h-12 sm:w-12 text-white/50"
                />
            </div>
        </div>
        <div v-if="subtitle" class="mt-2 text-xs text-white/70">{{ subtitle }}</div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import TrendIndicator from '@/components/TrendIndicator.vue';
import {
    ChartBarIcon,
    UsersIcon,
    CurrencyDollarIcon,
    ChartPieIcon
} from '@heroicons/vue/24/outline';
import { formatKwacha, formatPercent } from '@/utils/format';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true,
        default: 0
    },
    trend: {
        type: Number,
        default: 0
    },
    icon: {
        type: String,
        required: true,
        validator: (value) => ['chart-bar', 'users', 'currency-dollar', 'chart-pie'].includes(value)
    },
    bgColor: {
        type: String,
        required: true
    },
    format: {
        type: String,
        default: 'text', // 'text', 'number', 'currency', 'percentage'
        validator: (value) => ['text', 'number', 'currency', 'percentage'].includes(value)
    },
    subtitle: {
        type: String,
        default: ''
    },
    clickable: {
        type: Boolean,
        default: false
    },
    alerts: {
        type: Object,
        default: () => ({
            pending_approvals: 0,
            pending_withdrawals: 0,
            withdrawal_amount: 0,
            system_alerts: []
        })
    }
});

const iconComponent = computed(() => {
    const icons = {
        'chart-bar': ChartBarIcon,
        'users': UsersIcon,
        'currency-dollar': CurrencyDollarIcon,
        'chart-pie': ChartPieIcon
    };
    return icons[props.icon] || ChartBarIcon;
});

const formattedValue = computed(() => {
    if (props.value === undefined || props.value === null) return '-';

    switch (props.format) {
        case 'currency':
            return formatKwacha(Number(props.value));
        case 'percentage':
            return formatPercent(Number(props.value));
        case 'number':
            return new Intl.NumberFormat('en').format(Number(props.value));
        default:
            return props.value;
    }
});
</script>
