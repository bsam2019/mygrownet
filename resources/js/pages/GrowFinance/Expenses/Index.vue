<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
                    <p class="text-gray-500 text-sm">Track your business spending</p>
                </div>
                <button 
                    @click="router.visit(route('growfinance.expenses.create'))"
                    class="p-3 bg-red-500 text-white rounded-xl shadow-lg shadow-red-500/30 active:scale-95 transition-transform"
                    aria-label="Add expense"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <!-- Category Filter -->
            <div class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4 scrollbar-hide">
                <button
                    @click="selectedCategory = null"
                    :class="[
                        'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                        !selectedCategory ? 'bg-red-500 text-white' : 'bg-white text-gray-600'
                    ]"
                >
                    All
                </button>
                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="selectedCategory = cat"
                    :class="[
                        'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                        selectedCategory === cat ? 'bg-red-500 text-white' : 'bg-white text-gray-600'
                    ]"
                >
                    {{ cat }}
                </button>
            </div>

            <!-- Expenses List -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <ul v-if="filteredExpenses.length > 0" class="divide-y divide-gray-100">
                    <li v-for="expense in filteredExpenses" :key="expense.id">
                        <button 
                            @click="router.visit(route('growfinance.expenses.edit', expense.id))"
                            class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                        <BanknotesIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 truncate max-w-[180px]">
                                            {{ expense.description || expense.category || 'Expense' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ expense.expense_date }} • {{ expense.vendor?.name || 'No vendor' }}
                                        </p>
                                    </div>
                                </div>
                                <p class="font-semibold text-red-600">-{{ formatMoney(expense.amount) }}</p>
                            </div>
                        </button>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <BanknotesIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                    </div>
                    <p class="text-gray-500 text-sm">No expenses recorded</p>
                    <button 
                        @click="router.visit(route('growfinance.expenses.create'))"
                        class="text-red-600 text-sm font-medium mt-2"
                    >
                        Add your first expense
                    </button>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { PlusIcon, BanknotesIcon } from '@heroicons/vue/24/outline';

interface Expense {
    id: number;
    expense_date: string;
    category: string | null;
    description: string | null;
    amount: number;
    vendor: { id: number; name: string } | null;
}

interface Props {
    expenses: { data: Expense[] };
    categories: string[];
}

const props = defineProps<Props>();

const selectedCategory = ref<string | null>(null);

const filteredExpenses = computed(() => {
    if (!selectedCategory.value) return props.expenses.data;
    return props.expenses.data.filter(e => e.category === selectedCategory.value);
});

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
