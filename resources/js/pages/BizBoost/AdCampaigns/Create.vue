<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Props {
    objectives: { value: string; label: string; description: string }[];
    wallet_balance: number;
    markup_percentage: number;
    userCurrency: string;
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    objective: 'OUTCOME_TRAFFIC',
    client_budget: '',
});

const submit = () => {
    form.post(route('bizboost.ad-campaigns.store'));
};

const formatCurrency = (amount: number) => {
    const locale = props.userCurrency === 'ZMW' ? 'en-ZM' : 'en-US';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: props.userCurrency }).format(amount);
};

const metaBudget = () => {
    const client = parseFloat(form.client_budget) || 0;
    return client / (1 + props.markup_percentage / 100);
};
</script>

<template>
    <Head title="Create Ad Campaign - BizBoost" />
    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.ad-campaigns.index')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" /> Back to Campaigns
                    </Link>
                </div>

                <div v-if="wallet_balance <= 0" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-sm">
                    Your wallet balance is {{ formatCurrency(0) }}. You need to <Link :href="route('bizboost.wallet.index')" class="underline font-medium">deposit funds</Link> before launching a campaign.
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h1 class="text-2xl font-bold text-gray-900">Create Ad Campaign</h1>
                        <p class="mt-1 text-sm text-gray-500">A {{ markup_percentage }}% platform markup applies to all campaigns.</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Name</label>
                            <input v-model="form.name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. Summer Sale 2026" />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Objective</label>
                            <select v-model="form.objective" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option v-for="opt in objectives" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ objectives.find(o => o.value === form.objective)?.description }}</p>
                            <p v-if="form.errors.objective" class="mt-1 text-sm text-red-600">{{ form.errors.objective }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Budget (what you pay)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">{{ userCurrency === 'USD' ? '$' : 'K' }}</span>
                                <input v-model="form.client_budget" type="number" step="0.01" min="0" class="w-full pl-8 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00" />
                            </div>
                            <p v-if="form.client_budget && parseFloat(form.client_budget) > 0" class="mt-1 text-xs text-gray-500">
                                Meta spend: {{ formatCurrency(metaBudget()) }} | Platform fee: {{ formatCurrency(parseFloat(form.client_budget) - metaBudget()) }}
                            </p>
                            <p v-if="form.errors.client_budget" class="mt-1 text-sm text-red-600">{{ form.errors.client_budget }}</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link :href="route('bizboost.ad-campaigns.index')" class="px-4 py-2 text-gray-700 hover:text-gray-900">Cancel</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ form.processing ? 'Creating...' : 'Create Campaign' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
