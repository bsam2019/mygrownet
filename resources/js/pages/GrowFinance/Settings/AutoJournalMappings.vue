<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Auto-Journaling Mappings</h1>
                <p class="text-gray-500 text-sm">Configure automatic journal entries for system events</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <p class="text-sm text-gray-500">
                        Map external events to automatic journal entries. When a mapped event fires, a journal entry 
                        will be created using the configured debit and credit accounts.
                    </p>
                </div>

                <!-- Mappings Table (placeholder) -->
                <div class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-indigo-100 flex items-center justify-center">
                        <ArrowPathIcon class="h-6 w-6 text-indigo-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Auto-Journaling Coming Soon</h3>
                    <p class="text-gray-500 text-sm max-w-md mx-auto">
                        Configure automatic journal entries for events like sales completion, 
                        payment receipt, stock adjustments, and more.
                    </p>
                    <div class="mt-6 space-y-3 text-left max-w-lg mx-auto">
                        <div v-for="example in examples" :key="example.event"
                            class="bg-gray-50 rounded-xl p-4 text-sm"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <code class="text-xs bg-gray-200 px-2 py-0.5 rounded font-mono">{{ example.event }}</code>
                                <span class="text-xs text-gray-400">{{ example.frequency }}</span>
                            </div>
                            <p class="text-gray-600">{{ example.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { ArrowPathIcon } from '@heroicons/vue/24/outline'

const examples = [
    {
        event: 'stockflow.sale.completed.v1',
        frequency: 'On each sale',
        description: 'Debit Cash/Bank, Credit Sales Revenue — auto-record when a sale is completed in StockFlow.',
    },
    {
        event: 'growfinance.payment.received.v1',
        frequency: 'On payment',
        description: 'Debit Cash/Bank, Credit Accounts Receivable — auto-apply customer payments.',
    },
    {
        event: 'stockflow.stock.adjusted.v1',
        frequency: 'On adjustment',
        description: 'Debit Inventory Adjustments, Credit Inventory — record stock write-offs or adjustments.',
    },
]
</script>
