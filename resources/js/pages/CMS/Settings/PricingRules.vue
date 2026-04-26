<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: CMSLayout });

interface PricingRules {
    id: number;
    sliding_window_rate: number;
    casement_window_rate: number;
    sliding_door_rate: number;
    hinged_door_rate: number;
    other_rate: number;
    material_cost_per_m2: number;
    labour_cost_per_m2: number;
    overhead_cost_per_m2: number;
    minimum_profit_percent: number;
    tax_rate: number;
}

const props = defineProps<{ pricingRules: PricingRules }>();

const form = useForm({ ...props.pricingRules });

const totalCostPerM2 = computed(() =>
    Number(form.material_cost_per_m2) + Number(form.labour_cost_per_m2) + Number(form.overhead_cost_per_m2)
);

function profitMargin(rate: number) {
    if (!rate) return 0;
    return ((rate - totalCostPerM2.value) / rate) * 100;
}

function marginClass(rate: number) {
    const m = profitMargin(rate);
    if (m < Number(form.minimum_profit_percent)) return 'text-red-600';
    if (m < Number(form.minimum_profit_percent) + 5) return 'text-amber-600';
    return 'text-green-600';
}

const sellingRates = [
    { key: 'sliding_window_rate',  label: 'Sliding Window' },
    { key: 'casement_window_rate', label: 'Casement Window' },
    { key: 'sliding_door_rate',    label: 'Sliding Door' },
    { key: 'hinged_door_rate',     label: 'Hinged Door' },
    { key: 'other_rate',           label: 'Other' },
] as const;

function submit() {
    form.put(route('cms.settings.pricing-rules.update'));
}
</script>

<template>
    <Head title="Pricing Rules - Settings" />

    <div class="max-w-3xl mx-auto">
        <Link :href="route('cms.dashboard')" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4 transition">
            <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
            Back to Dashboard
        </Link>

        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-blue-100 rounded-lg">
                <CurrencyDollarIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pricing Rules</h1>
                <p class="text-sm text-gray-500">Set your company's aluminium fabrication rates and costs</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">

            <!-- Selling Rates -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-1">Selling Rates (per m²)</h2>
                <p class="text-xs text-gray-500 mb-4">What you charge clients. Shown on quotations.</p>

                <div class="space-y-3">
                    <div v-for="rate in sellingRates" :key="rate.key" class="flex items-center gap-4">
                        <label class="w-40 text-sm font-medium text-gray-700 flex-shrink-0">{{ rate.label }}</label>
                        <div class="flex-1 relative">
                            <span class="absolute left-3 top-2 text-sm text-gray-500">K</span>
                            <input
                                v-model.number="form[rate.key]"
                                type="number" min="0" step="0.01"
                                class="w-full pl-7 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div class="w-24 text-right text-xs">
                            <span :class="marginClass(form[rate.key])">
                                {{ profitMargin(form[rate.key]).toFixed(1) }}% margin
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Internal Costs -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-1">Internal Costs (per m²)</h2>
                <p class="text-xs text-gray-500 mb-4">Not shown to clients. Used for profit calculations only.</p>

                <div class="space-y-3">
                    <div class="flex items-center gap-4">
                        <label class="w-40 text-sm font-medium text-gray-700 flex-shrink-0">Material Cost</label>
                        <div class="flex-1 relative">
                            <span class="absolute left-3 top-2 text-sm text-gray-500">K</span>
                            <input v-model.number="form.material_cost_per_m2" type="number" min="0" step="0.01" class="w-full pl-7 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="w-40 text-sm font-medium text-gray-700 flex-shrink-0">Labour Cost</label>
                        <div class="flex-1 relative">
                            <span class="absolute left-3 top-2 text-sm text-gray-500">K</span>
                            <input v-model.number="form.labour_cost_per_m2" type="number" min="0" step="0.01" class="w-full pl-7 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="w-40 text-sm font-medium text-gray-700 flex-shrink-0">Overhead Cost</label>
                        <div class="flex-1 relative">
                            <span class="absolute left-3 top-2 text-sm text-gray-500">K</span>
                            <input v-model.number="form.overhead_cost_per_m2" type="number" min="0" step="0.01" class="w-full pl-7 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>

                    <!-- Total Cost Summary -->
                    <div class="flex items-center gap-4 pt-2 border-t border-gray-100">
                        <span class="w-40 text-sm font-semibold text-gray-700 flex-shrink-0">Total Cost / m²</span>
                        <span class="text-sm font-bold text-gray-900">K{{ totalCostPerM2.toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Profit & Tax Rules -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Profit & Tax Rules</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Profit %</label>
                        <div class="relative">
                            <input v-model.number="form.minimum_profit_percent" type="number" min="0" max="100" step="0.1" class="w-full pr-8 pl-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500" />
                            <span class="absolute right-3 top-2 text-sm text-gray-500">%</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Warn when a job falls below this margin</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (VAT)</label>
                        <div class="relative">
                            <input v-model.number="form.tax_rate" type="number" min="0" max="100" step="0.1" class="w-full pr-8 pl-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500" />
                            <span class="absolute right-3 top-2 text-sm text-gray-500">%</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Applied to quotation totals</p>
                    </div>
                </div>
            </div>

            <!-- Margin Preview Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Margin Preview</h2>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 text-xs font-medium text-gray-500">Type</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500">Rate / m²</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500">Cost / m²</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500">Profit / m²</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500">Margin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="rate in sellingRates" :key="rate.key">
                            <td class="py-2 text-gray-700">{{ rate.label }}</td>
                            <td class="py-2 text-right text-gray-900">K{{ Number(form[rate.key]).toFixed(2) }}</td>
                            <td class="py-2 text-right text-gray-600">K{{ totalCostPerM2.toFixed(2) }}</td>
                            <td class="py-2 text-right" :class="(form[rate.key] - totalCostPerM2) >= 0 ? 'text-green-700' : 'text-red-700'">
                                K{{ (Number(form[rate.key]) - totalCostPerM2).toFixed(2) }}
                            </td>
                            <td class="py-2 text-right font-semibold" :class="marginClass(form[rate.key])">
                                {{ profitMargin(form[rate.key]).toFixed(1) }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Save -->
            <div class="flex justify-end gap-3">
                <Link :href="route('cms.dashboard')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">Cancel</Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition"
                >
                    {{ form.processing ? 'Saving…' : 'Save Pricing Rules' }}
                </button>
            </div>
        </form>
    </div>
</template>
