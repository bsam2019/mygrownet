<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <div v-for="metric in investmentMetrics" :key="metric.title" 
            class="bg-white rounded-lg shadow p-3 sm:p-4 transition-all duration-200 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-gray-600">{{ metric.title }}</p>
                    <p class="mt-1 sm:mt-2 text-2xl sm:text-3xl font-semibold text-gray-900">
                        {{ formatKwacha(metric.value) }}
                    </p>
                </div>
                <div :class="`rounded-full p-2 sm:p-3 ${metric.bgColor} transition-colors duration-200`">
                    <component :is="metric.icon" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </div>
            </div>
            <div class="mt-3 sm:mt-4 flex items-center">
                <span :class="`${getChangeColor(metric.change)} text-xs sm:text-sm font-medium flex items-center`">
                    <component 
                        :is="metric.change >= 0 ? ArrowUpIcon : ArrowDownIcon" 
                        class="w-4 h-4 mr-1"
                    />
                    {{ formatPercent(metric.change) }}
                </span>
                <span class="text-gray-500 text-xs sm:text-sm ml-2">vs last period</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import {
    CurrencyDollarIcon,
    UserGroupIcon,
    ChartBarIcon,
    ArrowUpIcon,
    ArrowDownIcon
} from '@heroicons/vue/24/outline';
import { formatKwacha, formatPercent } from '@/utils/format';

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({
            totalValue: 0,
            valueChange: 0,
            activeInvestors: 0,
            investorChange: 0,
            averageRoi: 0,
            roiChange: 0
        })
    }
});

const metrics = computed(() => ({
    totalValue: Number(props.metrics?.totalValue ?? 0),
    valueChange: Number(props.metrics?.valueChange ?? 0),
    activeInvestors: Number(props.metrics?.activeInvestors ?? 0),
    investorChange: Number(props.metrics?.investorChange ?? 0),
    averageRoi: Number(props.metrics?.averageRoi ?? 0),
    roiChange: Number(props.metrics?.roiChange ?? 0)
}));

const investmentMetrics = computed(() => ([
    {
        title: 'Total Investment Value',
        value: metrics.value.totalValue,
        type: 'currency',
        change: metrics.value.valueChange,
        icon: CurrencyDollarIcon,
        bgColor: 'bg-blue-500 hover:bg-blue-600'
    },
    {
        title: 'Active Investors',
        value: metrics.value.activeInvestors,
        type: 'number',
        change: metrics.value.investorChange,
        icon: UserGroupIcon,
        bgColor: 'bg-green-500 hover:bg-green-600'
    },
    {
        title: 'Average ROI',
        value: metrics.value.averageRoi,
        type: 'percentage',
        change: metrics.value.roiChange,
        icon: ChartBarIcon,
        bgColor: 'bg-purple-500 hover:bg-purple-600'
    }
]));

const formatMetricValue = (value, type) => {
    const numValue = Number(value);
    if (isNaN(numValue)) return '-';

    if (type === 'currency') {
        return formatKwacha(numValue);
    }
    if (type === 'percentage') {
        return formatPercent(numValue);
    }
    return numValue.toLocaleString();
};

const formatChange = (value) => {
    const numValue = Number(value);
    if (isNaN(numValue)) return '0%';
    return numValue > 0 ? `+${formatPercent(numValue)}` : formatPercent(numValue);
};

const getChangeColor = (value) => {
    const numValue = Number(value);
    return !isNaN(numValue) && numValue >= 0 ? 'text-green-600' : 'text-red-600';
};
</script>
