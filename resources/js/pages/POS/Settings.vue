<script setup lang="ts">
import POSLayout from '@/layouts/POSLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
  Cog6ToothIcon,
  PrinterIcon,
  CurrencyDollarIcon,
  ReceiptPercentIcon,
  BuildingStorefrontIcon,
  BoltIcon,
  PlusIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: POSLayout });

interface POSSettings {
  business_name?: string;
  currency?: string;
  default_tax_rate?: number;
  receipt_header?: string;
  receipt_footer?: string;
  auto_print_receipt?: boolean;
  require_customer?: boolean;
  allow_credit_sales?: boolean;
  track_inventory?: boolean;
}

interface InventoryItem {
  id: number;
  name: string;
  sku?: string;
  selling_price: number;
  quantity_in_stock: number;
}

interface QuickProduct {
  id: number;
  inventory_item_id: number;
  display_order: number;
  inventory_item?: InventoryItem;
}

const props = defineProps<{
  settings: POSSettings;
  quickProducts?: QuickProduct[];
  inventoryItems?: InventoryItem[];
}>();

const form = ref<POSSettings>({
  business_name: props.settings?.business_name ?? '',
  currency: props.settings?.currency ?? 'ZMW',
  default_tax_rate: props.settings?.default_tax_rate ?? 16,
  receipt_header: props.settings?.receipt_header ?? '',
  receipt_footer: props.settings?.receipt_footer ?? 'Thank you for your business!',
  auto_print_receipt: props.settings?.auto_print_receipt ?? false,
  require_customer: props.settings?.require_customer ?? false,
  allow_credit_sales: props.settings?.allow_credit_sales ?? false,
  track_inventory: props.settings?.track_inventory ?? true,
});
</script>

<template>
  <Head title="POS Settings" />

  <div class="space-y-6 p-4 sm:p-6 lg:p-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">POS Settings</h1>
        <p class="mt-1 text-sm text-gray-500">Configure your point of sale system</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Store Settings -->
      <div class="rounded-lg border border-gray-200 bg-white p-6">
        <div class="mb-4 flex items-center gap-2">
          <BuildingStorefrontIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
          <h2 class="text-lg font-medium text-gray-900">Store Information</h2>
        </div>
        <div class="space-y-4">
          <div>
            <label for="business_name" class="block text-sm font-medium text-gray-700">Business Name</label>
            <input
              id="business_name"
              v-model="form.business_name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            />
          </div>
          <div>
            <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
            <select
              id="currency"
              v-model="form.currency"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            >
              <option value="ZMW">ZMW - Zambian Kwacha</option>
              <option value="USD">USD - US Dollar</option>
              <option value="ZAR">ZAR - South African Rand</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tax Settings -->
      <div class="rounded-lg border border-gray-200 bg-white p-6">
        <div class="mb-4 flex items-center gap-2">
          <ReceiptPercentIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
          <h2 class="text-lg font-medium text-gray-900">Tax Settings</h2>
        </div>
        <div class="space-y-4">
          <div>
            <label for="tax_rate" class="block text-sm font-medium text-gray-700">Default Tax Rate (%)</label>
            <input
              id="tax_rate"
              v-model.number="form.default_tax_rate"
              type="number"
              min="0"
              max="100"
              step="0.5"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            />
          </div>
        </div>
      </div>

      <!-- Receipt Settings -->
      <div class="rounded-lg border border-gray-200 bg-white p-6">
        <div class="mb-4 flex items-center gap-2">
          <PrinterIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
          <h2 class="text-lg font-medium text-gray-900">Receipt Settings</h2>
        </div>
        <div class="space-y-4">
          <div>
            <label for="receipt_header" class="block text-sm font-medium text-gray-700">Receipt Header</label>
            <textarea
              id="receipt_header"
              v-model="form.receipt_header"
              rows="2"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              placeholder="Text to appear at the top of receipts"
            />
          </div>
          <div>
            <label for="receipt_footer" class="block text-sm font-medium text-gray-700">Receipt Footer</label>
            <textarea
              id="receipt_footer"
              v-model="form.receipt_footer"
              rows="2"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              placeholder="Text to appear at the bottom of receipts"
            />
          </div>
          <div class="flex items-center">
            <input
              id="auto_print"
              v-model="form.auto_print_receipt"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <label for="auto_print" class="ml-2 block text-sm text-gray-700">
              Auto-print receipt after sale
            </label>
          </div>
        </div>
      </div>

      <!-- Sales Settings -->
      <div class="rounded-lg border border-gray-200 bg-white p-6">
        <div class="mb-4 flex items-center gap-2">
          <CurrencyDollarIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
          <h2 class="text-lg font-medium text-gray-900">Sales Settings</h2>
        </div>
        <div class="space-y-4">
          <div class="flex items-center">
            <input
              id="require_customer"
              v-model="form.require_customer"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <label for="require_customer" class="ml-2 block text-sm text-gray-700">
              Require customer for each sale
            </label>
          </div>
          <div class="flex items-center">
            <input
              id="allow_credit"
              v-model="form.allow_credit_sales"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <label for="allow_credit" class="ml-2 block text-sm text-gray-700">
              Allow credit sales
            </label>
          </div>
          <div class="flex items-center">
            <input
              id="track_inventory"
              v-model="form.track_inventory"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <label for="track_inventory" class="ml-2 block text-sm text-gray-700">
              Track inventory on sales
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Products Section -->
    <div class="rounded-lg border border-gray-200 bg-white p-6">
      <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <BoltIcon class="h-5 w-5 text-amber-500" aria-hidden="true" />
          <h2 class="text-lg font-medium text-gray-900">Quick Products</h2>
        </div>
        <p class="text-sm text-gray-500">Pin frequently sold items for quick access on the terminal</p>
      </div>
      
      <div v-if="quickProducts && quickProducts.length > 0" class="mb-4">
        <div class="flex flex-wrap gap-2">
          <div
            v-for="qp in quickProducts"
            :key="qp.id"
            class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-sm text-blue-700"
          >
            <span>{{ qp.inventory_item?.name || 'Unknown' }}</span>
            <button
              type="button"
              class="rounded-full p-0.5 hover:bg-blue-100"
              aria-label="Remove quick product"
            >
              <XMarkIcon class="h-4 w-4" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
      
      <div v-else class="rounded-lg border-2 border-dashed border-gray-200 p-8 text-center">
        <BoltIcon class="mx-auto h-10 w-10 text-gray-300" aria-hidden="true" />
        <p class="mt-2 text-sm text-gray-500">No quick products configured</p>
        <p class="text-xs text-gray-400">Add items from your inventory for quick access</p>
      </div>

      <div v-if="inventoryItems && inventoryItems.length > 0" class="mt-4">
        <label for="add_quick_product" class="block text-sm font-medium text-gray-700">Add from Inventory</label>
        <select
          id="add_quick_product"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        >
          <option value="">Select an item to add...</option>
          <option v-for="item in inventoryItems" :key="item.id" :value="item.id">
            {{ item.name }} ({{ item.sku || 'No SKU' }}) - K{{ item.selling_price }}
          </option>
        </select>
      </div>
      
      <div v-else class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-700">
        No inventory items available. Add items to your inventory first.
      </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
      >
        <Cog6ToothIcon class="h-4 w-4" aria-hidden="true" />
        Save Settings
      </button>
    </div>
  </div>
</template>
