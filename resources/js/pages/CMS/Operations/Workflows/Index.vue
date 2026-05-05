<template>
  <CMSLayout page-title="Workflows">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Workflows</h1>
          <p class="mt-1 text-sm text-gray-500">Manage task workflows and stages</p>
        </div>
      </div>

      <!-- Workflows List -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="workflow in workflows" :key="workflow.id" class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ workflow.name }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ workflow.description }}</p>
              </div>
              <span class="px-3 py-1 text-sm font-medium text-blue-700 bg-blue-100 rounded-full">
                {{ workflow.tasks_count }} tasks
              </span>
            </div>
          </div>

          <div class="p-6">
            <p class="text-sm font-medium text-gray-700 mb-3">Stages:</p>
            <div class="space-y-2">
              <div
                v-for="stage in workflow.stages"
                :key="stage.id"
                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg"
              >
                <div
                  class="w-3 h-3 rounded-full flex-shrink-0"
                  :style="{ backgroundColor: stage.color || '#6b7280' }"
                ></div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ stage.name }}</p>
                  <p v-if="stage.requires_approval" class="text-xs text-gray-500">Requires approval</p>
                </div>
                <span class="text-xs text-gray-500">Step {{ stage.sequence_order }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import CMSLayout from '@/Layouts/CMSLayout.vue'

interface Props {
  workflows: any[]
}

const props = defineProps<Props>()
</script>
