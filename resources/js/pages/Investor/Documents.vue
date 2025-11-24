<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
          <div>
            <Link
              :href="route('investor.dashboard')"
              class="text-sm text-gray-600 hover:text-gray-900 flex items-center mb-2"
            >
              <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
              Back to Dashboard
            </Link>
            <h1 class="text-2xl font-bold text-gray-900">Investment Documents</h1>
          </div>
          <Link
            :href="route('investor.logout')"
            method="post"
            as="button"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            Logout
          </Link>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-lg shadow">
        <!-- Document Categories -->
        <div class="divide-y divide-gray-200">
          <!-- Investment Agreement -->
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <DocumentTextIcon class="h-5 w-5 mr-2 text-blue-600" aria-hidden="true" />
              Investment Agreement
            </h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div class="flex items-center">
                  <DocumentIcon class="h-8 w-8 text-blue-600 mr-3" aria-hidden="true" />
                  <div>
                    <p class="text-sm font-medium text-gray-900">Convertible Investment Agreement</p>
                    <p class="text-xs text-gray-500">Signed on {{ formatDate(investor.investment_date) }}</p>
                  </div>
                </div>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                  Download
                </button>
              </div>
            </div>
          </div>

          <!-- Quarterly Reports -->
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <ChartBarIcon class="h-5 w-5 mr-2 text-green-600" aria-hidden="true" />
              Quarterly Reports
            </h3>
            <div class="space-y-3">
              <div class="text-center py-8 text-gray-500">
                <DocumentTextIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
                <p class="text-sm">No quarterly reports available yet</p>
                <p class="text-xs mt-1">Reports will be published quarterly</p>
              </div>
            </div>
          </div>

          <!-- Tax Documents -->
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <DocumentChartBarIcon class="h-5 w-5 mr-2 text-purple-600" aria-hidden="true" />
              Tax Documents
            </h3>
            <div class="space-y-3">
              <div class="text-center py-8 text-gray-500">
                <DocumentTextIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
                <p class="text-sm">No tax documents available yet</p>
                <p class="text-xs mt-1">Tax documents will be available annually</p>
              </div>
            </div>
          </div>

          <!-- Company Updates -->
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <NewspaperIcon class="h-5 w-5 mr-2 text-orange-600" aria-hidden="true" />
              Company Updates
            </h3>
            <div class="space-y-3">
              <div class="text-center py-8 text-gray-500">
                <DocumentTextIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
                <p class="text-sm">No updates available yet</p>
                <p class="text-xs mt-1">Company updates will be posted here</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Help Section -->
      <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex">
          <InformationCircleIcon class="h-6 w-6 text-blue-600 mr-3" aria-hidden="true" />
          <div>
            <h4 class="text-sm font-semibold text-blue-900 mb-1">Need a specific document?</h4>
            <p class="text-sm text-blue-800 mb-3">
              If you need a specific document or have questions about your investment documentation, please contact our investor relations team.
            </p>
            <a
              href="mailto:investors@mygrownet.com"
              class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700"
            >
              Contact Investor Relations
              <ArrowRightIcon class="h-4 w-4 ml-1" aria-hidden="true" />
            </a>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
  ArrowLeftIcon,
  DocumentTextIcon,
  ChartBarIcon,
  DocumentChartBarIcon,
  NewspaperIcon,
  InformationCircleIcon,
  ArrowRightIcon,
  DocumentIcon,
} from '@heroicons/vue/24/outline';

interface Investor {
  name: string;
  email: string;
  investment_date?: string;
}

const props = defineProps<{
  investor: Investor;
}>();

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};
</script>
