<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon, PencilIcon, StarIcon } from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';

interface Subcontractor {
  id: number;
  name: string;
  company_name: string;
  trade: string;
  email: string;
  phone: string;
  address: string;
  hourly_rate: number;
  average_rating: number;
  assignments: Array<any>;
  payments: Array<any>;
  ratings: Array<any>;
}

const props = defineProps<{
  subcontractor: Subcontractor;
  stats: {
    total_assignments: number;
    active_assignments: number;
    total_paid: number;
    average_rating: number;
  };
}>();
</script>

<template>
  <Head :title="subcontractor.name" />
  
  <CMSLayout>
    <div class="space-y-6">
      <div class="flex items-start justify-between">
        <div class="flex items-start gap-4">
          <Link :href="route('cms.subcontractors.index')" class="p-2 hover:bg-gray-100 rounded-lg">
            <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ subcontractor.name }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ subcontractor.company_name }}</p>
          </div>
        </div>
        <Link :href="route('cms.subcontractors.edit', subcontractor.id)" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
          <PencilIcon class="h-5 w-5" aria-hidden="true" />
          Edit
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-500">Rating</div>
          <div class="mt-2 flex items-center gap-2">
            <div class="flex items-center">
              <StarIconSolid v-for="i in 5" :key="i" :class="['h-5 w-5', i <= stats.average_rating ? 'text-yellow-400' : 'text-gray-300']" aria-hidden="true" />
            </div>
            <span class="text-lg font-bold text-gray-900">{{ stats.average_rating.toFixed(1) }}</span>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-500">Active Jobs</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ stats.active_assignments }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-500">Total Jobs</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ stats.total_assignments }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-500">Total Paid</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">K{{ stats.total_paid?.toLocaleString() }}</div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Assignments</h2>
            <div v-if="subcontractor.assignments.length === 0" class="text-center py-8 text-gray-500">
              No assignments yet
            </div>
            <div v-else class="space-y-3">
              <div v-for="assignment in subcontractor.assignments" :key="assignment.id" class="p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-gray-900">{{ assignment.project?.name || assignment.job?.title }}</div>
                    <div class="text-sm text-gray-500">{{ assignment.scope_of_work }}</div>
                  </div>
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                    {{ assignment.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment History</h2>
            <div v-if="subcontractor.payments.length === 0" class="text-center py-8 text-gray-500">
              No payments recorded
            </div>
            <div v-else class="space-y-2">
              <div v-for="payment in subcontractor.payments" :key="payment.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                  <div class="text-sm text-gray-500">{{ payment.payment_date }}</div>
                  <div class="text-xs text-gray-400">{{ payment.payment_method }}</div>
                </div>
                <div class="font-medium text-gray-900">K{{ payment.amount.toLocaleString() }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Contact Details</h2>
            <div>
              <div class="text-sm text-gray-500">Trade</div>
              <div class="mt-1 font-medium text-gray-900">{{ subcontractor.trade }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500">Email</div>
              <div class="mt-1 text-gray-900">{{ subcontractor.email || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500">Phone</div>
              <div class="mt-1 text-gray-900">{{ subcontractor.phone || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500">Address</div>
              <div class="mt-1 text-gray-900">{{ subcontractor.address || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500">Hourly Rate</div>
              <div class="mt-1 font-medium text-gray-900">K{{ subcontractor.hourly_rate?.toLocaleString() || 'N/A' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>
