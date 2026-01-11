<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import { StarIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/solid';
import { StarIcon as StarOutlineIcon } from '@heroicons/vue/24/outline';

interface Review {
  id: number;
  rating: number;
  comment: string;
  is_approved: boolean;
  verified_purchase: boolean;
  created_at: string;
  product: {
    id: number;
    name: string;
    images: string[];
  };
  buyer: {
    id: number;
    name: string;
  };
}

interface Props {
  reviews: {
    data: Review[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  filters: {
    approved?: string;
  };
}

const props = defineProps<Props>();

const approvalFilter = ref(props.filters.approved || 'all');

const filterOptions = [
  { value: 'all', label: 'All Reviews' },
  { value: 'false', label: 'Pending Approval' },
  { value: 'true', label: 'Approved' },
];

const applyFilters = () => {
  router.get('/admin/marketplace/reviews', {
    approved: approvalFilter.value === 'all' ? undefined : approvalFilter.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const approveReview = (review: Review) => {
  router.post(`/admin/marketplace/reviews/${review.id}/approve`, {}, {
    preserveScroll: true,
  });
};

const rejectReview = (review: Review) => {
  if (!confirm('Reject this review? This action cannot be undone.')) return;

  router.post(`/admin/marketplace/reviews/${review.id}/reject`, {}, {
    preserveScroll: true,
  });
};

const renderStars = (rating: number) => {
  return Array.from({ length: 5 }, (_, i) => i < rating);
};
</script>

<template>
  <Head title="Review Moderation - Admin" />

  <MarketplaceAdminLayout title="Review Moderation">
    <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-end gap-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
            <select
              v-model="approvalFilter"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 bg-white"
              @change="applyFilters"
            >
              <option v-for="option in filterOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
          <button
            @click="applyFilters"
            class="px-6 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
          >
            Apply
          </button>
        </div>
      </div>

      <!-- Reviews List -->
      <div class="space-y-4">
        <div
          v-for="review in reviews.data"
          :key="review.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
        >
          <div class="flex items-start gap-4">
            <!-- Product Image -->
            <img
              v-if="review.product.images[0]"
              :src="review.product.images[0]"
              :alt="review.product.name"
              class="w-20 h-20 rounded-lg object-cover flex-shrink-0"
            />
            <div v-else class="w-20 h-20 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
              <span class="text-gray-400 text-xs">No image</span>
            </div>

            <!-- Review Content -->
            <div class="flex-1 min-w-0">
              <!-- Product Name -->
              <Link
                :href="`/marketplace/products/${review.product.id}`"
                target="_blank"
                class="text-lg font-medium text-gray-900 hover:text-orange-600"
              >
                {{ review.product.name }}
              </Link>

              <!-- Rating -->
              <div class="flex items-center gap-2 mt-2">
                <div class="flex">
                  <StarIcon
                    v-for="(filled, index) in renderStars(review.rating)"
                    :key="index"
                    :class="filled ? 'text-yellow-400' : 'text-gray-300'"
                    class="h-5 w-5"
                    aria-hidden="true"
                  />
                </div>
                <span class="text-sm text-gray-600">{{ review.rating }}/5</span>
                <span v-if="review.verified_purchase" class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full">
                  Verified Purchase
                </span>
              </div>

              <!-- Comment -->
              <p class="mt-3 text-gray-700 whitespace-pre-wrap">{{ review.comment }}</p>

              <!-- Meta Info -->
              <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
                <span>By {{ review.buyer.name }}</span>
                <span>•</span>
                <span>{{ new Date(review.created_at).toLocaleDateString() }}</span>
                <span>•</span>
                <span :class="review.is_approved ? 'text-green-600' : 'text-yellow-600'">
                  {{ review.is_approved ? 'Approved' : 'Pending' }}
                </span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-2 flex-shrink-0">
              <button
                v-if="!review.is_approved"
                @click="approveReview(review)"
                class="flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium"
              >
                <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
                Approve
              </button>
              <button
                @click="rejectReview(review)"
                class="flex items-center gap-1.5 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium"
              >
                <XCircleIcon class="h-4 w-4" aria-hidden="true" />
                {{ review.is_approved ? 'Unapprove' : 'Reject' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="reviews.data.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <StarOutlineIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No reviews found</h3>
          <p class="mt-1 text-sm text-gray-500">Try adjusting your filters</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="reviews.last_page > 1" class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 px-6 py-4 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ (reviews.current_page - 1) * reviews.per_page + 1 }} to 
          {{ Math.min(reviews.current_page * reviews.per_page, reviews.total) }} of 
          {{ reviews.total }} reviews
        </div>
        <div class="flex gap-2">
          <Link
            v-if="reviews.current_page > 1"
            :href="`/admin/marketplace/reviews?page=${reviews.current_page - 1}`"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Previous
          </Link>
          <Link
            v-if="reviews.current_page < reviews.last_page"
            :href="`/admin/marketplace/reviews?page=${reviews.current_page + 1}`"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Next
          </Link>
        </div>
      </div>
  </MarketplaceAdminLayout>
</template>
