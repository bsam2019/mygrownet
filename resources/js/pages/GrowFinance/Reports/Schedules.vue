<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Report Schedules</h1>
                    <p class="text-gray-500 text-sm">Schedule automated report generation and delivery</p>
                </div>
                <button @click="showCreateForm = true" v-if="!showCreateForm"
                    class="p-3 bg-emerald-500 text-white rounded-xl shadow-lg shadow-emerald-500/30 active:scale-95 transition-transform"
                    aria-label="Create schedule"
                >
                    <PlusIcon class="h-5 w-5" />
                </button>
            </div>

            <!-- Create / Edit Form -->
            <div v-if="showCreateForm || editingSchedule" class="bg-white rounded-2xl shadow-sm p-5 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ editingSchedule ? 'Edit Schedule' : 'New Schedule' }}</h2>
                <form @submit.prevent="save">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input v-model="form.name" type="text" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="Monthly P&L Report"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                            <select v-model="form.report_type" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <option value="profit_loss">Profit & Loss</option>
                                <option value="balance_sheet">Balance Sheet</option>
                                <option value="trial_balance">Trial Balance</option>
                                <option value="cash_flow">Cash Flow</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <select v-model="form.frequency" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Format</label>
                            <select v-model="form.format"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="xlsx">Excel (XLSX)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                            <div v-for="(r, i) in form.recipients" :key="i" class="flex gap-2 mb-2">
                                <input v-model="r.email" type="email" required placeholder="Email"
                                    class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                                <input v-model="r.name" type="text" required placeholder="Name"
                                    class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                                <button type="button" @click="form.recipients.splice(i, 1)" v-if="form.recipients.length > 1"
                                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                    <XMarkIcon class="h-5 w-5" />
                                </button>
                            </div>
                            <button type="button" @click="form.recipients.push({ email: '', name: '' })"
                                class="text-sm text-emerald-600 font-medium hover:text-emerald-700"
                            >+ Add Recipient</button>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="submit"
                            class="px-6 py-2.5 bg-emerald-500 text-white rounded-xl text-sm font-medium hover:bg-emerald-600 active:scale-95 transition-all"
                        >{{ editingSchedule ? 'Update' : 'Create' }} Schedule</button>
                        <button type="button" @click="cancelForm"
                            class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-200 transition-colors"
                        >Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Schedule List -->
            <div v-if="schedules.length === 0 && !showCreateForm" class="bg-white rounded-2xl shadow-sm p-8 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                    <ClockIcon class="h-6 w-6 text-gray-400" />
                </div>
                <p class="text-gray-500 text-sm">No report schedules configured</p>
                <button @click="showCreateForm = true" class="text-emerald-600 text-sm font-medium mt-2">Create your first schedule</button>
            </div>

            <div v-else class="space-y-3">
                <div v-for="s in schedules" :key="s.id"
                    class="bg-white rounded-2xl shadow-sm p-4"
                >
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ s.name }}</h3>
                            <p class="text-xs text-gray-500">{{ reportTypeLabel[s.report_type] }} &middot; {{ frequencyLabel[s.frequency] }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="['px-2 py-0.5 rounded-full text-xs font-medium',
                                s.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'
                            ]">{{ s.is_active ? 'Active' : 'Inactive' }}</span>
                            <button @click="editSchedule(s)"
                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <PencilIcon class="h-4 w-4" />
                            </button>
                            <button @click="deleteSchedule(s.id)"
                                class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            >
                                <TrashIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-gray-400">
                        <span>Format: {{ s.format.toUpperCase() }}</span>
                        <span v-if="s.next_run_at">Next: {{ s.next_run_at }}</span>
                        <span v-if="s.last_run_at">Last: {{ s.last_run_at }}</span>
                        <span>{{ s.recipients?.length || 0 }} recipient(s)</span>
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import {
    PlusIcon,
    XMarkIcon,
    PencilIcon,
    TrashIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    schedules: Array,
})

const reportTypeLabel: Record<string, string> = {
    profit_loss: 'Profit & Loss',
    balance_sheet: 'Balance Sheet',
    trial_balance: 'Trial Balance',
    cash_flow: 'Cash Flow',
}

const frequencyLabel: Record<string, string> = {
    daily: 'Daily',
    weekly: 'Weekly',
    monthly: 'Monthly',
    quarterly: 'Quarterly',
    yearly: 'Yearly',
}

const showCreateForm = ref(false)
const editingSchedule = ref<any>(null)

const form = reactive({
    name: '',
    report_type: 'profit_loss',
    frequency: 'monthly',
    format: 'pdf',
    recipients: [{ email: '', name: '' }],
})

function resetForm() {
    form.name = ''
    form.report_type = 'profit_loss'
    form.frequency = 'monthly'
    form.format = 'pdf'
    form.recipients = [{ email: '', name: '' }]
}

function save() {
    if (editingSchedule.value) {
        router.put(route('growfinance.report-schedules.update', editingSchedule.value.id), {
            name: form.name,
            report_type: form.report_type,
            frequency: form.frequency,
            format: form.format,
            recipients: form.recipients,
        })
    } else {
        router.post(route('growfinance.report-schedules.store'), {
            name: form.name,
            report_type: form.report_type,
            frequency: form.frequency,
            format: form.format,
            recipients: form.recipients,
        })
    }
    cancelForm()
}

function editSchedule(s: any) {
    editingSchedule.value = s
    form.name = s.name
    form.report_type = s.report_type
    form.frequency = s.frequency
    form.format = s.format
    form.recipients = s.recipients?.length ? s.recipients : [{ email: '', name: '' }]
    showCreateForm = true
}

function cancelForm() {
    showCreateForm = false
    editingSchedule.value = null
    resetForm()
}

function deleteSchedule(id: number) {
    if (confirm('Delete this report schedule?')) {
        router.delete(route('growfinance.report-schedules.destroy', id))
    }
}
</script>
