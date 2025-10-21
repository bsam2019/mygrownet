<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/layouts/AdminLayout.vue'

const form = useForm({
  year: new Date().getFullYear(),
  quarter: Math.ceil((new Date().getMonth() + 1) / 3),
  total_project_profit: '',
  distribution_method: 'bp_based',
  notes: '',
})

const submit = () => {
  form.post(route('admin.profit-sharing.store'))
}
</script>

<template>
  <Head title="Create Quarterly Profit Share" />
  <AdminLayout>
    <div class="p-6 max-w-2xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Quarterly Profit Share</h1>

      <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
            <input
              v-model.number="form.year"
              type="number"
              min="2020"
              max="2100"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Quarter</label>
            <select
              v-model.number="form.quarter"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option :value="1">Q1 (Jan-Mar)</option>
              <option :value="2">Q2 (Apr-Jun)</option>
              <option :value="3">Q3 (Jul-Sep)</option>
              <option :value="4">Q4 (Oct-Dec)</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Total Project Profit (K)
          </label>
          <input
            v-model.number="form.total_project_profit"
            type="number"
            step="0.01"
            min="0"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="100000.00"
          />
          <p class="mt-1 text-sm text-gray-500">
            60% will be distributed to members, 40% retained by company
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Distribution Method
          </label>
          <select
            v-model="form.distribution_method"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="bp_based">BP Based (Business Points)</option>
            <option value="level_based">Level Based (Professional Level)</option>
          </select>
          <p class="mt-1 text-sm text-gray-500">
            BP Based: Distribution proportional to member's BP<br>
            Level Based: Distribution weighted by professional level multipliers
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="Add any notes about this profit distribution..."
          ></textarea>
        </div>

        <div class="flex justify-end space-x-3">
          <a
            :href="route('admin.profit-sharing.index')"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </a>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Creating...' : 'Create & Calculate' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
