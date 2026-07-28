<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Period End</h1>
                    <p class="text-gray-500 text-sm">Close the {{ currentPeriod.label }} accounting period</p>
                </div>
            </div>

            <div v-if="!checklist" class="bg-white rounded-2xl shadow-sm p-8 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-emerald-100 flex items-center justify-center">
                    <ClipboardDocumentCheckIcon class="h-6 w-6 text-emerald-600" />
                </div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">No Active Checklist</h2>
                <p class="text-gray-500 text-sm mb-4">Generate a period-end checklist for {{ currentPeriod.label }} to begin the closing process.</p>
                <button @click="generateChecklist"
                    class="px-6 py-2.5 bg-emerald-500 text-white rounded-xl text-sm font-medium hover:bg-emerald-600 active:scale-95 transition-all"
                >Generate Checklist for {{ currentPeriod.label }}</button>
            </div>

            <div v-else class="space-y-4">
                <!-- Status Banner -->
                <div :class="['rounded-xl p-4 text-sm font-medium flex items-center gap-3',
                    checklist.status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'
                ]">
                    <CheckCircleIcon v-if="checklist.status === 'completed'" class="h-5 w-5" />
                    <ClockIcon v-else class="h-5 w-5" />
                    <span>{{ checklist.status === 'completed' ? 'Period closed successfully' : 'Period end in progress' }}</span>
                </div>

                <!-- Tasks -->
                <div class="bg-white rounded-2xl shadow-sm divide-y divide-gray-100">
                    <div v-for="item in checklist.items" :key="item.task" class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div :class="['w-6 h-6 rounded-full border-2 flex items-center justify-center',
                                    item.completed ? 'bg-emerald-500 border-emerald-500' : 'border-gray-300'
                                ]">
                                    <CheckIcon v-if="item.completed" class="h-4 w-4 text-white" />
                                </div>
                                <div>
                                    <p :class="['font-medium', item.completed ? 'text-gray-400 line-through' : 'text-gray-900']">{{ item.task }}</p>
                                    <p v-if="item.completed && item.completed_at" class="text-xs text-gray-400 mt-0.5">Completed {{ item.completed_at }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button v-if="!item.completed && item.task === 'Run depreciation'"
                                    @click="runDepreciation"
                                    class="px-3 py-1.5 bg-amber-50 text-amber-700 rounded-lg text-xs font-medium hover:bg-amber-100 transition-colors"
                                >Run Now</button>
                                <button v-if="!item.completed && item.task === 'Snapshot financial reports'"
                                    @click="snapshotReports"
                                    class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-100 transition-colors"
                                >Run Now</button>
                                <button v-if="!item.completed && item.task === 'Close accounting period'"
                                    @click="closePeriod"
                                    class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100 transition-colors"
                                >Close Period</button>
                                <button v-if="!item.completed && !['Run depreciation', 'Snapshot financial reports', 'Close accounting period'].includes(item.task)"
                                    @click="completeTask(item.task)"
                                    class="px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-100 transition-colors"
                                >Mark Done</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-500">
                    <span class="font-medium text-gray-700">{{ completedCount }}/{{ checklist.items.length }}</span> tasks completed
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import {
    ClipboardDocumentCheckIcon,
    CheckCircleIcon,
    ClockIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    checklist: Object,
    currentPeriod: Object,
})

const completedCount = computed(() => {
    if (!props.checklist?.items) return 0
    return props.checklist.items.filter((i: any) => i.completed).length
})

function generateChecklist() {
    router.post(route('growfinance.period-end.generate'))
}

function completeTask(taskName: string) {
    router.post(route('growfinance.period-end.complete-task', props.checklist.id), {
        task_name: taskName,
    })
}

function runDepreciation() {
    router.post(route('growfinance.period-end.run-depreciation', props.checklist.id))
}

function snapshotReports() {
    router.post(route('growfinance.period-end.snapshot-reports', props.checklist.id))
}

function closePeriod() {
    if (confirm('Close the accounting period? This action cannot be undone.')) {
        router.post(route('growfinance.period-end.close-period', props.checklist.id))
    }
}
</script>
