<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { PaperAirplaneIcon, DocumentCheckIcon, CalendarDaysIcon, CheckCircleIcon, XCircleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: GrowFinanceLayout })

const props = defineProps({
    filingCalendar: {
        type: Array,
        default: () => [],
    },
})

const currentPeriod = ref(new Date().toISOString().slice(0, 7))
const submitting = ref(false)
const checking = ref(false)
const result = ref(null)
const statusResult = ref(null)
const statusRef = ref('')

async function handleSubmit() {
    submitting.value = true
    result.value = null
    try {
        const res = await fetch(route('growfinance.tax-returns.submit'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') },
            body: JSON.stringify({ period: currentPeriod.value }),
        })
        result.value = await res.json()
    } catch (e) {
        result.value = { success: false, error: e.message }
    } finally {
        submitting.value = false
    }
}

async function handleCheckStatus() {
    if (!statusRef.value) return
    checking.value = true
    statusResult.value = null
    try {
        const res = await fetch(route('growfinance.tax-returns.status'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') },
            body: JSON.stringify({ reference: statusRef.value }),
        })
        statusResult.value = await res.json()
    } catch (e) {
        statusResult.value = { found: false, error: e.message }
    } finally {
        checking.value = false
    }
}

const calendarEntries = computed(() => {
    if (Array.isArray(props.filingCalendar)) return props.filingCalendar
    if (props.filingCalendar?.entries) return props.filingCalendar.entries
    return []
})
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">ZRA Tax Return Submission</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Submit VAT returns electronically to the Zambia Revenue Authority.</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Submit Section -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <PaperAirplaneIcon class="h-5 w-5 text-emerald-500" />
                        Submit VAT Return
                    </h2>
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax Period</label>
                            <input
                                v-model="currentPeriod"
                                type="month"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>
                        <button
                            @click="handleSubmit"
                            :disabled="submitting"
                            class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-sm transition-colors flex items-center gap-2"
                        >
                            <ArrowPathIcon v-if="submitting" class="h-4 w-4 animate-spin" />
                            <PaperAirplaneIcon v-else class="h-4 w-4" />
                            {{ submitting ? 'Submitting...' : 'Prepare & Submit' }}
                        </button>
                    </div>
                </div>

                <!-- Submission Result -->
                <div v-if="result" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-3 flex items-center gap-2"
                        :class="result.success ? 'text-emerald-600' : 'text-red-600'">
                        <CheckCircleIcon v-if="result.success" class="h-5 w-5" />
                        <XCircleIcon v-else class="h-5 w-5" />
                        {{ result.success ? 'Submission Successful' : 'Submission Failed' }}
                    </h3>
                    <div v-if="result.success" class="space-y-2 text-sm">
                        <p><span class="font-medium text-gray-600">Reference:</span>
                            <code class="ml-2 px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-emerald-700 dark:text-emerald-300">{{ result.submission_reference }}</code>
                        </p>
                        <p class="text-gray-500">{{ result.message }}</p>
                    </div>
                    <div v-else class="space-y-2 text-sm">
                        <p class="text-red-600">{{ result.error }}</p>
                        <pre v-if="result.raw_response" class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 rounded text-xs overflow-x-auto">{{ JSON.stringify(result.raw_response, null, 2) }}</pre>
                    </div>
                </div>

                <!-- Check Status -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <DocumentCheckIcon class="h-5 w-5 text-blue-500" />
                        Check Submission Status
                    </h2>
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Submission Reference</label>
                            <input
                                v-model="statusRef"
                                type="text"
                                placeholder="ZRA-REF-..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <button
                            @click="handleCheckStatus"
                            :disabled="checking || !statusRef"
                            class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-sm transition-colors flex items-center gap-2"
                        >
                            <ArrowPathIcon v-if="checking" class="h-4 w-4 animate-spin" />
                            <span>{{ checking ? 'Checking...' : 'Check Status' }}</span>
                        </button>
                    </div>
                    <div v-if="statusResult" class="mt-4 text-sm">
                        <template v-if="statusResult.found">
                            <p class="flex items-center gap-2 text-emerald-600">
                                <CheckCircleIcon class="h-4 w-4" />
                                Status: <strong>{{ statusResult.status }}</strong>
                            </p>
                        </template>
                        <template v-else>
                            <p class="text-red-600">{{ statusResult.error }}</p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Filing Calendar Sidebar -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <CalendarDaysIcon class="h-5 w-5 text-amber-500" />
                    Filing Calendar
                </h2>
                <div v-if="calendarEntries.length > 0" class="space-y-3">
                    <div
                        v-for="(entry, idx) in calendarEntries"
                        :key="idx"
                        class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-600"
                    >
                        <p class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ entry.tax_period || entry.period }}</p>
                        <p class="text-xs text-gray-500 mt-1">Due: <span class="font-medium text-gray-700 dark:text-gray-300">{{ entry.due_date || entry.dueDate }}</span></p>
                        <p class="text-xs text-gray-500">{{ entry.description || 'VAT return filing period' }}</p>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-400 italic">
                    {{ filingCalendar.length === 0 ? 'Unable to fetch filing calendar. Ensure ZRA credentials are configured.' : 'No upcoming filing deadlines.' }}
                </p>
            </div>
        </div>
    </div>
</template>
