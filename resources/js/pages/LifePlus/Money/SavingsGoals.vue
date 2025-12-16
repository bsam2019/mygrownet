<script setup lang="ts">
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    PlusIcon,
    TrashIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: LifePlusLayout });

interface SavingsGoal {
    id: number;
    name: string;
    target_amount: number;
    current_amount: number;
    remaining: number;
    progress: number;
    target_date: string | null;
    formatted_target_date: string | null;
    status: string;
    is_completed: boolean;
}

const props = defineProps<{
    goals: SavingsGoal[];
}>();

const showAddModal = ref(false);
const showContributeModal = ref(false);
const selectedGoal = ref<SavingsGoal | null>(null);

const form = useForm({
    name: '',
    target_amount: '',
    target_date: '',
});

const contributeForm = useForm({
    amount: '',
});

const formatCurrency = (amount: number) => {
    return 'K ' + amount.toLocaleString();
};

const openContribute = (goal: SavingsGoal) => {
    selectedGoal.value = goal;
    contributeForm.reset();
    showContributeModal.value = true;
};

const submitGoal = () => {
    form.post(route('lifeplus.money.savings.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

const submitContribution = () => {
    if (!selectedGoal.value) return;
    
    contributeForm.post(route('lifeplus.money.savings.contribute', selectedGoal.value.id), {
        onSuccess: () => {
            showContributeModal.value = false;
            contributeForm.reset();
            selectedGoal.value = null;
        },
    });
};

const deleteGoal = (id: number) => {
    if (confirm('Delete this savings goal?')) {
        router.delete(route('lifeplus.money.savings.destroy', id));
    }
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.money.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900 flex-1">Savings Goals</h1>
            <button 
                @click="showAddModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-xl font-medium hover:bg-blue-600 transition-colors"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                New Goal
            </button>
        </div>

        <!-- Goals List -->
        <div class="space-y-4">
            <div v-if="goals.length === 0" class="text-center py-12">
                <div class="text-5xl mb-4">🎯</div>
                <p class="text-gray-500">No savings goals yet</p>
                <button 
                    @click="showAddModal = true"
                    class="mt-3 text-blue-600 font-medium"
                >
                    Create your first goal
                </button>
            </div>

            <div 
                v-for="goal in goals" 
                :key="goal.id"
                class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"
            >
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ goal.name }}</h3>
                        <p v-if="goal.formatted_target_date" class="text-xs text-gray-500">
                            Target: {{ goal.formatted_target_date }}
                        </p>
                    </div>
                    <span 
                        v-if="goal.is_completed"
                        class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium"
                    >
                        ✓ Completed
                    </span>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Progress</span>
                        <span class="font-medium text-gray-900">
                            {{ formatCurrency(goal.current_amount) }} / {{ formatCurrency(goal.target_amount) }}
                        </span>
                    </div>
                    
                    <div class="relative h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div 
                            class="absolute inset-y-0 left-0 rounded-full transition-all"
                            :class="goal.is_completed ? 'bg-emerald-500' : 'bg-blue-500'"
                            :style="{ width: `${goal.progress}%` }"
                        ></div>
                    </div>
                    
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">{{ goal.progress.toFixed(0) }}% saved</span>
                        <span class="text-gray-500">{{ formatCurrency(goal.remaining) }} to go</span>
                    </div>
                </div>
                
                <div class="flex gap-2 mt-4">
                    <button 
                        v-if="!goal.is_completed"
                        @click="openContribute(goal)"
                        class="flex-1 py-2 bg-blue-500 text-white rounded-xl font-medium hover:bg-blue-600 transition-colors text-sm"
                    >
                        + Add Savings
                    </button>
                    <button 
                        @click="deleteGoal(goal.id)"
                        class="p-2 rounded-xl hover:bg-red-50 transition-colors"
                        aria-label="Delete goal"
                    >
                        <TrashIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Goal Modal -->
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
                            <h2 class="text-lg font-semibold text-gray-900">New Savings Goal</h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitGoal" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Goal Name</label>
                                <input 
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g., Emergency Fund"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Amount (K)</label>
                                <input 
                                    v-model="form.target_amount"
                                    type="number"
                                    min="1"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="1000"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Date (optional)</label>
                                <input 
                                    v-model="form.target_date"
                                    type="date"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-blue-500 text-white rounded-xl font-medium hover:bg-blue-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Goal' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Contribute Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showContributeModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center">
                    <div 
                        class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom"
                        @click.stop
                    >
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Add to {{ selectedGoal?.name }}</h2>
                            <button 
                                @click="showContributeModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitContribution" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount (K)</label>
                                <input 
                                    v-model="contributeForm.amount"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                    placeholder="0.00"
                                />
                            </div>
                            
                            <p class="text-sm text-gray-500">
                                Current: {{ formatCurrency(selectedGoal?.current_amount || 0) }} / 
                                {{ formatCurrency(selectedGoal?.target_amount || 0) }}
                            </p>
                            
                            <button 
                                type="submit"
                                :disabled="contributeForm.processing"
                                class="w-full py-3 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 disabled:opacity-50 transition-colors"
                            >
                                {{ contributeForm.processing ? 'Adding...' : 'Add Savings' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
