<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.fixed-assets.index')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Acquire Fixed Asset</h1>
                    <p class="text-sm text-gray-500">Add an asset to the register</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Asset Name</label>
                    <input v-model="form.name" required
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="e.g., Dell Laptop XPS 15"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select v-model="form.category"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white"
                        >
                            <option value="">Select category</option>
                            <option value="Computer Equipment">Computer Equipment</option>
                            <option value="Office Furniture">Office Furniture</option>
                            <option value="Motor Vehicle">Motor Vehicle</option>
                            <option value="Machinery">Machinery</option>
                            <option value="Building">Building</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label>
                        <input v-model="form.purchase_date" type="date" required
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cost (K)</label>
                        <input v-model="form.cost" type="number" step="0.01" min="0.01" required
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Residual Value (K)</label>
                        <input v-model="form.residual_value" type="number" step="0.01" min="0"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Useful Life (months)</label>
                        <input v-model="form.useful_life_months" type="number" min="1" max="600" required
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., 36"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Depreciation Method</label>
                        <select v-model="form.depreciation_method"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white"
                        >
                            <option value="straight_line">Straight Line</option>
                            <option value="reducing_balance">Reducing Balance</option>
                        </select>
                    </div>
                </div>
                <div v-if="form.depreciation_method === 'reducing_balance'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Depreciation Rate (%)</label>
                    <input v-model="form.depreciation_rate" type="number" step="0.01" min="0" max="100"
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="e.g., 25"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input v-model="form.location"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., Head Office"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
                        <input v-model="form.serial_number"
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea v-model="form.notes" rows="2" maxlength="1000"
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                    ></textarea>
                </div>
                <button type="submit"
                    class="w-full py-3 bg-indigo-500 text-white rounded-xl font-medium active:scale-95 transition-transform"
                >Acquire Asset</button>
            </form>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import { reactive } from 'vue'

const form = reactive({
    name: '',
    category: '',
    purchase_date: '',
    cost: '',
    residual_value: '',
    useful_life_months: '',
    depreciation_method: 'straight_line',
    depreciation_rate: '',
    location: '',
    serial_number: '',
    notes: '',
})

function submit() {
    router.post(route('growfinance.fixed-assets.store'), { ...form })
}
</script>
