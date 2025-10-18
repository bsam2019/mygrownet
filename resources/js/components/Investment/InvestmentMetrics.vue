<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div v-for="(metric, index) in metricItems" :key="index" class="bg-gray-50 p-4 rounded-lg min-h-[120px] flex flex-col">
      <h4 class="text-sm font-medium text-gray-500">{{ metric.label }}</h4>
      <div class="mt-1">
        <div class="break-words">
          <p :class="[
            'font-semibold text-gray-900',
            metric.isCurrency ? 'text-base sm:text-lg' : 'text-lg sm:text-xl'
          ]">
            {{ metric.value || 'N/A' }}
          </p>
        </div>
        <p v-if="metric.change !== undefined && metric.change !== null" :class="[
          'mt-1 text-xs font-medium',
          metric.change >= 0 ? 'text-green-600' : 'text-red-600'
        ]">
          {{ metric.change >= 0 ? '+' : '' }}{{ formatPercentage(metric.change) }}
        </p>
      </div>
      <p v-if="metric.description" class="mt-2 text-xs text-gray-500">{{ metric.description }}</p>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import { formatCurrency, formatPercentage } from '@/utils/formatting';

export default {
  name: 'InvestmentMetrics',
  props: {
    metrics: {
      type: Object,
      required: true,
      validator: (value) => {
        return ['current_value', 'roi', 'growth_rate', 'risk_adjusted_return', 'days_remaining', 'total_earned', 'next_payout'].every(key => key in value);
      }
    }
  },
  setup(props) {
    const metricItems = computed(() => [
      {
        label: 'Current Value',
        value: props.metrics.current_value ? formatCurrency(props.metrics.current_value) : 'N/A',
        change: props.metrics.growth_rate,
        description: 'Total value of your investment including returns',
        isCurrency: true
      },
      {
        label: 'Return on Investment',
        value: props.metrics.roi !== null ? formatPercentage(props.metrics.roi) : 'N/A',
        description: 'Total return as a percentage of your initial investment',
        isCurrency: false
      },
      {
        label: 'Risk-Adjusted Return',
        value: props.metrics.risk_adjusted_return !== null ? formatPercentage(props.metrics.risk_adjusted_return) : 'N/A',
        description: 'Returns adjusted for the level of risk taken',
        isCurrency: false
      },
      {
        label: 'Total Earned',
        value: props.metrics.total_earned !== null ? formatCurrency(props.metrics.total_earned) : 'N/A',
        description: 'Total profits earned from this investment',
        isCurrency: true
      },
      {
        label: 'Next Payout',
        value: props.metrics.next_payout !== null ? formatCurrency(props.metrics.next_payout) : 'N/A',
        description: 'Expected amount of your next profit share',
        isCurrency: true
      },
      {
        label: 'Days Until Lock-in End',
        value: props.metrics.days_remaining !== null ? `${Math.round(props.metrics.days_remaining)} days` : 'N/A',
        description: 'Number of days remaining in the lock-in period',
        isCurrency: false
      }
    ]);

    return {
      metricItems,
      formatPercentage
    };
  }
};
</script> 