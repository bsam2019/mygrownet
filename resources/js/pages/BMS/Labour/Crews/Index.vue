<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { PlusIcon, UserGroupIcon } from '@heroicons/vue/24/outline';

interface Crew {
  id: number;
  name: string;
  specialization: string;
  supervisor: { name: string };
  members_count: number;
}

const props = defineProps<{
  crews: {
    data: Crew[];
  };
}>();
</script>

<template>
  <Head title="Crews" />
  
  <CMSLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Crews</h1>
          <p class="mt-1 text-sm text-gray-500">Manage labour crews and assignments</p>
        </div>
        <Link
          :href="route('cms.labour.crews.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Create Crew
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link
          v-for="crew in crews.data"
          :key="crew.id"
          :href="route('cms.labour.crews.show', crew.id)"
          class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-center gap-3 mb-4">
            <div class="p-3 bg-blue-100 rounded-lg">
              <UserGroupIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">{{ crew.name }}</h3>
              <p class="text-sm text-gray-500">{{ crew.specialization }}</p>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Supervisor</span>
              <span class="font-medium text-gray-900">{{ crew.supervisor.name }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Members</span>
              <span class="font-medium text-gray-900">{{ crew.members_count }}</span>
            </div>
          </div>
        </Link>
      </div>
    </div>
  </CMSLayout>
</template>
