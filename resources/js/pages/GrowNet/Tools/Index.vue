<template>
  <MemberLayout>
    <div class="max-w-7xl mx-auto p-4 sm:p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Business Tools</h1>
        <p class="mt-2 text-gray-600">Free tools to help you run your business</p>
      </div>

      <!-- Free Public Tools Section -->
      <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Free Tools</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Quick Invoice Generator -->
          <a
            href="/quick-invoice"
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all block group"
          >
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-amber-200 transition-colors">
              <DocumentTextIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Quick Invoice</h3>
            <p class="text-sm text-gray-600 mb-3">Create professional invoices, quotations, receipts & delivery notes instantly. No account required.</p>
            <ul class="text-xs text-gray-500 space-y-1 mb-4">
              <li class="flex items-center gap-1">
                <CheckIcon class="h-3 w-3 text-green-500" aria-hidden="true" />
                5 professional templates
              </li>
              <li class="flex items-center gap-1">
                <CheckIcon class="h-3 w-3 text-green-500" aria-hidden="true" />
                Share via WhatsApp, Email, PDF
              </li>
              <li class="flex items-center gap-1">
                <CheckIcon class="h-3 w-3 text-green-500" aria-hidden="true" />
                Add logo & signature
              </li>
            </ul>
            <div class="flex items-center justify-between">
              <span class="text-sm text-amber-600 font-medium group-hover:text-amber-700">Open Tool →</span>
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Free</span>
            </div>
          </a>

          <!-- Coming Soon Placeholder -->
          <div class="bg-gray-50 rounded-lg border-2 border-dashed border-gray-200 p-6 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
              <PlusIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
            </div>
            <h3 class="text-lg font-semibold text-gray-400 mb-2">More Coming Soon</h3>
            <p class="text-sm text-gray-400">We're building more free tools to help your business grow</p>
          </div>
        </div>
      </div>

      <!-- Info Section -->
      <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <InformationCircleIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
          </div>
          <div>
            <h3 class="font-semibold text-blue-900 mb-1">About Our Free Tools</h3>
            <p class="text-sm text-blue-700">
              These tools are available to everyone, no MyGrowNet membership required. 
              Create professional business documents and share them instantly with your clients.
            </p>
          </div>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { 
  DocumentTextIcon, 
  PlusIcon,
  CheckIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline';
</script>
