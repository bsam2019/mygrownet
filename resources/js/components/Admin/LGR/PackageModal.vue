<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
    <div class="flex min-h-screen items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/50 transition-opacity" @click="$emit('close')"></div>
      
      <div class="relative w-full max-w-2xl rounded-lg bg-white shadow-xl">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ package ? 'Edit Package' : 'Create New Package' }}
          </h3>
          <button
            @click="$emit('close')"
            class="rounded-lg p-1 hover:bg-gray-100"
            aria-label="Close modal"
          >
            <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="p-6 space-y-6">
          <!-- Basic Info -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Package Name</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Package 1"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Slug</label>
              <input
                v-model="form.slug"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="package-1"
              />
              <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
            </div>
          </div>

          <!-- Financial Details -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Package Amount (K)</label>
              <input
                v-model.number="form.package_amount"
                type="number"
                step="0.01"
                min="0"
                required
                @input="calculateTotalReward"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="300.00"
              />
              <p v-if="form.errors.package_amount" class="mt-1 text-sm text-red-600">{{ form.errors.package_amount }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Daily LGR Rate (K)</label>
              <input
                v-model.number="form.daily_lgr_rate"
                type="number"
                step="0.01"
                min="0"
                required
                @input="calculateTotalReward"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="10.00"
              />
              <p v-if="form.errors.daily_lgr_rate" class="mt-1 text-sm text-red-600">{{ form.errors.daily_lgr_rate }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Duration (Days)</label>
              <input
                v-model.number="form.duration_days"
                type="number"
                min="1"
                max="365"
                required
                @input="calculateTotalReward"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="50"
              />
              <p v-if="form.errors.duration_days" class="mt-1 text-sm text-red-600">{{ form.errors.duration_days }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Total Reward (K)</label>
              <input
                v-model.number="form.total_reward"
                type="number"
                step="0.01"
                min="0"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="500.00"
              />
              <p v-if="form.errors.total_reward" class="mt-1 text-sm text-red-600">{{ form.errors.total_reward }}</p>
              <p v-if="calculatedReward" class="mt-1 text-xs text-gray-500">
                Calculated: K{{ calculatedReward.toFixed(2) }}
              </p>
            </div>
          </div>

          <!-- Calculated ROI -->
          <div v-if="calculatedRoi" class="rounded-lg bg-blue-50 p-4">
            <div class="flex items-center gap-2">
              <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
              <span class="text-sm font-medium text-blue-900">
                ROI: {{ calculatedRoi.toFixed(2) }}%
              </span>
            </div>
          </div>

          <!-- Settings -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Sort Order</label>
              <input
                v-model.number="form.sort_order"
                type="number"
                min="0"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="0"
              />
            </div>

            <div class="flex items-center pt-6">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <label class="ml-2 block text-sm text-gray-700">Active</label>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="Package description..."
            ></textarea>
          </div>

          <!-- Features -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Features</label>
            <div class="space-y-2">
              <div v-for="(feature, index) in form.features" :key="index" class="flex gap-2">
                <input
                  v-model="form.features[index]"
                  type="text"
                  class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  placeholder="Feature description"
                />
                <button
                  type="button"
                  @click="removeFeature(index)"
                  class="rounded-md bg-red-50 px-3 py-2 text-red-600 hover:bg-red-100"
                >
                  Remove
                </button>
              </div>
              <button
                type="button"
                @click="addFeature"
                class="text-sm text-blue-600 hover:text-blue-800"
              >
                + Add Feature
              </button>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
            <button
              type="button"
              @click="$emit('close')"
              class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : (package ? 'Update Package' : 'Create Package') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface Package {
  id: number;
  name: string;
  slug: string;
  package_amount: number;
  daily_lgr_rate: number;
  duration_days: number;
  total_reward: number;
  is_active: boolean;
  sort_order: number;
  description: string | null;
  features: string[] | null;
}

interface Props {
  package?: Package | null;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'saved']);

const form = useForm({
  name: props.package?.name || '',
  slug: props.package?.slug || '',
  package_amount: props.package?.package_amount || 0,
  daily_lgr_rate: props.package?.daily_lgr_rate || 0,
  duration_days: props.package?.duration_days || 70,
  total_reward: props.package?.total_reward || 0,
  is_active: props.package?.is_active ?? true,
  sort_order: props.package?.sort_order || 0,
  description: props.package?.description || '',
  features: props.package?.features || [],
});

const calculatedReward = computed(() => {
  if (form.daily_lgr_rate && form.duration_days) {
    return form.daily_lgr_rate * form.duration_days;
  }
  return null;
});

const calculatedRoi = computed(() => {
  if (form.package_amount && form.total_reward) {
    return (form.total_reward / form.package_amount) * 100;
  }
  return null;
});

const calculateTotalReward = () => {
  if (form.daily_lgr_rate && form.duration_days) {
    form.total_reward = form.daily_lgr_rate * form.duration_days;
  }
};

const addFeature = () => {
  form.features.push('');
};

const removeFeature = (index: number) => {
  form.features.splice(index, 1);
};

const submit = () => {
  const url = props.package
    ? route('admin.lgr.packages.update', props.package.id)
    : route('admin.lgr.packages.store');

  const method = props.package ? 'put' : 'post';

  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      emit('saved');
    },
  });
};

// Auto-generate slug from name
watch(() => form.name, (newName) => {
  if (!props.package) {
    form.slug = newName.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
  }
});
</script>
