<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

defineProps<{
  agents: any;
}>();
</script>

<template>
  <Head title="Tour Agents - ZamStay" />

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Tour Agents</h1>

    <div v-if="agents?.data?.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="agent in agents.data" :key="agent.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white font-bold">
            {{ agent.business_name?.charAt(0) || 'A' }}
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ agent.business_name }}</h3>
            <p class="text-sm text-gray-500">{{ agent.user?.name }}</p>
          </div>
        </div>
        <p v-if="agent.bio" class="text-sm text-gray-600 mb-4 line-clamp-2">{{ agent.bio }}</p>
        <div class="flex items-center justify-between text-sm">
          <span class="text-gray-500">{{ agent.phone || 'No phone' }}</span>
          <Link :href="route('zamstay.agents.show', agent.id)" class="text-emerald-600 hover:text-emerald-700 font-medium">
            View Profile
          </Link>
        </div>
      </div>
    </div>
    <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">
      No agents registered yet.
    </div>
  </div>
</template>
