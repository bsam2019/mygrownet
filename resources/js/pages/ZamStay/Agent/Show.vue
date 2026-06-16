<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

defineProps<{
  agent: any;
}>();
</script>

<template>
  <Head :title="`${agent.business_name} - ZamStay Agent`" />

  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('zamstay.agents.index')" class="inline-flex items-center text-sm text-gray-500 hover:text-emerald-600 mb-6">
      &larr; All Agents
    </Link>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
      <div class="flex items-center gap-6 mb-6">
        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-xl font-bold">
          {{ agent.business_name?.charAt(0) || 'A' }}
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ agent.business_name }}</h1>
          <p class="text-gray-500">{{ agent.user?.name }}</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <span class="text-gray-500">Phone</span>
          <p class="font-medium text-gray-900">{{ agent.phone || 'N/A' }}</p>
        </div>
        <div>
          <span class="text-gray-500">License</span>
          <p class="font-medium text-gray-900">{{ agent.license_number || 'N/A' }}</p>
        </div>
        <div>
          <span class="text-gray-500">Commission Rate</span>
          <p class="font-medium text-gray-900">{{ agent.commission_rate }}%</p>
        </div>
        <div>
          <span class="text-gray-500">Status</span>
          <p class="font-medium" :class="agent.is_approved ? 'text-emerald-600' : 'text-amber-600'">
            {{ agent.is_approved ? 'Approved' : 'Pending' }}
          </p>
        </div>
      </div>

      <p v-if="agent.bio" class="mt-6 text-gray-600">{{ agent.bio }}</p>
    </div>
  </div>
</template>
