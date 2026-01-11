<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import { MagnifyingGlassIcon, CheckCircleIcon, XCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Product {
  id: number;
  name: string;
  price: number;
  formatted_price: string;
  status: 'pending' | 'active' | 'rejected' | 'inactive';
  images: string[];
  image_urls: string[];
  primary_image_url: string | null;
  seller: {
    id: number;
    business_name: string;
  };
  created_at: string;
}

interface Props {
  products: {
    data: Product[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  filters: {
    status?: string;
    search?: string;
  };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const showRejectModal = ref(false);
const selectedProduct = ref<Product | null>(null);
const rejectReason = ref('');
const processing = ref(false);

const statusOptions = [
  { value: 'all', label: 'All Products' },
  { value: 'pending', label: 'Pending Review' },
  { value: 'active', label: 'Active' },
  { value: 'rejected', label: 'Rejected' },
  { value: 'inactive', label: 'Inactive' },
];

const applyFilters = () => {
  router.get('/admin/marketplace/products', {
    status: statusFilter.value === 'all' ? undefined : statusFilter.value,
    search: search.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const approveProduct = (product: Product) => {
  if (!confirm(`Approve product "${product.name}"?`)) return;

  router.post(`/admin/marketplace/products/${product.id}/approve`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message handled by backend
    },
  });
};

const openRejectModal = (product: Product) => {
  selectedProduct.value = product;
  rejectReason.value = '';
  showRejectModal.value = true;
};

const rejectProduct = () => {
  if (!selectedProduct.value || !rejectReason.value.trim()) return;

  processing.value = true;
  router.post(`/admin/marketplace/products/${selectedProduct.value.id}/reject`, {
    reason: rejectReason.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showRejectModal.value = false;
      selectedProduct.value = null;
      rejectReason.value = '';
      processing.value = false;
    },
    onError: () => {
      processing.value = false;
    },
  });
};

const getStatusBadge = (status: string) => {
  const badges = {
    pending: 'bg-yellow-100 text-yellow-800',
    active: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    inactive: 'bg-gray-100 text-gray-800',
  };
  return badges[status as keyof typeof badges] || badges.inactive;
};
</script>

<template>
  <Head title="Product Moderation - Admin" />

  <MarketplaceAdminLayout title="Product Moderation">
    <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Products</label>
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
              <input
                v-model="search"
                type="text"
                placeholder="Search by product name..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 bg-white placeholder-gray-400"
                @keyup.enter="applyFilters"
              />
            </div>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
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
        </div>

        <div class="mt-4 flex justify-end">
          <button
            @click="applyFilters"
            class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
          >
            Apply Filters
          </button>
        </div>
      </div>

      <!-- Products List -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <img
                      v-if="product.primary_image_url"
                      :src="product.primary_image_url"
                      :alt="product.name"
                      class="w-12 h-12 rounded-lg object-cover"
                    />
                    <div v-else class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center">
                      <span class="text-gray-400 text-xs">No image</span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ product.name }}</p>
                      <p class="text-sm text-gray-500">ID: {{ product.id }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <Link
                    :href="`/admin/marketplace/sellers/${product.seller.id}`"
                    class="text-orange-600 hover:text-orange-700 font-medium"
                  >
                    {{ product.seller.business_name }}
                  </Link>
                </td>
                <td class="px-6 py-4 text-gray-900 font-medium">
                  {{ product.formatted_price }}
                </td>
                <td class="px-6 py-4">
                  <span :class="getStatusBadge(product.status)" class="px-2.5 py-1 rounded-full text-xs font-medium">
                    {{ product.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ new Date(product.created_at).toLocaleDateString() }}
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/admin/marketplace/products/${product.id}`"
                      class="text-gray-600 hover:text-gray-900 text-sm font-medium"
                    >
                      View
                    </Link>
                    <button
                      v-if="product.status === 'pending'"
                      @click="approveProduct(product)"
                      class="flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm"
                    >
                      <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
                      Approve
                    </button>
                    <button
                      v-if="product.status === 'pending'"
                      @click="openRejectModal(product)"
                      class="flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm"
                    >
                      <XCircleIcon class="h-4 w-4" aria-hidden="true" />
                      Reject
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="products.data.length === 0" class="text-center py-12">
          <ClockIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
          <p class="mt-1 text-sm text-gray-500">Try adjusting your filters</p>
        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ (products.current_page - 1) * products.per_page + 1 }} to 
            {{ Math.min(products.current_page * products.per_page, products.total) }} of 
            {{ products.total }} products
          </div>
          <div class="flex gap-2">
            <Link
              v-if="products.current_page > 1"
              :href="`/admin/marketplace/products?page=${products.current_page - 1}`"
              class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Previous
            </Link>
            <Link
              v-if="products.current_page < products.last_page"
              :href="`/admin/marketplace/products?page=${products.current_page + 1}`"
              class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Next
            </Link>
          </div>
        </div>
      </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Reject Product</h3>
        <p class="text-sm text-gray-600 mb-4">
          Please provide a reason for rejecting "{{ selectedProduct?.name }}"
        </p>
        <textarea
          v-model="rejectReason"
          rows="4"
          placeholder="Enter rejection reason..."
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 bg-white placeholder-gray-400"
        ></textarea>
        <div class="mt-6 flex gap-3 justify-end">
          <button
            @click="showRejectModal = false"
            :disabled="processing"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            Cancel
          </button>
          <button
            @click="rejectProduct"
            :disabled="!rejectReason.trim() || processing"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ processing ? 'Rejecting...' : 'Reject Product' }}
          </button>
        </div>
      </div>
    </div>
  </MarketplaceAdminLayout>
</template>
