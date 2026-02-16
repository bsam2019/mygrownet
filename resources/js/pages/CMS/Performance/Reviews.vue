<template>
  <CMSLayout title="Performance Reviews">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Performance Reviews</h1>
          <p class="text-sm text-gray-600 mt-1">Manage employee performance reviews</p>
        </div>
        <Link
          :href="route('cms.performance.reviews.create')"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Create Review
        </Link>
      </div>

      <!-- Reviews List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cycle</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="review in reviews" :key="review.id">
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ review.worker?.name }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ review.cycle?.cycle_name }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ review.review_type }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ review.due_date }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ review.overall_rating ? review.overall_rating.toFixed(2) : 'N/A' }}
              </td>
              <td class="px-6 py-4">
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    review.status === 'completed' ? 'bg-green-100 text-green-800' :
                    review.status === 'submitted' ? 'bg-blue-100 text-blue-800' :
                    review.status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ review.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-sm">
                <Link
                  :href="route('cms.performance.reviews.show', review.id)"
                  class="text-blue-600 hover:text-blue-800"
                >
                  {{ review.status === 'pending' ? 'Start Review' : 'View' }}
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

defineProps<{
  reviews: any[];
}>();
</script>
