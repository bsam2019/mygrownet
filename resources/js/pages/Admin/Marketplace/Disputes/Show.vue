<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
  ExclamationTriangleIcon, 
  CheckCircleIcon,
  ClockIcon,
  UserIcon,
  ShoppingBagIcon
} from '@heroicons/vue/24/outline';

interface Dispute {
  id: number;
  order_id: number;
  reason: string;
  description: string;
  status: 'open' | 'investigating' | 'resolved' | 'closed';
  resolution_type?: string;
  resolution?: string;
  created_at: string;
  resolved_at?: string;
  order: {
    order_number: string;
    total: number;
    formatted_total: string;
    items: Array<{
      product: {
        name: string;
        images: string[];
      };
      quantity: number;
      price: number;
    }>;
  };
  buyer: {
    id: number;
    name: string;
    email: string;
  };
  seller: {
    id: number;
    business_name: string;
    user: {
      email: string;
    };
  };
}

interface Props {
  dispute: Dispute;
}

const props = defineProps<Props>();

const showResolveModal = ref(false);
const resolutionType = ref('');
const resolutionNotes = ref('');
const processing = ref(false);

const resolutionOptions = [
  { value: 'refund', label: 'Full Refund to Buyer' },
  { value: 'partial_refund', label: 'Partial Refund' },
  { value: 'replacement', label: 'Replacement Product' },
  { value: 'no_action', label: 'No Action Required' },
];

const resolveDispute = () => {
  if (!resolutionType.value || !resolutionNotes.value.trim()) return;

  processing.value = true;
  router.post(`/admin/marketplace/disputes/${props.dispute.id}/resolve`, {
    resolution_type: resolutionType.value,
    resolution: resolutionNotes.value,
  }, {
    onSuccess: () => {
      showResolveModal.value = false;
      processing.value = false;
    },
    onError: () => {
      processing.value = false;
    },
  });
};

const getStatusBadge = (status: string) => {
  const badges = {
    open: 'bg-red-100 text-red-800',
    investigating: 'bg-yellow-100 text-yellow-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
  };
  return badges[status as keyof typeof badges] || badges.open;
};
</script>

<template>
  <Head :title="`Dispute #${dispute.id} - Admin`" />

  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <Link href="/admin/marketplace/disputes" class="text-orange-600 hover:text-orange-700 text-sm font-medium mb-2 inline-block">
          ← Back to Disputes
        </Link>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Dispute #{{ dispute.id }}</h1>
            <p class="mt-1 text-gray-600">Order {{ dispute.order.order_number }}</p>
          </div>
          <span :class="getStatusBadge(dispute.status)" class="px-3 py-1.5 rounded-full text-sm font-medium">
            {{ dispute.status }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Dispute Details -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-start gap-3 mb-4">
              <ExclamationTriangleIcon class="h-6 w-6 text-red-600 flex-shrink-0 mt-1" aria-hidden="true" />
              <div class="flex-1">
                <h2 class="text-lg font-bold text-gray-900 mb-2">{{ dispute.reason }}</h2>
                <p class="text-gray-700 whitespace-pre-wrap">{{ dispute.description }}</p>
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
              <p class="text-sm text-gray-500">
                Filed on {{ new Date(dispute.created_at).toLocaleString() }}
              </p>
            </div>
          </div>

          <!-- Order Details -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
              <ShoppingBagIcon class="h-5 w-5" aria-hidden="true" />
              Order Details
            </h3>
            <div class="space-y-4">
              <div v-for="(item, index) in dispute.order.items" :key="index" class="flex gap-4">
                <img
                  v-if="item.product.images[0]"
                  :src="item.product.images[0]"
                  :alt="item.product.name"
                  class="w-16 h-16 rounded-lg object-cover"
                />
                <div class="flex-1">
                  <p class="font-medium text-gray-900">{{ item.product.name }}</p>
                  <p class="text-sm text-gray-600">Quantity: {{ item.quantity }}</p>
                  <p class="text-sm font-medium text-gray-900">K{{ (item.price / 100).toFixed(2) }}</p>
                </div>
              </div>
              <div class="pt-4 border-t border-gray-200">
                <div class="flex justify-between text-lg font-bold">
                  <span>Total</span>
                  <span>{{ dispute.order.formatted_total }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Resolution (if resolved) -->
          <div v-if="dispute.status === 'resolved' && dispute.resolution" class="bg-green-50 rounded-lg border border-green-200 p-6">
            <div class="flex items-start gap-3">
              <CheckCircleIcon class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" aria-hidden="true" />
              <div class="flex-1">
                <h3 class="text-lg font-bold text-green-900 mb-2">Resolution</h3>
                <p class="text-sm text-green-800 mb-2">
                  <strong>Type:</strong> {{ dispute.resolution_type?.replace('_', ' ') }}
                </p>
                <p class="text-green-900 whitespace-pre-wrap">{{ dispute.resolution }}</p>
                <p class="text-sm text-green-700 mt-3">
                  Resolved on {{ new Date(dispute.resolved_at!).toLocaleString() }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Actions -->
          <div v-if="dispute.status !== 'resolved'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Actions</h3>
            <button
              @click="showResolveModal = true"
              class="w-full px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
            >
              Resolve Dispute
            </button>
          </div>

          <!-- Buyer Info -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-3 flex items-center gap-2">
              <UserIcon class="h-4 w-4" aria-hidden="true" />
              Buyer
            </h3>
            <div>
              <p class="font-medium text-gray-900">{{ dispute.buyer.name }}</p>
              <p class="text-sm text-gray-600">{{ dispute.buyer.email }}</p>
              <Link
                :href="`/admin/users/${dispute.buyer.id}`"
                class="text-sm text-orange-600 hover:text-orange-700 font-medium mt-2 inline-block"
              >
                View Profile →
              </Link>
            </div>
          </div>

          <!-- Seller Info -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-3 flex items-center gap-2">
              <UserIcon class="h-4 w-4" aria-hidden="true" />
              Seller
            </h3>
            <div>
              <p class="font-medium text-gray-900">{{ dispute.seller.business_name }}</p>
              <p class="text-sm text-gray-600">{{ dispute.seller.user.email }}</p>
              <Link
                :href="`/admin/marketplace/sellers/${dispute.seller.id}`"
                class="text-sm text-orange-600 hover:text-orange-700 font-medium mt-2 inline-block"
              >
                View Seller →
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Resolve Modal -->
  <div v-if="showResolveModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-lg w-full p-6">
      <h3 class="text-lg font-bold text-gray-900 mb-4">Resolve Dispute</h3>
      
      <div class="space-y-4">
        <!-- Resolution Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Resolution Type</label>
          <select
            v-model="resolutionType"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-gray-900 bg-white"
          >
            <option value="">Select resolution type...</option>
            <option v-for="option in resolutionOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Resolution Notes -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Resolution Details</label>
          <textarea
            v-model="resolutionNotes"
            rows="5"
            placeholder="Explain the resolution and any actions taken..."
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-gray-900 bg-white placeholder-gray-400"
          ></textarea>
        </div>
      </div>

      <div class="mt-6 flex gap-3 justify-end">
        <button
          @click="showResolveModal = false"
          :disabled="processing"
          class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50"
        >
          Cancel
        </button>
        <button
          @click="resolveDispute"
          :disabled="!resolutionType || !resolutionNotes.trim() || processing"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ processing ? 'Resolving...' : 'Resolve Dispute' }}
        </button>
      </div>
    </div>
  </div>
</template>
