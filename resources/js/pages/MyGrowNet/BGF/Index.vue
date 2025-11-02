<template>
  <AppLayout title="Business Growth Fund">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Back Button (Mobile) -->
        <button
          @click="goBack"
          class="mb-4 flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 md:hidden"
        >
          <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back
        </button>
        
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-start justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Business Growth Fund</h1>
              <p class="mt-2 text-gray-600">
                Access short-term financing for verified business orders and opportunities
              </p>
            </div>
            <Link
              :href="route('bgf.about')"
              class="hidden md:flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition border border-blue-200"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Learn More
            </Link>
          </div>
          
          <!-- Quick Info Banner -->
          <div class="mt-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-600 p-4 rounded-r-lg">
            <div class="flex items-start gap-3">
              <svg class="h-6 w-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="flex-1">
                <p class="text-sm text-gray-700">
                  <strong>New to BGF?</strong> Learn how it works, eligibility requirements, and profit sharing structure.
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                  <Link :href="route('bgf.how-it-works')" class="text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline">
                    How It Works →
                  </Link>
                  <Link :href="route('bgf.terms')" class="text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline">
                    Terms & Conditions →
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Total Applications</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_applications }}</div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Active Projects</div>
            <div class="mt-2 text-3xl font-bold text-blue-600">{{ stats.active_projects }}</div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Total Funded</div>
            <div class="mt-2 text-3xl font-bold text-emerald-600">K{{ formatMoney(stats.total_funded) }}</div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Completed Projects</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.completed_projects }}</div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Link
            :href="route('mygrownet.bgf.create')"
            class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow p-6 text-center transition"
          >
            <div class="text-lg font-semibold">Apply for Funding</div>
            <div class="mt-2 text-sm opacity-90">Submit a new BGF application</div>
          </Link>
          
          <Link
            :href="route('mygrownet.bgf.applications')"
            class="bg-white hover:bg-gray-50 border-2 border-gray-200 rounded-lg shadow p-6 text-center transition"
          >
            <div class="text-lg font-semibold text-gray-900">My Applications</div>
            <div class="mt-2 text-sm text-gray-600">View all applications</div>
          </Link>
          
          <Link
            :href="route('mygrownet.bgf.projects')"
            class="bg-white hover:bg-gray-50 border-2 border-gray-200 rounded-lg shadow p-6 text-center transition"
          >
            <div class="text-lg font-semibold text-gray-900">My Projects</div>
            <div class="mt-2 text-sm text-gray-600">View active projects</div>
          </Link>
        </div>

        <!-- Recent Applications -->
        <div v-if="recentApplications.length > 0" class="bg-white rounded-lg shadow mb-8">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Applications</h2>
          </div>
          <div class="divide-y divide-gray-200">
            <div
              v-for="application in recentApplications"
              :key="application.id"
              class="px-6 py-4 hover:bg-gray-50 transition"
            >
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-medium text-gray-900">{{ application.business_name }}</div>
                  <div class="text-sm text-gray-500">{{ application.reference_number }}</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">K{{ formatMoney(application.amount_requested) }}</div>
                  <div>
                    <span
                      :class="getStatusClass(application.status)"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    >
                      {{ formatStatus(application.status) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Projects -->
        <div v-if="activeProjects.length > 0" class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Active Projects</h2>
          </div>
          <div class="divide-y divide-gray-200">
            <div
              v-for="project in activeProjects"
              :key="project.id"
              class="px-6 py-4 hover:bg-gray-50 transition"
            >
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-medium text-gray-900">{{ project.application.business_name }}</div>
                  <div class="text-sm text-gray-500">{{ project.project_number }}</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">K{{ formatMoney(project.approved_amount) }}</div>
                  <div class="text-xs text-gray-500">
                    Disbursed: K{{ formatMoney(project.total_disbursed) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Info Section -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-blue-900 mb-3">About Business Growth Fund</h3>
          <div class="text-sm text-blue-800 space-y-2">
            <p>✓ Short-term financing for verified business orders</p>
            <p>✓ Profit-sharing partnership (60-70% member, 30-40% MyGrowNet)</p>
            <p>✓ Funding up to K50,000 per project</p>
            <p>✓ Member contribution: 20-30% required</p>
            <p>✓ Completion period: 1-4 months</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';

defineProps<{
  stats: {
    total_applications: number;
    approved_applications: number;
    active_projects: number;
    completed_projects: number;
    total_funded: number;
    total_repaid: number;
  };
  recentApplications: any[];
  activeProjects: any[];
}>();

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    router.visit(route('dashboard'));
  }
};

const formatMoney = (amount: number) => {
  return new Intl.NumberFormat('en-ZM', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
};

const formatStatus = (status: string) => {
  return status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-blue-100 text-blue-800',
    under_review: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
