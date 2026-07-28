<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.periods.index')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">New Fiscal Year</h1>
                    <p class="text-sm text-gray-500">Create a fiscal year with monthly periods</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                    <input v-model="form.label" required
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                        placeholder="e.g., FY 2026"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input v-model="form.start_date" type="date" required
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input v-model="form.end_date" type="date" required
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                        />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="form.generate_periods" type="checkbox" id="generate_periods"
                        class="rounded border-gray-300 text-amber-500 focus:ring-amber-500"
                    />
                    <label for="generate_periods" class="text-sm text-gray-700">Generate monthly periods</label>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-amber-500 text-white rounded-xl font-medium active:scale-95 transition-transform"
                >Create Fiscal Year</button>
            </form>
        </div>
    </GrowFinanceLayout>
</template>

<script setup>
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import { reactive } from 'vue'

const form = reactive({
    label: '',
    start_date: '',
    end_date: '',
    generate_periods: true,
})

function submit() {
    router.post(route('growfinance.periods.store'), { ...form })
}
</script>
