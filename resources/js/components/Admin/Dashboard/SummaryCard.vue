<template>
    <div class="bg-white overflow-hidden rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <dt class="text-sm font-medium text-gray-500 truncate">{{ title }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-gray-900">{{ value }}</dd>
                    <div v-if="change !== undefined" class="mt-3 flex items-center text-sm">
                        <TrendIndicator
                            :value="change"
                            :show-badge="true"
                            class="text-sm"
                        />
                        <span class="ml-2 text-gray-500">vs last period</span>
                    </div>
                </div>
                <div class="flex-shrink-0 rounded-full p-3" :class="[iconBackgroundClass]">
                    <component
                        :is="iconComponent"
                        class="h-6 w-6"
                        :class="iconColorClass"
                        aria-hidden="true"
                    />
                </div>
            </div>
            <div v-if="subtitle" class="mt-4">
                <p class="text-sm text-gray-500">{{ subtitle }}</p>
            </div>
        </div>
        <div v-if="showFooter" class="bg-gray-50 px-4 py-4 sm:px-6">
            <div class="text-sm">
                <a :href="footerLink" class="font-medium text-blue-600 hover:text-blue-500">
                    {{ footerLinkText }}
                    <span aria-hidden="true"> &rarr;</span>
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChartBarIcon, UsersIcon, CurrencyDollarIcon, BanknotesIcon } from '@heroicons/vue/24/outline';
import TrendIndicator from '@/components/TrendIndicator.vue';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    icon: {
        type: String,
        required: true
    },
    trend: {
        type: String,
        default: 'up',
        validator: (value) => ['up', 'down'].includes(value)
    },
    change: {
        type: Number,
        default: undefined
    },
    subtitle: {
        type: String,
        default: ''
    },
    footerLink: {
        type: String,
        default: ''
    },
    footerLinkText: {
        type: String,
        default: 'View details'
    },
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'success', 'warning', 'danger', 'info'].includes(value)
    }
});

const iconComponent = computed(() => {
    const icons = {
        'currency-dollar': CurrencyDollarIcon,
        'users': UsersIcon,
        'chart-bar': ChartBarIcon,
        'banknotes': BanknotesIcon
    };
    return icons[props.icon] || ChartBarIcon;
});

const showFooter = computed(() => !!props.footerLink);

const iconBackgroundClass = computed(() => {
    const variants = {
        default: 'bg-blue-50',
        success: 'bg-green-50',
        warning: 'bg-yellow-50',
        danger: 'bg-red-50',
        info: 'bg-indigo-50'
    };
    return variants[props.variant];
});

const iconColorClass = computed(() => {
    const variants = {
        default: 'text-blue-600',
        success: 'text-green-600',
        warning: 'text-yellow-600',
        danger: 'text-red-600',
        info: 'text-indigo-600'
    };
    return variants[props.variant];
});
</script>
