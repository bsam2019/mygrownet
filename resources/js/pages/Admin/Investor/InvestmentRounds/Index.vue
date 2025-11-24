<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Investment Rounds</h1>
          <p class="text-gray-600 mt-1">Manage investment opportunities for the investor dashboard</p>
        </div>
        <Link
          :href="route('admin.investment-rounds.create')"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Create New Round
        </Link>
      </div>

      <!-- Rounds List -->
      <div class="bg-white rounded-lg shadow">
        <div v-if="rounds.length === 0" class="p-8 text-center">
          <p class="text-gray-500 mb-4">No investment rounds created yet</p>
          <Link
            :href="route('admin.investment-rounds.create')"
            class="text-blue-600 hover:text-blue-700"
          >
            Create your first investment round
          </Link>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Goal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Raised</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Featured</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="round in rounds" :key="round.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="font-medium text-gray-900">{{ round.name }}</div>
                  <div class="text-sm text-gray-500">{{ round.description.substring(0, 60) }}...</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  K{{ round.goalAmount.toLocaleString() }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  K{{ round.raisedAmount.toLocaleString() }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                      <div
                        class="bg-blue-600 h-2 rounded-full"
                        :style="`width: ${round.progressPercentage}%`"
                      ></div>
                    </div>
                    <span class="text-sm text-gray-600">{{ round.progressPercentage }}%</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="px-2 py-1 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-gray-100 text-gray-800': round.status === 'draft',
                      'bg-green-100 text-green-800': round.status === 'active',
                      'bg-yellow-100 text-yellow-800': round.status === 'closed',
                      'bg-blue-100 text-blue-800': round.status === 'completed'
                    }"
                  >
                    {{ round.statusDisplay }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span v-if="round.isFeatured" class="text-yellow-500">⭐ Featured</span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('admin.investment-rounds.edit', round.id)"
                      class="text-blue-600 hover:text-blue-700 text-sm"
                    >
                      Edit
                    </Link>
                    
                    <button
                      v-if="round.status === 'draft'"
                      @click="activateRound(round.id)"
                      class="text-green-600 hover:text-green-700 text-sm"
                    >
                      Activate
                    </button>
                    
                    <button
                      v-if="round.isActive && !round.isFeatured"
                      @click="setFeatured(round.id)"
                      class="text-yellow-600 hover:text-yellow-700 text-sm"
                    >
                      Set Featured
                    </button>
                    
                    <button
                      v-if="round.isActive"
                      @click="closeRound(round.id)"
                      class="text-orange-600 hover:text-orange-700 text-sm"
                    >
                      Close
                    </button>
                    
                    <button
                      v-if="round.status === 'closed'"
                      @click="reopenRound(round.id)"
                      class="text-green-600 hover:text-green-700 text-sm"
                    >
                      Reopen
                    </button>
                    
                    <button
                      v-if="round.status === 'draft'"
                      @click="deleteRound(round.id)"
                      class="text-red-600 hover:text-red-700 text-sm"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { route } from 'ziggy-js'

interface InvestmentRound {
  id: number
  name: string
  description: string
  goalAmount: number
  raisedAmount: number
  progressPercentage: number
  status: string
  statusDisplay: string
  isFeatured: boolean
  isActive: boolean
}

interface Props {
  rounds: InvestmentRound[]
}

defineProps<Props>()

const activateRound = (id: number) => {
  if (confirm('Activate this investment round?')) {
    router.post(route('admin.investment-rounds.activate', id))
  }
}

const setFeatured = (id: number) => {
  if (confirm('Set this as the featured investment round? This will remove featured status from other rounds.')) {
    router.post(route('admin.investment-rounds.set-featured', id))
  }
}

const closeRound = (id: number) => {
  if (confirm('Close this investment round? It will no longer accept investments.')) {
    router.post(route('admin.investment-rounds.close', id))
  }
}

const reopenRound = (id: number) => {
  if (confirm('Reopen this investment round? It will become active again.')) {
    router.post(route('admin.investment-rounds.reopen', id))
  }
}

const deleteRound = (id: number) => {
  if (confirm('Delete this investment round? This action cannot be undone.')) {
    router.delete(route('admin.investment-rounds.destroy', id))
  }
}
</script>
