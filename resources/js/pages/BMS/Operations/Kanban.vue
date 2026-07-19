<template>
  <CMSLayout page-title="Task Board">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Task Board</h1>
          <p class="mt-1 text-sm text-gray-500">Drag and drop tasks between stages</p>
        </div>
        <div class="flex items-center gap-3">
          <select
            v-model="selectedWorkflow"
            @change="loadTasks"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">All Workflows</option>
            <option v-for="workflow in workflows" :key="workflow.id" :value="workflow.id">
              {{ workflow.name }}
            </option>
          </select>
          <Link
            :href="route('cms.operations.tasks.create')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <PlusIcon class="h-5 w-5" aria-hidden="true" />
            New Task
          </Link>
        </div>
      </div>

      <!-- Kanban Board -->
      <div class="flex gap-4 overflow-x-auto pb-4">
        <div
          v-for="stage in stages"
          :key="stage.id"
          class="flex-shrink-0 w-80 bg-gray-50 rounded-lg p-4"
        >
          <!-- Stage Header -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <div
                class="w-3 h-3 rounded-full"
                :style="{ backgroundColor: stage.color || '#6b7280' }"
              ></div>
              <h3 class="font-semibold text-gray-900">{{ stage.name }}</h3>
              <span class="px-2 py-0.5 text-xs font-medium bg-gray-200 text-gray-700 rounded-full">
                {{ getTasksForStage(stage.id).length }}
              </span>
            </div>
          </div>

          <!-- Tasks -->
          <div
            class="space-y-3 min-h-[200px]"
            @drop="onDrop($event, stage.id)"
            @dragover.prevent
            @dragenter.prevent
          >
            <div
              v-for="task in getTasksForStage(stage.id)"
              :key="task.id"
              draggable="true"
              @dragstart="onDragStart($event, task)"
              class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 cursor-move hover:shadow-md transition"
            >
              <Link
                :href="route('cms.operations.tasks.show', task.id)"
                class="block"
              >
                <div class="flex items-start justify-between mb-2">
                  <h4 class="text-sm font-medium text-gray-900 flex-1">{{ task.title }}</h4>
                  <span
                    class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full flex-shrink-0"
                    :class="{
                      'bg-gray-100 text-gray-700': task.priority === 'low',
                      'bg-yellow-100 text-yellow-700': task.priority === 'medium',
                      'bg-orange-100 text-orange-700': task.priority === 'high',
                      'bg-red-100 text-red-700': task.priority === 'urgent',
                    }"
                  >
                    {{ task.priority }}
                  </span>
                </div>

                <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ task.description }}</p>

                <div class="flex items-center justify-between text-xs text-gray-500">
                  <div class="flex items-center gap-2">
                    <UserCircleIcon class="h-4 w-4" aria-hidden="true" />
                    <span>{{ task.assigned_user?.name || 'Unassigned' }}</span>
                  </div>
                  <div v-if="task.due_date" class="flex items-center gap-1">
                    <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                    <span>{{ new Date(task.due_date).toLocaleDateString() }}</span>
                  </div>
                </div>

                <!-- Task Metadata -->
                <div v-if="task.comments_count || task.attachments_count" class="flex items-center gap-3 mt-3 pt-3 border-t border-gray-100">
                  <div v-if="task.comments_count" class="flex items-center gap-1 text-xs text-gray-500">
                    <ChatBubbleLeftIcon class="h-4 w-4" aria-hidden="true" />
                    <span>{{ task.comments_count }}</span>
                  </div>
                  <div v-if="task.attachments_count" class="flex items-center gap-1 text-xs text-gray-500">
                    <PaperClipIcon class="h-4 w-4" aria-hidden="true" />
                    <span>{{ task.attachments_count }}</span>
                  </div>
                </div>
              </Link>
            </div>

            <!-- Empty State -->
            <div v-if="getTasksForStage(stage.id).length === 0" class="text-center py-8 text-gray-400">
              <p class="text-sm">No tasks</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import {
  PlusIcon,
  UserCircleIcon,
  CalendarIcon,
  ChatBubbleLeftIcon,
  PaperClipIcon,
} from '@heroicons/vue/24/outline'

interface Props {
  workflows: any[]
  stages: any[]
  tasks: any[]
}

const props = defineProps<Props>()

const selectedWorkflow = ref('')
const draggedTask = ref<any>(null)

const getTasksForStage = (stageId: number) => {
  return props.tasks.filter(task => task.workflow_stage_id === stageId)
}

const onDragStart = (event: DragEvent, task: any) => {
  draggedTask.value = task
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
  }
}

const onDrop = (event: DragEvent, stageId: number) => {
  event.preventDefault()
  
  if (!draggedTask.value || draggedTask.value.workflow_stage_id === stageId) {
    return
  }

  // Update task stage
  router.patch(route('cms.operations.tasks.update', draggedTask.value.id), {
    workflow_stage_id: stageId,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      draggedTask.value = null
    },
  })
}

const loadTasks = () => {
  router.get(route('cms.operations.kanban'), {
    workflow_id: selectedWorkflow.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>
