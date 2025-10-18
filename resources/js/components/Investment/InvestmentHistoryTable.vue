<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Investment History</h3>
        <div class="flex items-center space-x-3">
          <!-- Filter Controls -->
          <select 
            v-model="filters.tier" 
            @change="applyFilters"
            data-testid="tier-filter"
            class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Tiers</option>
            <option v-for="tier in availableTiers" :key="tier" :value="tier">{{ tier }}</option>
          </select>
          
          <select 
            v-model="filters.status" 
            @change="applyFilters"
            class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="withdrawn">Withdrawn</option>
          </select>
          
          <button
            @click="toggleSortOrder"
            data-testid="sort-button"
            class="flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors"
          >
            <ArrowsUpDownIcon class="h-4 w-4 mr-1" />
            {{ sortOrder === 'desc' ? 'Newest First' : 'Oldest First' }}
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-32">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>

      <!-- Table -->
      <div v-else-if="filteredInvestments.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Investment Details
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tier & Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Performance
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="investment in filteredInvestments" :key="investment.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div :class="getTierColorClass(investment.tier_name)" class="h-10 w-10 rounded-lg flex items-center justify-center">
                      <BanknotesIcon class="h-5 w-5 text-white" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      Investment #{{ investment.id }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ formatDate(investment.created_at) }}
                    </div>
                  </div>
                </div>
              </td>
              
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  <span :class="getTierBadgeClass(investment.tier_name)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mb-1">
                    {{ investment.tier_name }}
                  </span>
                </div>
                <div class="text-sm font-semibold text-gray-900">
                  {{ formatCurrency(investment.amount) }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ formatPercentage(investment.profit_rate) }} annual rate
                </div>
              </td>
              
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  <div class="font-semibold text-green-600">
                    {{ formatCurrency(investment.current_value) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    Current Value
                  </div>
                </div>
                <div class="mt-1">
                  <div :class="investment.roi >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                    {{ investment.roi >= 0 ? '+' : '' }}{{ formatPercentage(investment.roi) }} ROI
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatCurrency(investment.total_earned) }} earned
                  </div>
                </div>
              </td>
              
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(investment.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ investment.status }}
                </span>
                <div v-if="investment.withdrawal_eligibility" class="mt-1">
                  <div class="text-xs" :class="investment.withdrawal_eligibility.can_withdraw ? 'text-green-600' : 'text-red-600'">
                    {{ investment.withdrawal_eligibility.can_withdraw ? 'Withdrawable' : 'Locked' }}
                  </div>
                  <div v-if="!investment.withdrawal_eligibility.can_withdraw" class="text-xs text-gray-500">
                    {{ investment.withdrawal_eligibility.days_remaining }} days left
                  </div>
                </div>
              </td>
              
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <Link 
                    :href="route('investments.show', investment.id)"
                    class="text-blue-600 hover:text-blue-900 transition-colors"
                  >
                    View
                  </Link>
                  <button
                    v-if="investment.withdrawal_eligibility?.can_withdraw"
                    @click="initiateWithdrawal(investment)"
                    data-testid="withdraw-button"
                    class="text-green-600 hover:text-green-900 transition-colors"
                  >
                    Withdraw
                  </button>
                  <button
                    @click="showPerformanceChart(investment)"
                    class="text-purple-600 hover:text-purple-900 transition-colors"
                  >
                    Chart
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-else data-testid="empty-state" class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
          <BanknotesIcon class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No investments found</h3>
        <p class="text-gray-500 max-w-md mx-auto">
          {{ hasFilters ? 'Try adjusting your filters to see more results.' : 'Start your investment journey today.' }}
        </p>
        <Link 
          v-if="!hasFilters"
          :href="route('investments.create')" 
          class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 transition-colors"
        >
          Make Investment
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} investments
        </div>
        <div class="flex items-center space-x-2">
          <button
            v-for="page in paginationPages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-1 text-sm rounded-lg transition-colors',
              page === pagination.current_page
                ? 'bg-blue-600 text-white'
                : 'text-gray-600 hover:bg-gray-100'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { 
  BanknotesIcon,
  ArrowsUpDownIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency, formatPercentage, formatDate } from '@/utils/formatting';

