<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import { Cog6ToothIcon } from '@heroicons/vue/24/outline';
</script>

<template>
  <Head title="Settings - Marketplace Admin" />

  <MarketplaceAdminLayout title="Marketplace Settings">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12">
      <div class="text-center">
        <Cog6ToothIcon class="mx-auto h-16 w-16 text-gray-400" aria-hidden="true" />
        <h3 class="mt-4 text-lg font-semibold text-gray-900">Marketplace Settings</h3>
        <p class="mt-2 text-gray-600">
          Configuration options coming soon
        </p>
        <div class="mt-6 text-sm text-gray-500">
          <p>This section will include settings for:</p>
          <ul class="mt-2 space-y-1">
            <li>• Commission rates and fees</li>
            <li>• Payment gateway configuration</li>
            <li>• Shipping and delivery options</li>
            <li>• Seller approval requirements</li>
            <li>• Product moderation rules</li>
            <li>• Email notification templates</li>
          </ul>
        </div>
      </div>
    </div>
  </MarketplaceAdminLayout>
</template>
