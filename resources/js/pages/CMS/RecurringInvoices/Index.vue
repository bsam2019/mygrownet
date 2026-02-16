<template>
  <CMSLayout page-title="Recurring Invoices">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Recurring Invoices</h1>
          <p class="text-sm text-gray-600 mt-1">Automate invoice generation with recurring templates</p>
        </div>
        <Link
          :href="route('cms.recurring-invoices.create')"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Recurring Invoice
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center gap-4">
          <div class="flex-1">
            <label class="text-sm font-medium text-gray-700 mb-1 block">Status</label>
            <select
              v-model="selectedStatus"
              @change="filterByStatus"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="paused">Paused</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Recurring Invoices List -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div v-if="recurringInvoices.length === 0" class="p-12 text-center">
          <DocumentTextIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" aria-hidden="true" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No recurring invoices</h3>
          <p class="text-gray-600 mb-4">Create your first recurring invoice template to automate billing</p>
          <Link
            :href="route('cms.recurring-invoices.create')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <PlusIcon class="h-5 w-5" aria-hidden="true" />
            Create Recurring Invoice
          </Link>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generated</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="invoice in recurringInvoices"
                :key="invoice.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4">
                  <Link
                    :href="route('cms.recurring-invoices.show', invoice.id)"
                    class="text-sm font-medium text-blue-600 hover:text-blue-700"
                  >
                    {{ invoice.title }}
                  </Link>
                  <p v-if="invoice.description" class="text-xs text-gray-500 mt-1">{{ invoice.description }}</p>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ invoice.customer?.name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ formatFrequency(invoice.frequency, invoice.interval) }}
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                  K{{ formatNumber(invoice.total) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ formatDate(invoice.next_generation_date) }}
                </td>
                <td class="px-6 py-4">
                  <span :class="getStatusClass(invoice.status)">
                    {{ invoice.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ invoice.occurrences_count }}
                  <span v-if="invoice.max_occurrences" class="text-gray-500">/ {{ invoice.max_occurrences }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <Menu as="div" class="relative inline-block text-left">
                    <MenuButton class="p-2 rounded-lg hover:bg-gray-100 transition">
                      <EllipsisVerticalIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </MenuButton>
                    <MenuItems class="absolute right-0 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                      <div class="py-1">
                        <MenuItem v-slot="{ active }">
                          <Link
                            :href="route('cms.recurring-invoices.show', invoice.id)"
                            :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']"
                          >
                            View Details
                          </Link>
                        </MenuItem>
                        <MenuItem v-if="invoice.status === 'active'" v-slot="{ active }">
                          <button
                            @click="generateNow(invoice.id)"
                            :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                          >
                            Generate Now
                          </button>
                        </MenuItem>
                        <MenuItem v-if="invoice.status === 'active'" v-slot="{ active }">
                          <button
                            @click="pauseInvoice(invoice.id)"
                            :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                          >
                            Pause
                          </button>
                        </MenuItem>
                        <MenuItem v-if="invoice.status === 'paused'" v-slot="{ active }">
                          <button
                            @click="resumeInvoice(invoice.id)"
                            :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                          >
                            Resume
                          </button>
                        </MenuItem>
                        <MenuItem v-slot="{ active }">
                          <Link
                            :href="route('cms.recurring-invoices.edit', invoice.id)"
                            :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']"
                          >
                            Edit
                          </Link>
                        </MenuItem>
                        <MenuItem v-if="invoice.status !== 'cancelled'" v-slot="{ active }">
                          <button
                            @click="cancelInvoice(invoice.id)"
                            :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-red-600']"
                          >
                            Cancel
                          </button>
                        </MenuItem>
                      </div>
                    </MenuItems>
                  </Menu>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { PlusIcon, DocumentTextIcon, EllipsisVerticalIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';

interface Props {
  recurringInvoices: any[];
  filters: {
    status?: string;
  };
}

const props = defineProps<Props>();

const selectedStatus = ref(props.filters.status || '');

const filterByStatus = () => {
  router.get(route('cms.recurring-invoices.index'), {
    status: selectedStatus.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const formatFrequency = (frequency: string, interval: number) => {
  const freq = interval > 1 ? `Every ${interval} ${frequency}` : frequency.charAt(0).toUpperCase() + frequency.slice(1);
  return freq;
};

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-ZM', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-ZM', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getStatusClass = (status: string) => {
  const classes = 'px-2 py-1 text-xs font-medium rounded-full';
  switch (status) {
    case 'active':
      return `${classes} bg-green-100 text-green-800`;
    case 'paused':
      return `${classes} bg-yellow-100 text-yellow-800`;
    case 'completed':
      return `${classes} bg-blue-100 text-blue-800`;
    case 'cancelled':
      return `${classes} bg-red-100 text-red-800`;
    default:
      return `${classes} bg-gray-100 text-gray-800`;
  }
};

const generateNow = (id: number) => {
  if (confirm('Generate invoice now?')) {
    router.post(route('cms.recurring-invoices.generate', id));
  }
};

const pauseInvoice = (id: number) => {
  router.post(route('cms.recurring-invoices.pause', id));
};

const resumeInvoice = (id: number) => {
  router.post(route('cms.recurring-invoices.resume', id));
};

const cancelInvoice = (id: number) => {
  if (confirm('Are you sure you want to cancel this recurring invoice? This action cannot be undone.')) {
    router.post(route('cms.recurring-invoices.cancel', id));
  }
};
</script>
