<template>
  <AdminLayout title="Starter Kit Tiers">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-semibold text-gray-900">Starter Kit Tier Management</h1>
          <p class="mt-1 text-sm text-gray-500">
            Configure starter kit tiers, pricing, storage allocations, and benefits
          </p>
        </div>

        <!-- Tiers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="tier in tiers"
            :key="tier.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
          >
            <!-- Tier Header -->
            <div class="p-6 border-b border-gray-200">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">{{ tier.tier_name }}</h3>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    tier.is_active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ tier.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <p class="text-sm text-gray-500">{{ tier.description }}</p>
            </div>

            <!-- Tier Details -->
            <div class="p-6 space-y-4">
              <div>
                <div class="text-sm text-gray-500">Price</div>
                <div class="text-2xl font-bold text-gray-900">K{{ tier.price }}</div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-500">Storage</div>
                  <div class="text-lg font-semibold text-gray-900">{{ tier.storage_gb }} GB</div>
                </div>
                <div>
                  <div class="text-sm text-gray-500">Earning %</div>
                  <div class="text-lg font-semibold text-gray-900">{{ tier.earning_potential_percentage }}%</div>
                </div>
              </div>

              <div>
                <div class="text-sm text-gray-500">Benefits</div>
                <div class="text-lg font-semibold text-gray-900">{{ tier.benefits_count }} items</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
              <Link
                :href="route('admin.starter-kit-tiers.edit', tier.id)"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Edit Tier
              </Link>
            </div>
          </div>
        </div>

        <!-- Info Box -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <InformationCircleIcon class="h-5 w-5 text-blue-400" aria-hidden="true" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">Tier Configuration</h3>
              <div class="mt-2 text-sm text-blue-700">
                <p>
                  Click "Edit Tier" to modify tier details, pricing, storage allocations, and benefit assignments.
                  Changes take effect immediately for new purchases.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { InformationCircleIcon } from '@heroicons/vue/24/outline';

interface Tier {
  id: number;
  tier_key: string;
  tier_name: string;
  description: string;
  price: number;
  storage_gb: number;
  earning_potential_percentage: number;
  sort_order: number;
  is_active: boolean;
  benefits_count: number;
}

defineProps<{
  tiers: Tier[];
}>();
</script>
