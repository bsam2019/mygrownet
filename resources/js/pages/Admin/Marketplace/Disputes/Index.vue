<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

interface Dispute {
  id: number;
  type: string;
  status: 'open' | 'investigating' | 'resolved' | 'closed';
  created_at: string;
  order: {
    id: number;
    order_number: string;
    total: number;
    formatted_total: string;
  };
  buyer: {
    id: number;
    name: string;
  };
  seller: {
    id: number;
    business_name: string;
  };
}

interface Props {
  disputes: {
    data: Dispute[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  filters: {
    status?: string;
  };
}

const props = defineProps<Props>();

const statusFilter = ref(props.filters.status || 'all');

const statusOptions = [
  { value: 'all', label: 'All Disputes' },
  { value: 'open', label: 'Open' },
  { value: 'investigating', label: 'Investigating' },
  { value: 'resolved', label: 'Resolved' },
  { value: 'closed', label: 'Closed' },
];

const applyFilters = () => {
  router.get('/admin/marketplace/disputes', {
    status: statusFilter.value === 'all' ? undefined : statusFilter.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    open: 'bg-red-100 text-red-800',
    investigating: 'bg-yellow-100 text-yellow-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
  };
  return badges[status] || badges.open;
};

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    not_received: 'Item Not Received',
    not_as_described: 'Not As Described',
    damaged: 'Damaged Item',
    wrong_item: 'Wrong Item',
    other: 'Other',
  };
  return labels[type] || type;
};
</script>

<template>
  <Head title="Dispute Resolution - Admin" />

  <MarketplaceAdminLayout title="Dispute Resolution">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex items-end gap-4">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
          <select
            v-model="statusFilter"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 bg-white"
            @change="applyFilters"
          >
            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
        <button
          @click="applyFilters"
          class="px-6 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
        >
          Apply Filters
        </button>
      </div>
    </div>

    <!-- Disputes List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispute</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buyer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="dispute in disputes.data" :key="dispute.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <ExclamationTriangleIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                  <span class="font-medium text-gray-900">#{{ dispute.id }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div>
                  <p class="font-medium text-gray-900">{{ dispute.order.order_number }}</p>
                  <p class="text-sm text-gray-500">{{ dispute.order.formatted_total }}</p>
                </div>
              </td>
              <td class="px-6 py-4">
                <p class="text-gray-900">{{ dispute.buyer.name }}</p>
              </td>
              <td class="px-6 py-4">
                <Link
                  :href="`/admin/marketplace/sellers/${dispute.seller.id}`"
                  class="text-orange-600 hover:text-orange-700 font-medium"
                >
                  {{ dispute.seller.business_name }}
                </Link>
              </td>
              <td class="px-6 py-4">
                <span class="text-sm text-gray-900">{{ getTypeLabel(dispute.type) }}</span>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusBadge(dispute.status)" class="px-2.5 py-1 rounded-full text-xs font-medium">
                  {{ dispute.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ new Date(dispute.created_at).toLocaleDateString() }}
              </td>
              <td class="px-6 py-4 text-right">
                <Link
                  :href="`/admin/marketplace/disputes/${dispute.id}`"
                  class="text-orange-600 hover:text-orange-700 text-sm font-medium"
                >
                  View Details
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="disputes.data.length === 0" class="text-center py-12">
        <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No disputes found</h3>
        <p class="mt-1 text-sm text-gray-500">Try adjusting your filters</p>
      </div>

      <!-- Pagination -->
      <div v-if="disputes.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ (disputes.current_page - 1) * disputes.per_page + 1 }} to 
          {{ Math.min(disputes.current_page * disputes.per_page, disputes.total) }} of 
          {{ disputes.total }} disputes
        </div>
        <div class="flex gap-2">
          <Link
            v-if="disputes.current_page > 1"
            :href="`/admin/marketplace/disputes?page=${disputes.current_page - 1}`"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Previous
          </Link>
          <Link
            v-if="disputes.current_page < disputes.last_page"
            :href="`/admin/marketplace/disputes?page=${disputes.current_page + 1}`"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Next
          </Link>
        </div>
      </div>
    </div>
  </MarketplaceAdminLayout>
</template>
