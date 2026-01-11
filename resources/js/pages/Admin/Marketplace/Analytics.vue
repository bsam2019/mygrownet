<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import { ChartBarIcon } from '@heroicons/vue/24/outline';
</script>

<template>
  <Head title="Analytics - Marketplace Admin" />

  <MarketplaceAdminLayout title="Marketplace Analytics">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12">
      <div class="text-center">
        <ChartBarIcon class="mx-auto h-16 w-16 text-gray-400" aria-hidden="true" />
        <h3 class="mt-4 text-lg font-semibold text-gray-900">Analytics Dashboard</h3>
        <p class="mt-2 text-gray-600">
          Comprehensive marketplace analytics coming soon
        </p>
        <div class="mt-6 text-sm text-gray-500">
          <p>This section will include:</p>
          <ul class="mt-2 space-y-1">
            <li>• Sales trends and revenue charts</li>
            <li>• Top performing products and sellers</li>
            <li>• Customer behavior analytics</li>
            <li>• Order fulfillment metrics</li>
            <li>• Dispute resolution statistics</li>
          </ul>
        </div>
      </div>
    </div>
  </MarketplaceAdminLayout>
</template>
