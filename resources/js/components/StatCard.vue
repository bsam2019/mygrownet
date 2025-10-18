<template>
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div :class="`bg-${color}-100 rounded-md p-3`">
                        <component :is="iconComponent" :class="`h-6 w-6 text-${color}-600`" />
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">{{ title }}</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">{{ value }}</div>
                            <div v-if="change !== undefined" :class="changeClass" class="ml-2 flex items-baseline text-sm font-semibold">
                                <component :is="changeIcon" class="self-center flex-shrink-0 h-4 w-4" />
                                <span class="sr-only">{{ change >= 0 ? 'Increased' : 'Decreased' }} by</span>
                                {{ Math.abs(change) }}%
                            </div>
                        </dd>
                        <dd v-if="subtitle" class="text-xs text-gray-500 mt-1">{{ subtitle }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { 
    UsersIcon, 
    CreditCardIcon, 
    CurrencyDollarIcon, 
    StarIcon,
    Squares2X2Icon,
    BanknotesIcon,
    ArrowUpIcon,
    ArrowDownIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    title: string;
    value: string | number;
    change?: number;
    subtitle?: string;
    icon: string;
    color?: string;
}>();

const iconMap: Record<string, any> = {
    'users': UsersIcon,
    'credit-card': CreditCardIcon,
    'currency-dollar': CurrencyDollarIcon,
    'star': StarIcon,
    'grid': Squares2X2Icon,
    'banknotes': BanknotesIcon,
};

const iconComponent = computed(() => iconMap[props.icon] || UsersIcon);

const changeClass = computed(() => {
    if (props.change === undefined) return '';
    return props.change >= 0 ? 'text-green-600' : 'text-red-600';
});

const changeIcon = computed(() => {
    if (props.change === undefined) return null;
    return props.change >= 0 ? ArrowUpIcon : ArrowDownIcon;
});
</script>
