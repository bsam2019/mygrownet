<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <Link
            :href="route('admin.investor-accounts.index')"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
            Back to Investor Accounts
          </Link>
          <h1 class="mt-4 text-2xl font-bold text-gray-900">Edit Investor Account</h1>
          <p class="mt-1 text-sm text-gray-500">Update investor details (investment amount cannot be changed)</p>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name *</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Email *</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Equity Percentage *</label>
              <input
                v-model="form.equity_percentage"
                type="number"
                step="0.0001"
                min="0"
                max="100"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <p v-if="form.errors.equity_percentage" class="mt-1 text-sm text-red-600">
                {{ form.errors.equity_percentage }}
              </p>
            </div>

            <div class="bg-gray-50 p-4 rounded-md">
              <p class="text-sm text-gray-600">
                <strong>Investment Amount:</strong> K{{ formatNumber(account.investment_amount) }}
                <br />
                <strong>Investment Date:</strong> {{ formatDate(account.investment_date) }}
                <br />
                <strong>Status:</strong> {{ account.status }}
              </p>
              <p class="mt-2 text-xs text-gray-500">
                These fields cannot be edited. Create a new investment record if needed.
              </p>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <Link
              :href="route('admin.investor-accounts.index')"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {{ form.processing ? 'Updating...' : 'Update Account' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface InvestorAccount {
  id: number;
  name: string;
  email: string;
  investment_amount: number;
  investment_date: string;
  status: string;
  equity_percentage: number;
}

const props = defineProps<{
  account: InvestorAccount;
}>();

const form = useForm({
  name: props.account.name,
  email: props.account.email,
  equity_percentage: props.account.equity_percentage,
});

const submit = () => {
  form.put(route('admin.investor-accounts.update', props.account.id));
};

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};
</script>
