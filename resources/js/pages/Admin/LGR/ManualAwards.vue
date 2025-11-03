<template>
  <AdminLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">LGR Manual Awards</h2>
            <p class="mt-1 text-sm text-gray-600">Award loyalty bonuses to active premium members</p>
          </div>
          <button
            @click="showAwardModal = true"
            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Award Bonus
          </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid gap-6 sm:grid-cols-3">
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <p class="text-sm font-medium text-gray-500">Total Awarded</p>
              <p class="mt-2 text-3xl font-bold text-blue-600">
                K{{ Number(stats.total_awarded).toFixed(2) }}
              </p>
            </div>
          </div>

          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <p class="text-sm font-medium text-gray-500">Total Recipients</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ stats.total_recipients }}
              </p>
            </div>
          </div>

          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <p class="text-sm font-medium text-gray-500">This Month</p>
              <p class="mt-2 text-3xl font-bold text-green-600">
                K{{ Number(stats.this_month).toFixed(2) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Awards Table -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Award History</h3>
            
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      Recipient
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      Amount
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      Awarded By
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      Reason
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="award in awards.data" :key="award.id" class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      {{ formatDate(award.created_at) }}
                    </td>
                    <td class="px-6 py-4 text-sm">
                      <div class="font-medium text-gray-900">{{ award.user.name }}</div>
                      <div class="text-gray-500">{{ award.user.email }}</div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-green-600">
                      K{{ Number(award.amount).toFixed(2) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        :class="[
                          'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                          getTypeClass(award.award_type),
                        ]"
                      >
                        {{ formatType(award.award_type) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      {{ award.awarded_by.name }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                      {{ award.reason }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="awards.links.length > 3" class="mt-6 flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Showing {{ awards.from }} to {{ awards.to }} of {{ awards.total }} awards
              </div>
              <div class="flex space-x-2">
                <Link
                  v-for="link in awards.links"
                  :key="link.label"
                  :href="link.url"
                  :class="[
                    'rounded-md px-3 py-2 text-sm',
                    link.active
                      ? 'bg-blue-600 text-white'
                      : 'bg-white text-gray-700 hover:bg-gray-50',
                    !link.url && 'cursor-not-allowed opacity-50',
                  ]"
                  v-html="link.label"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Award Modal -->
    <AwardModal 
      v-if="showAwardModal"
      :eligible-members="eligibleMembers"
      @close="showAwardModal = false"
      @success="handleAwardSuccess"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AwardModal from '@/components/Admin/LGR/AwardModal.vue';

interface Props {
  awards: any;
  stats: {
    total_awarded: number;
    total_recipients: number;
    this_month: number;
  };
  eligibleMembers: any[];
}

defineProps<Props>();

const showAwardModal = ref(false);

const handleAwardSuccess = () => {
  showAwardModal.value = false;
  router.reload({ only: ['awards', 'stats'] });
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getTypeClass = (type: string) => {
  const classes = {
    early_adopter: 'bg-blue-100 text-blue-800',
    performance: 'bg-green-100 text-green-800',
    special: 'bg-purple-100 text-purple-800',
    marketing: 'bg-amber-100 text-amber-800',
  };
  return classes[type as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};
</script>
