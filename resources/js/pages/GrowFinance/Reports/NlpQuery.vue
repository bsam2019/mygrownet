<script setup lang="ts">
import { ref } from 'vue';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { MagnifyingGlassIcon, SparklesIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const question = ref('');
const result = ref<any>(null);
const loading = ref(false);
const error = ref('');

const suggestedQuestions = [
    'What was our revenue this month?',
    'What is our cash position?',
    'What are our current financial ratios?',
    'Any anomalies detected?',
    'Compare this month vs last month',
    'What is our net income?',
    'What are our total expenses?',
    'What is our accounts receivable?',
];

const askQuestion = async (q?: string) => {
    if (q) question.value = q;
    if (!question.value.trim()) return;
    loading.value = true;
    error.value = '';
    result.value = null;
    try {
        const res = await axios.post(route('growfinance.nlp.ask'), { question: question.value });
        result.value = res.data;
    } catch (e: any) {
        error.value = e.response?.data?.error || 'Failed to process question';
    } finally {
        loading.value = false;
    }
};

const formatCurrency = (val: number) => {
    return 'K' + (val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">NLP Query</h1>
                <p class="text-gray-500 text-sm">Ask a question about your finances in plain English</p>
            </div>

            <!-- Search Bar -->
            <div class="relative mb-4">
                <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input
                    v-model="question"
                    type="text"
                    placeholder="e.g. What was our revenue last month?"
                    class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent shadow-sm"
                    @keyup.enter="askQuestion()"
                />
                <button
                    @click="askQuestion()"
                    :disabled="loading || !question.trim()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-1.5 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                >
                    {{ loading ? '...' : 'Ask' }}
                </button>
            </div>

            <!-- Suggested Questions -->
            <div class="mb-6">
                <p class="text-xs text-gray-500 mb-2">Suggestions</p>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="sq in suggestedQuestions"
                        :key="sq"
                        @click="askQuestion(sq)"
                        class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-full hover:bg-emerald-100 hover:text-emerald-700 hover:border-emerald-200 transition-colors border border-gray-200"
                    >
                        {{ sq }}
                    </button>
                </div>
            </div>

            <!-- Error -->
            <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                {{ error }}
            </div>

            <!-- Loading -->
            <div v-if="loading" class="bg-white rounded-2xl shadow-sm p-8 text-center">
                <div class="animate-pulse space-y-3">
                    <div class="h-4 bg-gray-200 rounded w-3/4 mx-auto"></div>
                    <div class="h-8 bg-gray-200 rounded w-1/2 mx-auto"></div>
                </div>
            </div>

            <!-- Result -->
            <div v-if="result && !loading">
                <!-- Number Result -->
                <div v-if="result.type === 'number'" class="bg-white rounded-2xl shadow-sm p-6">
                    <p class="text-sm text-gray-500 mb-1">{{ result.query }}</p>
                    <p class="text-4xl font-bold text-gray-900">{{ formatCurrency(result.result) }}</p>
                    <p class="text-sm text-gray-600 mt-2">{{ result.formatted }}</p>
                </div>

                <!-- Table Result -->
                <div v-else-if="result.type === 'table'" class="bg-white rounded-2xl shadow-sm p-4">
                    <p class="text-sm font-semibold text-gray-900 mb-3">{{ result.query }}</p>
                    <p class="text-sm text-gray-600 mb-3">{{ result.formatted }}</p>
                    <div v-if="result.data.forecast" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-400 text-xs uppercase">
                                    <th class="pb-2 font-medium">Month</th>
                                    <th class="pb-2 font-medium text-right">Cash Balance</th>
                                    <th class="pb-2 font-medium text-right">Lower</th>
                                    <th class="pb-2 font-medium text-right">Upper</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in result.data.forecast" :key="m.month" class="border-t border-gray-100">
                                    <td class="py-2 text-gray-700">{{ m.month }}</td>
                                    <td class="py-2 text-right font-medium" :class="m.projected_cash_balance >= 0 ? 'text-gray-900' : 'text-red-600'">
                                        {{ formatCurrency(m.projected_cash_balance) }}
                                    </td>
                                    <td class="py-2 text-right text-gray-500">{{ formatCurrency(m.confidence_interval_lower) }}</td>
                                    <td class="py-2 text-right text-gray-500">{{ formatCurrency(m.confidence_interval_upper) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else-if="result.data.current_ratio !== undefined" class="grid grid-cols-2 gap-3">
                        <div v-for="(val, key) in result.data" :key="key"
                            v-if="key !== 'period'"
                            class="text-center p-2"
                        >
                            <p class="text-xs text-gray-500 capitalize">{{ key.replace(/_/g, ' ') }}</p>
                            <p class="text-lg font-bold text-gray-900">{{ typeof val === 'number' ? val.toFixed(2) : val }}</p>
                        </div>
                    </div>
                </div>

                <!-- Comparison Result -->
                <div v-else-if="result.type === 'comparison'" class="bg-white rounded-2xl shadow-sm p-4">
                    <p class="text-sm font-semibold text-gray-900 mb-3">{{ result.query }}</p>
                    <p class="text-sm text-gray-600 mb-4">{{ result.formatted }}</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div v-if="result.data.current" class="p-3 bg-emerald-50 rounded-lg">
                            <p class="text-xs font-semibold text-emerald-700 mb-2">This Month</p>
                            <p class="text-sm text-gray-700">Revenue: {{ formatCurrency(result.data.current.total_income) }}</p>
                            <p class="text-sm text-gray-700">Expenses: {{ formatCurrency(result.data.current.total_expenses) }}</p>
                        </div>
                        <div v-if="result.data.previous" class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs font-semibold text-gray-700 mb-2">Last Month</p>
                            <p class="text-sm text-gray-700">Revenue: {{ formatCurrency(result.data.previous.total_income) }}</p>
                            <p class="text-sm text-gray-700">Expenses: {{ formatCurrency(result.data.previous.total_expenses) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Help Result -->
                <div v-else-if="result.type === 'help'" class="bg-white rounded-2xl shadow-sm p-6 text-center">
                    <SparklesIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-600">{{ result.formatted }}</p>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
