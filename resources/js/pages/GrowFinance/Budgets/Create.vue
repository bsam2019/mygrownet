<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.budgets.index')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Create Budget</h1>
                    <p class="text-sm text-gray-500">Set a spending target</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input v-model="form.name" required
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        placeholder="e.g., Office Supplies"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select v-model="form.category"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white"
                        >
                            <option value="">None</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account</label>
                        <select v-model="form.account_id"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white"
                        >
                            <option value="">None</option>
                            <option v-for="acct in accounts" :key="acct.id" :value="acct.id">{{ acct.code }} - {{ acct.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                        <select v-model="form.period"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white"
                        >
                            <option v-for="p in periods" :key="p.value" :value="p.value">{{ p.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Budget Amount (K)</label>
                        <input v-model="form.budgeted_amount" type="number" step="0.01" min="0.01" required
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        />
                    </div>
                </div>
                <div v-if="form.period === 'custom'" class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input v-model="form.start_date" type="date"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input v-model="form.end_date" type="date"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alert at %</label>
                        <input v-model="form.alert_threshold" type="number" min="1" max="100" placeholder="80"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        />
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.rollover_unused" type="checkbox"
                                class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500"
                            />
                            <span class="text-sm text-gray-700">Rollover unused</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea v-model="form.notes" rows="2" maxlength="500"
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
                    ></textarea>
                </div>
                <button type="submit"
                    class="w-full py-3 bg-emerald-500 text-white rounded-xl font-medium active:scale-95 transition-transform"
                >Create Budget</button>
            </form>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import { reactive } from 'vue'

const props = defineProps({
    accounts: Array,
    categories: Array,
    periods: Array,
})

const form = reactive({
    name: '',
    category: '',
    account_id: '',
    period: 'monthly',
    start_date: '',
    end_date: '',
    budgeted_amount: '',
    alert_threshold: '',
    rollover_unused: false,
    notes: '',
})

function submit() {
    router.post(route('growfinance.budgets.store'), { ...form })
}
</script>
