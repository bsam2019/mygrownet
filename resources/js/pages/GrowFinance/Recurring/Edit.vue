<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.recurring.show', recurring.id)" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Edit Recurring Transaction</h1>
                    <p class="text-sm text-gray-500">{{ recurring.description }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input v-model="form.description" required
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (K)</label>
                    <input v-model="form.amount" type="number" step="0.01" min="0.01" required
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                        <select v-model="form.frequency"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent bg-white"
                        >
                            <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select v-model="form.category"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent bg-white"
                        >
                            <option value="">None</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input v-model="form.end_date" type="date"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account</label>
                        <select v-model="form.account_id"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent bg-white"
                        >
                            <option value="">None</option>
                            <option v-for="acct in accounts" :key="acct.id" :value="acct.id">{{ acct.code }} - {{ acct.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                        <select v-model="form.vendor_id"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent bg-white"
                        >
                            <option value="">None</option>
                            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                        <select v-model="form.customer_id"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent bg-white"
                        >
                            <option value="">None</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Occurrences</label>
                    <input v-model="form.max_occurrences" type="number" min="1" placeholder="Leave blank for unlimited"
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea v-model="form.notes" rows="2" maxlength="500"
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent resize-none"
                    ></textarea>
                </div>
                <button type="submit"
                    class="w-full py-3 bg-cyan-500 text-white rounded-xl font-medium active:scale-95 transition-transform"
                >Update Recurring Transaction</button>
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
    recurring: Object,
    accounts: Array,
    vendors: Array,
    customers: Array,
    frequencies: Array,
    categories: Array,
})

const form = reactive({
    description: props.recurring?.description || '',
    amount: props.recurring?.amount || '',
    frequency: props.recurring?.frequency || 'monthly',
    end_date: props.recurring?.end_date ? props.recurring.end_date.substring(0, 10) : '',
    account_id: props.recurring?.account_id || '',
    vendor_id: props.recurring?.vendor_id || '',
    customer_id: props.recurring?.customer_id || '',
    category: props.recurring?.category || '',
    payment_method: props.recurring?.payment_method || '',
    max_occurrences: props.recurring?.max_occurrences || '',
    notes: props.recurring?.notes || '',
})

function submit() {
    router.put(route('growfinance.recurring.update', props.recurring?.id), { ...form })
}
</script>
