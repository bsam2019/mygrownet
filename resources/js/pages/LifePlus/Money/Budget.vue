<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Budget {
    id: number;
    category_id: number | null;
    category_name: string | null;
    amount: number;
    formatted_amount: string;
    period: string;
    spent: number;
    formatted_spent: string;
    percentage: number;
    start_date: string | null;
    end_date: string | null;
}

const props = defineProps<{
    budgets: Budget[];
    currentBudget: Budget | null;
}>();

const showAddModal = ref(false);
const editingBudget = ref<Budget | null>(null);

const form = useForm({
    amount: '',
    period: 'monthly',
});

const openAddModal = (budget?: Budget) => {
    if (budget) {
        editingBudget.value = budget;
        form.amount = budget.amount.toString();
        form.period = budget.period;
    } else {
        editingBudget.value = null;
        form.reset();
    }
    showAddModal.value = true;
};

const submitBudget = () => {
    if (editingBudget.value) {
        form.put(route('lifeplus.money.budget.update', editingBudget.value.id), {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
                editingBudget.value = null;
            },
        });
    } else {
        form.post(route('lifeplus.money.budget.store'), {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteBudget = (id: number) => {
    if (confirm('Delete this budget?')) {
        useForm({}).delete(route('lifeplus.money.budget.destroy', id));
    }
};

const getProgressColor = (percentage: number) => {
    if (percentage >= 100) return 'bg-red-500';
    if (percentage >= 80) return 'bg-amber-500';
    return 'bg-emerald-500';
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.money.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Back to money overview"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900 flex-1">Budget</h1>
            <button 
                @click="openAddModal()"
                class="flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 transition-colors"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Set Budget
            </button>
        </div>

        <!-- Current Budget Card -->
        <div v-if="currentBudget" class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-emerald-100 text-sm">{{ currentBudget.period === 'monthly' ? 'Monthly' : 'Weekly' }} Budget</p>
                    <p class="text-3xl font-bold mt-1">{{ currentBudget.formatted_amount }}</p>
                </div>
                <button 
                    @click="openAddModal(currentBudget)"
                    class="p-2 bg-white/20 rounded-lg hover:bg-white/30 transition-colors"
                    aria-label="Edit budget"
                >
                    <PencilIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
            
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span>Spent: {{ currentBudget.formatted_spent }}</span>
                    <span>{{ currentBudget.percentage }}%</span>
                </div>
                <div class="h-3 bg-white/20 rounded-full overflow-hidden">
                    <div 
                        class="h-full rounded-full transition-all"
                        :class="currentBudget.percentage >= 100 ? 'bg-red-400' : 'bg-white'"
                        :style="{ width: `${Math.min(currentBudget.percentage, 100)}%` }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- No Budget Set -->
        <div v-else class="bg-gray-50 rounded-2xl p-6 text-center">
            <p class="text-gray-500 mb-3">No budget set for this period</p>
            <button 
                @click="openAddModal()"
                class="text-emerald-600 font-medium"
            >
                Set your first budget
            </button>
        </div>

        <!-- Budget History -->
        <div v-if="budgets.length > 0" class="space-y-3">
            <h2 class="font-semibold text-gray-900">Budget History</h2>
            
            <div 
                v-for="budget in budgets" 
                :key="budget.id"
                class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"
            >
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="font-semibold text-gray-900">{{ budget.formatted_amount }}</p>
                        <p class="text-sm text-gray-500 capitalize">{{ budget.period }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="openAddModal(budget)"
                            class="p-2 rounded-lg hover:bg-gray-100"
                            aria-label="Edit budget"
                        >
                            <PencilIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                        </button>
                        <button 
                            @click="deleteBudget(budget.id)"
                            class="p-2 rounded-lg hover:bg-red-50"
                            aria-label="Delete budget"
                        >
                            <TrashIcon class="h-4 w-4 text-red-500" aria-hidden="true" />
                        </button>
                    </div>
                </div>
                
                <div class="space-y-1">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>{{ budget.formatted_spent }} spent</span>
                        <span>{{ budget.percentage }}%</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div 
                            class="h-full rounded-full transition-all"
                            :class="getProgressColor(budget.percentage)"
                            :style="{ width: `${Math.min(budget.percentage, 100)}%` }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Budget Modal -->
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
                                {{ editingBudget ? 'Edit Budget' : 'Set Budget' }}
                            </h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitBudget" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount (K)</label>
                                <input 
                                    v-model="form.amount"
                                    type="number"
                                    min="1"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-2xl font-bold text-center"
                                    placeholder="0"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button 
                                        type="button"
                                        @click="form.period = 'weekly'"
                                        :class="[
                                            'py-3 rounded-xl font-medium border-2 transition-colors',
                                            form.period === 'weekly' 
                                                ? 'border-emerald-500 bg-emerald-50 text-emerald-700' 
                                                : 'border-gray-200 text-gray-700'
                                        ]"
                                    >
                                        Weekly
                                    </button>
                                    <button 
                                        type="button"
                                        @click="form.period = 'monthly'"
                                        :class="[
                                            'py-3 rounded-xl font-medium border-2 transition-colors',
                                            form.period === 'monthly' 
                                                ? 'border-emerald-500 bg-emerald-50 text-emerald-700' 
                                                : 'border-gray-200 text-gray-700'
                                        ]"
                                    >
                                        Monthly
                                    </button>
                                </div>
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Saving...' : (editingBudget ? 'Update Budget' : 'Set Budget') }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
