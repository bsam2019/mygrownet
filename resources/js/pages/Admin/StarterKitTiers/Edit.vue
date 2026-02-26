<template>
  <AdminLayout :title="`Edit ${tier.tier_name} Tier`">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <Link
            :href="route('admin.starter-kit-tiers.index')"
            class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-flex items-center"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
            Back to Tiers
          </Link>
          <h1 class="text-2xl font-semibold text-gray-900">Edit {{ tier.tier_name }} Tier</h1>
          <p class="mt-1 text-sm text-gray-500">
            Configure tier details, pricing, and benefit assignments
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Tier Details Form -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Tier Details</h2>
              
              <form @submit.prevent="updateTierDetails" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Tier Name</label>
                  <input
                    v-model="tierForm.tier_name"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Description</label>
                  <textarea
                    v-model="tierForm.description"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Price (K)</label>
                  <input
                    v-model.number="tierForm.price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Storage (GB)</label>
                  <input
                    v-model.number="tierForm.storage_gb"
                    type="number"
                    min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Earning Potential (%)</label>
                  <input
                    v-model.number="tierForm.earning_potential_percentage"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                  <input
                    v-model.number="tierForm.sort_order"
                    type="number"
                    min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required
                  />
                </div>

                <div class="flex items-center">
                  <input
                    v-model="tierForm.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-900">Active</label>
                </div>

                <button
                  type="submit"
                  :disabled="tierForm.processing"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                >
                  {{ tierForm.processing ? 'Saving...' : 'Save Tier Details' }}
                </button>
              </form>
            </div>
          </div>

          <!-- Benefits Assignment -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Benefit Assignments</h2>

              <form @submit.prevent="updateBenefits">
                <!-- Starter Kit Benefits -->
                <div v-if="all_benefits.starter_kit" class="mb-6">
                  <h3 class="text-md font-medium text-gray-900 mb-3">Starter Kit Benefits</h3>
                  <div class="space-y-3">
                    <div
                      v-for="benefit in all_benefits.starter_kit"
                      :key="benefit.id"
                      class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
                    >
                      <input
                        v-model="benefitSelections[benefit.id].is_included"
                        type="checkbox"
                        class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                      <div class="ml-3 flex-1">
                        <label class="block text-sm font-medium text-gray-900">
                          {{ benefit.name }}
                        </label>
                        <p class="text-xs text-gray-500">{{ benefit.description }}</p>
                        
                        <!-- Limit Value Input -->
                        <div v-if="benefitSelections[benefit.id].is_included && benefit.unit" class="mt-2">
                          <label class="block text-xs text-gray-700">
                            Limit Value ({{ benefit.unit }})
                          </label>
                          <input
                            v-model.number="benefitSelections[benefit.id].limit_value"
                            type="number"
                            min="0"
                            class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Physical Items -->
                <div v-if="all_benefits.physical_item" class="mb-6">
                  <h3 class="text-md font-medium text-gray-900 mb-3">Physical Items</h3>
                  <div class="space-y-3">
                    <div
                      v-for="benefit in all_benefits.physical_item"
                      :key="benefit.id"
                      class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
                    >
                      <input
                        v-model="benefitSelections[benefit.id].is_included"
                        type="checkbox"
                        class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                      <div class="ml-3 flex-1">
                        <label class="block text-sm font-medium text-gray-900">
                          {{ benefit.name }}
                        </label>
                        <p class="text-xs text-gray-500">{{ benefit.description }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200">
                  <button
                    type="submit"
                    :disabled="benefitsForm.processing"
                    class="inline-flex justify-center items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                  >
                    {{ benefitsForm.processing ? 'Saving...' : 'Save Benefits' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Benefit {
  id: number;
  name: string;
  slug: string;
  category: string;
  benefit_type: string;
  description: string;
  icon: string;
  unit?: string;
}

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
}

const props = defineProps<{
  tier: Tier;
  all_benefits: Record<string, Benefit[]>;
  included_benefits: Record<number, { is_included: boolean; limit_value: number | null }>;
}>();

// Tier details form
const tierForm = useForm({
  tier_name: props.tier.tier_name,
  description: props.tier.description,
  price: props.tier.price,
  storage_gb: props.tier.storage_gb,
  earning_potential_percentage: props.tier.earning_potential_percentage,
  sort_order: props.tier.sort_order,
  is_active: props.tier.is_active,
});

// Benefits form
const benefitsForm = useForm({
  benefits: [] as Array<{ benefit_id: number; is_included: boolean; limit_value: number | null }>,
});

// Initialize benefit selections
const benefitSelections = reactive<Record<number, { is_included: boolean; limit_value: number | null }>>({});

// Populate benefit selections from all benefits
Object.values(props.all_benefits).flat().forEach((benefit) => {
  benefitSelections[benefit.id] = {
    is_included: props.included_benefits[benefit.id]?.is_included || false,
    limit_value: props.included_benefits[benefit.id]?.limit_value || null,
  };
});

const updateTierDetails = () => {
  tierForm.put(route('admin.starter-kit-tiers.update', props.tier.id), {
    preserveScroll: true,
  });
};

const updateBenefits = () => {
  // Convert benefitSelections to array format
  const benefitsArray = Object.entries(benefitSelections).map(([benefitId, data]) => ({
    benefit_id: parseInt(benefitId),
    is_included: data.is_included,
    limit_value: data.limit_value,
  }));

  benefitsForm.benefits = benefitsArray;

  benefitsForm.post(route('admin.starter-kit-tiers.update-benefits', props.tier.id), {
    preserveScroll: true,
  });
};
</script>
