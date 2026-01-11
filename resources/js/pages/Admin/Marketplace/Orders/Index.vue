<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import { MagnifyingGlassIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline';

interface Order {
  id: number;
  order_number: string;
  status: string;
  total: number;
  formatted_total: string;
  created_at: string;
  buyer: {
    id: number;
    name: string;
    email: string;
  };
  seller: {
    id: number;
    business_name: string;
  };
}

interface Props {
  orders: {
    data: Order[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  filters: {
    status?: string;
    seller_id?: number;
  };
}

const props = defineProps<Props>();

const statusFilter = ref(props.filters.status || 'all');

const statusOptions = [
  { value: 'all', label: 'All Orders' },
  { value: 'pending', label: 'Pending Payment' },
  { value: 'paid', label: 'Paid' },
  { value: 'processing', label: 'Processing' },
  { value: 'shipped', label: 'Shipped' },
  { value: 'delivered', label: 'Delivered' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' },
  { value: 'disputed', label: 'Disputed' },
];

const applyFilters = () => {
  router.get('/admin/marketplace/orders', {
    status: statusFilter.value === 'all' ? undefined : statusFilter.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-blue-100 text-blue-800',
    processing: 'bg-indigo-100 text-indigo-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-teal-100 text-teal-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-gray-100 text-gray-800',
    disputed: 'bg-red-100 text-red-800',
    refunded: 'bg-orange-100 text-orange-800',
  };
  return badges[status] || badges.pending;
};
</script>

<template>
  <Head title="Order Monitoring - Admin" />

  <MarketplaceAdminLayout title="Order Monitoring">
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

    <!-- Orders List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buyer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <ShoppingBagIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                  <div>
                    <p class="font-medium text-gray-900">{{ order.order_number }}</p>
                    <p class="text-sm text-gray-500">ID: {{ order.id }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div>
                  <p class="font-medium text-gray-900">{{ order.buyer.name }}</p>
                  <p class="text-sm text-gray-500">{{ order.buyer.email }}</p>
                </div>
              </td>
              <td class="px-6 py-4">
                <Link
                  :href="`/admin/marketplace/sellers/${order.seller.id}`"
                  class="text-orange-600 hover:text-orange-700 font-medium"
                >
                  {{ order.seller.business_name }}
                </Link>
              </td>
              <td class="px-6 py-4 text-gray-900 font-medium">
                {{ order.formatted_total }}
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusBadge(order.status)" class="px-2.5 py-1 rounded-full text-xs font-medium">
                  {{ order.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ new Date(order.created_at).toLocaleDateString() }}
              </td>
              <td class="px-6 py-4 text-right">
                <Link
                  :href="`/admin/marketplace/orders/${order.id}`"
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
      <div v-if="orders.data.length === 0" class="text-center py-12">
        <ShoppingBagIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
        <p class="mt-1 text-sm text-gray-500">Try adjusting your filters</p>
      </div>

      <!-- Pagination -->
      <div v-if="orders.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ (orders.current_page - 1) * orders.per_page + 1 }} to 
          {{ Math.min(orders.current_page * orders.per_page, orders.total) }} of 
          {{ orders.total }} orders
        </div>
        <div class="flex gap-2">
          <Link
            v-if="orders.current_page > 1"
            :href="`/admin/marketplace/orders?page=${orders.current_page - 1}`"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Previous
          </Link>
          <Link
            v-if="orders.current_page < orders.last_page"
            :href="`/admin/marketplace/orders?page=${orders.current_page + 1}`"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Next
          </Link>
        </div>
      </div>
    </div>
  </MarketplaceAdminLayout>
</template>
