<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">LGR System Settings</h1>
          <p class="mt-1 text-sm text-gray-600">
            Configure Loyalty Growth Reward system parameters
          </p>
        </div>

        <form @submit.prevent="saveSettings">
          <!-- General Settings -->
          <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">General Settings</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div v-for="setting in settings.general" :key="setting.key" class="flex items-start">
                <div class="flex-1">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ setting.label }}
                  </label>
                  <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                </div>
                <div class="ml-4">
                  <input
                    v-if="setting.type === 'boolean'"
                    :id="setting.key"
                    v-model="form[setting.key]"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <input
                    v-else-if="setting.type === 'integer'"
                    :id="setting.key"
                    v-model.number="form[setting.key]"
                    type="number"
                    step="1"
                    class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  />
                  <input
                    v-else-if="setting.type === 'decimal'"
                    :id="setting.key"
                    v-model.number="form[setting.key]"
                    type="number"
                    step="0.01"
                    class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  />
                  <input
                    v-else
                    :id="setting.key"
                    v-model="form[setting.key]"
                    type="text"
                    class="w-64 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Withdrawal Settings -->
          <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Withdrawal Settings</h2>
              <p class="text-sm text-gray-600 mt-1">Configure LGR withdrawal parameters</p>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div v-for="setting in settings.withdrawal" :key="setting.key" class="flex items-start">
                <div class="flex-1">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ setting.label }}
                  </label>
                  <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                </div>
                <div class="ml-4">
                  <input
                    v-if="setting.type === 'boolean'"
                    :id="setting.key"
                    v-model="form[setting.key]"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <div v-else class="flex items-center">
                    <input
                      :id="setting.key"
                      v-model.number="form[setting.key]"
                      type="number"
                      step="0.01"
                      class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="setting.key.includes('percentage')" class="ml-2 text-sm text-gray-600">%</span>
                    <span v-else class="ml-2 text-sm text-gray-600">K</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Transfer Settings -->
          <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Transfer Settings</h2>
              <p class="text-sm text-gray-600 mt-1">Configure LGR to Wallet transfer parameters</p>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div v-for="setting in settings.transfer" :key="setting.key" class="flex items-start">
                <div class="flex-1">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ setting.label }}
                  </label>
                  <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                </div>
                <div class="ml-4">
                  <input
                    v-if="setting.type === 'boolean'"
                    :id="setting.key"
                    v-model="form[setting.key]"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <div v-else class="flex items-center">
                    <input
                      :id="setting.key"
                      v-model.number="form[setting.key]"
                      type="number"
                      step="0.01"
                      class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="setting.key.includes('percentage')" class="ml-2 text-sm text-gray-600">%</span>
                    <span v-else class="ml-2 text-sm text-gray-600">K</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Award Settings -->
          <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Manual Award Settings</h2>
              <p class="text-sm text-gray-600 mt-1">Configure manual LGR award limits</p>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div v-for="setting in settings.awards" :key="setting.key" class="flex items-start">
                <div class="flex-1">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ setting.label }}
                  </label>
                  <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                </div>
                <div class="ml-4 flex items-center">
                  <input
                    :id="setting.key"
                    v-model.number="form[setting.key]"
                    type="number"
                    step="0.01"
                    class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-600">K</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Save Button -->
          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              <span v-if="processing">Saving...</span>
              <span v-else>Save Settings</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Setting {
  key: string;
  value: any;
  type: string;
  label: string;
  description: string;
}

interface Settings {
  general?: Setting[];
  withdrawal?: Setting[];
  transfer?: Setting[];
  awards?: Setting[];
}

const props = defineProps<{
  settings: Settings;
}>();

const processing = ref(false);

// Initialize form with current settings
const form = reactive<Record<string, any>>({});

// Populate form with current values
Object.entries(props.settings).forEach(([group, groupSettings]) => {
  groupSettings.forEach((setting: Setting) => {
    form[setting.key] = setting.value;
  });
});

const saveSettings = () => {
  processing.value = true;

  const settingsArray = Object.entries(form).map(([key, value]) => ({
    key,
    value: value.toString(),
  }));

  router.post(route('admin.lgr.settings.update'), {
    settings: settingsArray,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      processing.value = false;
    },
    onError: () => {
      processing.value = false;
    },
  });
};
</script>
