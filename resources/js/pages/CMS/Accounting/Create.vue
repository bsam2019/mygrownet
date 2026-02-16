<template>
  <CMSLayout>
    <div class="p-6 max-w-3xl mx-auto">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new account to your chart of accounts</p>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        <!-- Account Code -->
        <div>
          <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
            Account Code <span class="text-red-500">*</span>
          </label>
          <input
            id="code"
            v-model="form.code"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="e.g., 1500"
          />
          <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
        </div>

        <!-- Account Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Account Name <span class="text-red-500">*</span>
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="e.g., Equipment"
          />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>

        <!-- Account Type -->
        <div>
          <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
            Account Type <span class="text-red-500">*</span>
          </label>
          <select
            id="type"
            v-model="form.type"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Select type...</option>
            <option v-for="type in accountTypes" :key="type.value" :value="type.value">
              {{ type.label }}
            </option>
          </select>
          <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
        </div>

        <!-- Category -->
        <div>
          <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
            Category
          </label>
          <input
            id="category"
            v-model="form.category"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="e.g., Fixed Assets"
          />
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            Description
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Optional description..."
          ></textarea>
        </div>

        <!-- Opening Balance -->
        <div>
          <label for="opening_balance" class="block text-sm font-medium text-gray-700 mb-2">
            Opening Balance
          </label>
          <input
            id="opening_balance"
            v-model="form.opening_balance"
            type="number"
            step="0.01"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="0.00"
          />
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
          <Link
            :href="route('cms.accounting.index')"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
          >
            {{ form.processing ? 'Creating...' : 'Create Account' }}
          </button>
        </div>
      </form>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'

interface Props {
  accountTypes: Array<{ value: string; label: string }>
}

defineProps<Props>()

const form = useForm({
  code: '',
  name: '',
  type: '',
  category: '',
  description: '',
  opening_balance: 0,
})

const submit = () => {
  form.post(route('cms.accounting.store'))
}
</script>