interface Investment {
  id: number;
  amount: number;
  current_value: number;
  total_earned: number;
  roi: number;
  tier_name: string;
  profit_rate: number;
  status: string;
  created_at: string;
  withdrawal_eligibility?: {
    can_withdraw: boolean;
    days_remaining: number;
  };
}

interface Pagination {
  current_page: number;
  from: number;
  to: number;
  total: number;
  per_page: number;
  last_page: number;
}

interface Props {
  investments: Investment[];
  pagination?: Pagination;
  availableTiers?: string[];
}

const props = withDefaults(defineProps<Props>(), {
  investments: () => [],
  availableTiers: () => ['Basic', 'Starter', 'Builder', 'Leader', 'Elite'],
});

const emit = defineEmits<{
  withdraw: [investment: Investment];
  showChart: [investment: Investment];
}>();

const loading = ref(false);
const filters = ref({
  tier: '',
  status: '',
  search: '',
});
const sortOrder = ref<'asc' | 'desc'>('desc');

const filteredInvestments = computed(() => {
  let filtered = [...props.investments];

  // Apply filters
  if (filters.value.tier) {
    filtered = filtered.filter(inv => inv.tier_name === filters.value.tier);
  }
  
  if (filters.value.status) {
    filtered = filtered.filter(inv => inv.status === filters.value.status);
  }

  // Apply sorting
  filtered.sort((a, b) => {
    const dateA = new Date(a.created_at).getTime();
    const dateB = new Date(b.created_at).getTime();
    return sortOrder.value === 'desc' ? dateB - dateA : dateA - dateB;
  });

  return filtered;
});

const hasFilters = computed(() => {
  return filters.value.tier || filters.value.status || filters.value.search;
});

const paginationPages = computed(() => {
  if (!props.pagination) return [];
  
  const pages = [];
  const current = props.pagination.current_page;
  const last = props.pagination.last_page;
  
  // Show up to 5 pages around current page
  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

const getTierColorClass = (tierName: string): string => {
  const classes: Record<string, string> = {
    'Basic': 'bg-gray-500',
    'Starter': 'bg-blue-500',
    'Builder': 'bg-blue-600',
    'Leader': 'bg-blue-700',
    'Elite': 'bg-indigo-600',
  };
  return classes[tierName] || 'bg-gray-500';
};

const getTierBadgeClass = (tierName: string): string => {
  const classes: Record<string, string> = {
    'Basic': 'bg-gray-100 text-gray-800',
    'Starter': 'bg-blue-100 text-blue-800',
    'Builder': 'bg-blue-100 text-blue-800',
    'Leader': 'bg-blue-100 text-blue-800',
    'Elite': 'bg-indigo-100 text-indigo-800',
  };
  return classes[tierName] || 'bg-gray-100 text-gray-800';
};

const getStatusBadgeClass = (status: string): string => {
  const classes: Record<string, string> = {
    'active': 'bg-green-100 text-green-800',
    'completed': 'bg-blue-100 text-blue-800',
    'withdrawn': 'bg-gray-100 text-gray-800',
    'pending': 'bg-yellow-100 text-yellow-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const applyFilters = () => {
  // In a real implementation, this would trigger a server request
  // For now, we'll just trigger reactivity
  loading.value = true;
  setTimeout(() => {
    loading.value = false;
  }, 300);
};

const toggleSortOrder = () => {
  sortOrder.value = sortOrder.value === 'desc' ? 'asc' : 'desc';
};

const changePage = (page: number) => {
  router.get(route('investments.index'), { page }, { preserveState: true });
};

const initiateWithdrawal = (investment: Investment) => {
  emit('withdraw', investment);
};

const showPerformanceChart = (investment: Investment) => {
  emit('showChart', investment);
};
</script>