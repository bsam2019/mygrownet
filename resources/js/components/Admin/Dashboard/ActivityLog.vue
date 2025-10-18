<template>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Activity Log</h3>
            <div class="flex items-center space-x-2">
                <select v-model="selectedType" class="text-sm border-gray-300 rounded-md">
                    <option value="">All Types</option>
                    <option value="investment">Investments</option>
                    <option value="withdrawal">Withdrawals</option>
                    <option value="user">User Activity</option>
                </select>
            </div>
        </div>

        <div class="flow-root">
            <ul class="-mb-8">
                <li v-for="(activity, index) in filteredActivities" :key="activity.id">
                    <div class="relative pb-8">
                        <span v-if="index !== activities.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                        <div class="relative flex space-x-3">
                            <div>
                                <span :class="[getActivityColor(activity), 'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white']">
                                    <component :is="getActivityIcon(activity)" class="h-5 w-5 text-white" aria-hidden="true" />
                                </span>
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                <div>
                                    <p class="text-sm text-gray-500">
                                        {{ activity.description }}
                                        <span class="font-medium text-gray-900">{{ activity.user }}</span>
                                    </p>
                                    <div v-if="activity.loggable?.amount" class="mt-1 text-sm">
                                        <span :class="getAmountColor(activity)">
                                            {{ formatCurrency(activity.loggable.amount) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="whitespace-nowrap text-right">
                                    <time :datetime="activity.created_at" class="text-sm text-gray-500">
                                        {{ formatDate(activity.created_at) }}
                                    </time>
                                    <StatusBadge
                                        v-if="activity.loggable?.status"
                                        :status="activity.loggable.status"
                                        class="mt-1"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li v-if="!filteredActivities.length" class="text-center py-4 text-sm text-gray-500">
                    No activities found
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import {
    UserIcon,
    CurrencyDollarIcon,
    ArrowPathIcon,
    ShieldCheckIcon,
    BanknotesIcon
} from '@heroicons/vue/24/outline';
import { formatDate, formatCurrency } from '@/utils/formatting';
import StatusBadge from '@/components/StatusBadge.vue';

const props = defineProps({
    activities: {
        type: Array,
        required: true
    }
});

const selectedType = ref('');

const filteredActivities = computed(() => {
    if (!selectedType.value) return props.activities;

    return props.activities.filter(activity => {
        const type = activity.type?.toLowerCase() || '';
        return type === selectedType.value;
    });
});

const getActivityIcon = (activity) => {
    const type = activity.type?.toLowerCase() || '';

    return {
        investment: CurrencyDollarIcon,
        withdrawal: BanknotesIcon,
        user: UserIcon,
        system: ArrowPathIcon,
        security: ShieldCheckIcon
    }[type] || ArrowPathIcon;
};

const getActivityColor = (activity) => {
    const type = activity.type?.toLowerCase() || '';

    return {
        investment: 'bg-blue-500',
        withdrawal: 'bg-red-500',
        user: 'bg-green-500',
        system: 'bg-purple-500',
        security: 'bg-yellow-500'
    }[type] || 'bg-gray-500';
};

const getAmountColor = (activity) => {
    const type = activity.type?.toLowerCase() || '';

    return {
        withdrawal: 'text-red-600',
        investment: activity.loggable?.status === 'completed' ? 'text-green-600' : 'text-gray-900'
    }[type] || 'text-gray-900';
};
</script>
