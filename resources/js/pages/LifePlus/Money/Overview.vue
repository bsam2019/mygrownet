<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    PlusIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    Cog6ToothIcon,
    TrashIcon,
    PencilIcon,
    BanknotesIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';
import FeatureGate from '@/Components/LifePlus/FeatureGate.vue';

defineOptions({ layout: LifePlusLayout });

interface Category {
    id: number;
    name: string;
    icon: string;
    color: string;
}

interface Expense {
    id: number;
    category_id: number | null;
    category_name: string;
    category_icon: string;
    category_color: string;
    amount: number;
    description: string | null;
    expense_date: string;
    formatted_date: string;
}

interface CategorySummary {
    category_id: number | null;
    category_name: string;
    icon: string;
    color: string;
    total: number;
    count: number;
}

interface Summary {
    month: string;
    month_name: string;
    total_spent: number;
    budget: number;
    remaining: number;
    percentage: number;
    is_over_budget: boolean;
    by_category: CategorySummary[];
    transaction_count: number;
}

const props = defineProps<{
    expenses: Expense[];
    summary: Summary;
    categories: Category[];
    currentBudget: { amount: number } | null;
    month: string;
}>();

const showAddModal = ref(false);
const selectedExpense = ref<Expense | null>(null);

const form = useForm({
    category_id: null as number | null,
    amount: '',
    description: '',
    expense_date: new Date().toISOString().split('T')[0],
});

const formatCurrency = (amount: number) => {
    return 'K ' + amount.toLocaleString();
};

const changeMonth = (direction: number) => {
    const [year, month] = props.month.split('-').map(Number);
    const date = new Date(year, month - 1 + direction, 1);
    const newMonth = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    router.get(route('lifeplus.money.index'), { month: newMonth }, { preserveState: true });
};

const openAddModal = (expense?: Expense) => {
    if (expense) {
        selectedExpense.value = expense;
        form.category_id = expense.category_id;
        form.amount = expense.amount.toString();
        form.description = expense.description || '';
        form.expense_date = expense.expense_date;
    } else {
        selectedExpense.value = null;
        form.reset();
        form.expense_date = new Date().toISOString().split('T')[0];
    }
    showAddModal.value = true;
};

