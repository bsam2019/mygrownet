<script setup lang="ts">
import { computed } from 'vue';

interface UsageData {
  used: number;
  limit: number | 'unlimited';
  remaining: number | 'unlimited';
  is_unlimited: boolean;
  percentage: number;
}

interface Props {
  type: string;
  usage: UsageData;
  showLabel?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showLabel: true,
});

const typeLabels: Record<string, string> = {
  transactions_per_month: 'Transactions',
  storage_mb: 'Storage (MB)',
  savings_goals: 'Savings Goals',
  team_members: 'Team Members',
  api_calls: 'API Calls',
  products: 'Products',
  contacts: 'Contacts',
  vendors: 'Vendors',
  guests: 'Guests',
};

const label = computed(() => typeLabels[props.type] || props.type);

const progressColor = computed(() => {
  if (props.usage.is_unlimited) return 'bg-green-500';
  if (props.usage.percentage >= 100) return 'bg-red-500';
  if (props.usage.percentage >= 80) return 'bg-amber-500';
  return 'bg-blue-500';
});

const statusText = computed(() => {
  if (props.usage.is_unlimited) return 'Unlimited';
  return `${props.usage.used} / ${props.usage.limit}`;
});

const remainingText = computed(() => {
  if (props.usage.is_unlimited) return '∞ remaining';
  return `${props.usage.remaining} remaining`;
});
</script>

<template>
  <div class="space-y-1">
    <div v-if="showLabel" class="flex justify-between text-sm">
      <span class="text-gray-600">{{ label }}</span>
      <span class="text-gray-900 font-medium">{{ statusText }}</span>
    </div>
    
    <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
      <div
        v-if="!usage.is_unlimited"
        class="absolute inset-y-0 left-0 rounded-full transition-all duration-300"
        :class="progressColor"
        :style="{ width: `${Math.min(usage.percentage, 100)}%` }"
      />
      <div
        v-else
        class="absolute inset-0 bg-green-500 rounded-full"
      />
    </div>
    
    <div class="text-xs text-gray-500">
      {{ remainingText }}
    </div>
  </div>
</template>
