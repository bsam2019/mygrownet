<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-900">
        LGR Packages Management
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Header Actions -->
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-medium text-gray-900">Manage LGR Packages</h3>
            <p class="mt-1 text-sm text-gray-600">
              Configure package amounts, daily rates, and earning cycles
            </p>
          </div>
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Package
          </button>
        </div>

        <!-- Packages Table -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Package
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Package Amount
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Daily LGR
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Duration
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Total Reward
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    ROI
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Users
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="pkg in packages" :key="pkg.id" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap px-6 py-4">
                    <div class="flex items-center">
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ pkg.name }}</div>
                        <div class="text-sm text-gray-500">{{ pkg.slug }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <div class="text-sm font-semibold text-gray-900">{{ pkg.formatted_package_amount }}</div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <div class="text-sm font-semibold text-green-600">{{ pkg.formatted_daily_rate }}</div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <div class="text-sm text-gray-900">{{ pkg.duration_days }} days</div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <div class="text-sm font-semibold text-blue-600">{{ pkg.formatted_total_reward }}</div>
                    <div v-if="!pkg.is_calculation_correct" class="text-xs text-red-500">
                      ⚠️ Calculation mismatch
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ pkg.roi_percentage.toFixed(2) }}%</div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <span
                      :class="[
                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                        pkg.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-gray-100 text-gray-800',
                      ]"
                    >
                      {{ pkg.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <div class="text-sm text-gray-900">{{ pkg.users_count }}</div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        @click="editPackage(pkg)"
                        class="text-blue-600 hover:text-blue-900"
                        title="Edit"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button
                        @click="toggleActive(pkg)"
                        :class="[
                          'hover:opacity-75',
                          pkg.is_active ? 'text-gray-600' : 'text-green-600',
                        ]"
                        :title="pkg.is_active ? 'Deactivate' : 'Activate'"
                      >
                        <svg v-if="pkg.is_active" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </button>
                      <button
                        v-if="pkg.users_count === 0"
                        @click="deletePackage(pkg)"
                        class="text-red-600 hover:text-red-900"
                        title="Delete"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Info Box -->
        <div class="rounded-lg bg-blue-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">Package Configuration</h3>
              <div class="mt-2 text-sm text-blue-700">
                <ul class="list-disc space-y-1 pl-5">
                  <li>Total Reward should equal Daily LGR × Duration Days</li>
                  <li>ROI is calculated as (Total Reward ÷ Package Amount) × 100</li>
                  <li>Packages with users cannot be deleted</li>
                  <li>Inactive packages won't be shown to new users</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <PackageModal
      v-if="showCreateModal || editingPackage"
      :package="editingPackage"
      @close="closeModal"
      @saved="handleSaved"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PackageModal from '@/components/Admin/LGR/PackageModal.vue';

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
  roi_percentage: number;
  is_calculation_correct: boolean;
  formatted_package_amount: string;
  formatted_daily_rate: string;
  formatted_total_reward: string;
  users_count: number;
}

interface Props {
  packages: Package[];
}

const props = defineProps<Props>();

const showCreateModal = ref(false);
const editingPackage = ref<Package | null>(null);

const editPackage = (pkg: Package) => {
  editingPackage.value = pkg;
};

const closeModal = () => {
  showCreateModal.value = false;
  editingPackage.value = null;
};

const handleSaved = () => {
  closeModal();
  router.reload({ only: ['packages'] });
};

const toggleActive = (pkg: Package) => {
  router.post(route('admin.lgr.packages.toggle-active', pkg.id), {}, {
    preserveScroll: true,
  });
};

const deletePackage = (pkg: Package) => {
  if (confirm(`Are you sure you want to delete "${pkg.name}"?`)) {
    router.delete(route('admin.lgr.packages.destroy', pkg.id), {
      preserveScroll: true,
    });
  }
};
</script>