const submitExpense = () => {
    if (selectedExpense.value) {
        form.put(route('lifeplus.money.expenses.update', selectedExpense.value.id), {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('lifeplus.money.expenses.store'), {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteExpense = (id: number) => {
    if (confirm('Delete this expense?')) {
        router.delete(route('lifeplus.money.expenses.destroy', id));
    }
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header with Month Navigation -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">Money</h1>
            <div class="flex items-center gap-2 bg-white rounded-xl shadow-md p-1">
                <button 
                    @click="changeMonth(-1)"
                    class="p-2 rounded-lg hover:bg-emerald-50 transition-colors"
                    aria-label="Previous month"
                >
                    <ChevronLeftIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                </button>
                <span class="text-sm font-medium text-gray-700 min-w-[100px] text-center">
                    {{ summary.month_name }}
                </span>
                <button 
                    @click="changeMonth(1)"
                    class="p-2 rounded-lg hover:bg-emerald-50 transition-colors"
                    aria-label="Next month"
                >
                    <ChevronRightIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="bg-gradient-to-br from-white via-emerald-50 to-teal-50 rounded-3xl p-6 shadow-lg border border-emerald-200">
            <div class="text-center mb-4">
                <p class="text-emerald-600 text-sm font-medium">Total Spent</p>
                <p class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent mt-1">
                    {{ formatCurrency(summary.total_spent) }}
                </p>
                <p class="text-sm text-gray-600 mt-1">
                    Budget: <span class="font-semibold">{{ formatCurrency(summary.budget) }}</span>
                </p>
            </div>
            
            <div class="relative h-4 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                <div 
                    class="absolute inset-y-0 left-0 rounded-full transition-all shadow-md"
                    :class="summary.is_over_budget ? 'bg-gradient-to-r from-red-500 to-orange-500' : 'bg-gradient-to-r from-emerald-500 to-teal-500'"
                    :style="{ width: `${Math.min(100, summary.percentage)}%` }"
                ></div>
            </div>
            
            <div class="flex justify-between mt-3 text-sm">
                <span :class="summary.is_over_budget ? 'text-red-600 font-semibold' : 'text-emerald-600 font-semibold'">
                    {{ summary.percentage.toFixed(0) }}% used
                </span>
                <span class="text-gray-700 font-medium">
                    {{ summary.is_over_budget ? 'Over by' : 'Left:' }} {{ formatCurrency(Math.abs(summary.remaining)) }}
                </span>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex gap-3">
            <button 
                @click="openAddModal()"
                class="flex-1 flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 transition-all shadow-lg hover:shadow-xl hover:scale-105"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Add Expense
            </button>
            <Link 
                :href="route('lifeplus.money.budget')"
                class="px-4 py-3 bg-gradient-to-br from-gray-100 to-gray-200 text-gray-700 rounded-xl font-medium hover:from-gray-200 hover:to-gray-300 transition-all shadow-md hover:shadow-lg"
            >
                <Cog6ToothIcon class="h-5 w-5" aria-hidden="true" />
            </Link>
        </div>

        <!-- Categories Breakdown -->
        <div class="bg-gradient-to-br from-white to-blue-50 rounded-3xl p-5 shadow-lg border border-blue-200">
            <h2 class="font-semibold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-4">By Category</h2>
            
            <div v-if="summary.by_category.length === 0" class="text-center py-6 text-gray-500">
                No expenses this month
            </div>
            
            <div v-else class="space-y-3">
                <div 
                    v-for="cat in summary.by_category" 
                    :key="cat.category_id"
                    class="flex items-center gap-3"
                >
                    <span class="text-xl">{{ cat.icon }}</span>
                    <div class="flex-1">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">{{ cat.category_name }}</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(cat.total) }}</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                            <div 
                                class="h-full rounded-full"
                                :style="{ 
                                    width: `${(cat.total / summary.total_spent) * 100}%`,
                                    backgroundColor: cat.color 
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="bg-gradient-to-br from-white to-purple-50 rounded-3xl p-5 shadow-lg border border-purple-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Recent Expenses</h2>
                <span class="text-sm text-purple-600 font-medium">{{ summary.transaction_count }} transactions</span>
            </div>
            
            <div v-if="expenses.length === 0" class="text-center py-6 text-gray-500">
                No expenses recorded
            </div>
            
            <div v-else class="space-y-2">
                <div 
                    v-for="expense in expenses.slice(0, 10)" 
                    :key="expense.id"
                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 group"
                >
                    <span class="text-xl">{{ expense.category_icon }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ expense.description || expense.category_name }}
                        </p>
                        <p class="text-xs text-gray-500">{{ expense.formatted_date }}</p>
                    </div>
                    <span class="font-medium text-gray-900">{{ formatCurrency(expense.amount) }}</span>
                    <div class="hidden group-hover:flex items-center gap-1">
                        <button 
                            @click="openAddModal(expense)"
                            class="p-1.5 rounded-lg hover:bg-gray-200"
                            aria-label="Edit expense"
                        >
                            <PencilIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                        </button>
                        <button 
                            @click="deleteExpense(expense.id)"
                            class="p-1.5 rounded-lg hover:bg-red-100"
                            aria-label="Delete expense"
                        >
                            <TrashIcon class="h-4 w-4 text-red-500" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Savings Goals Link -->
            <Link 
                :href="route('lifeplus.money.savings')"
                class="block bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-4 text-white hover:scale-105 transition-transform"
            >
                <BanknotesIcon class="h-6 w-6 mb-2" aria-hidden="true" />
                <h3 class="font-semibold">Savings Goals</h3>
                <p class="text-blue-100 text-xs mt-0.5">Track progress</p>
            </Link>

            <!-- Chilimba Tracker Link - Premium Feature -->
            <FeatureGate feature="chilimba" compact>
                <Link 
                    :href="route('lifeplus.chilimba.index')"
                    class="block bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-4 text-white hover:scale-105 transition-transform"
                >
                    <UserGroupIcon class="h-6 w-6 mb-2" aria-hidden="true" />
                    <h3 class="font-semibold">Chilimba</h3>
                    <p class="text-emerald-100 text-xs mt-0.5">Village banking</p>
                </Link>
            </FeatureGate>
        </div>

        <!-- Add/Edit Expense Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center">
                    <div 
                        class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom"
                        @click.stop
                    >
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ selectedExpense ? 'Edit Expense' : 'Add Expense' }}
                            </h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitExpense" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount (K)</label>
                                <input 
                                    v-model="form.amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-lg"
                                    placeholder="0.00"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select 
                                    v-model="form.category_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                >
                                    <option :value="null">Select category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.icon }} {{ cat.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <input 
                                    v-model="form.description"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="What was this for?"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input 
                                    v-model="form.expense_date"
                                    type="date"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                />
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Saving...' : (selectedExpense ? 'Update' : 'Add Expense') }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
