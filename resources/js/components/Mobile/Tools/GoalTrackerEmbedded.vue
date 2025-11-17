<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    TargetIcon, 
    CheckCircleIcon,
    PlusCircleIcon,
} from 'lucide-vue-next';

interface Goal {
    id: number;
    goal_type: 'monthly_income' | 'team_size' | 'total_earnings';
    target_amount: number;
    target_date: string;
    description: string | null;
    current_progress: number;
    status: 'active' | 'completed' | 'cancelled';
    created_at: string;
}

interface Props {
    goals: Goal[];
    currentEarnings: number;
    userTier: 'basic' | 'premium' | null;
}

const props = defineProps<Props>();
const emit = defineEmits(['success', 'error']);

const showCreateModal = ref(false);
const isSubmitting = ref(false);

// Local reactive goals list
const localGoals = ref<Goal[]>([...props.goals]);

// Form data
const form = ref({
    goal_type: 'monthly_income' as 'monthly_income' | 'team_size' | 'total_earnings',
    target_amount: 0,
    target_date: '',
    description: '',
});

const goalTypes = [
    { value: 'monthly_income', label: 'Monthly Income', icon: '💰', unit: 'K' },
    { value: 'team_size', label: 'Team Size', icon: '👥', unit: 'members' },
    { value: 'total_earnings', label: 'Total Earnings', icon: '📈', unit: 'K' },
];

const activeGoals = computed(() => localGoals.value.filter(g => g.status === 'active'));
const completedGoals = computed(() => localGoals.value.filter(g => g.status === 'completed'));

const getGoalProgress = (goal: Goal) => {
    if (goal.target_amount === 0) return 0;
    return Math.min((goal.current_progress / goal.target_amount) * 100, 100);
};

const getGoalType = (type: string) => {
    return goalTypes.find(t => t.value === type) || goalTypes[0];
};

const getDaysRemaining = (targetDate: string) => {
    const target = new Date(targetDate);
    const today = new Date();
    const diff = target.getTime() - today.getTime();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
};

const createGoal = () => {
    // Validate form
    if (!form.value.target_amount || form.value.target_amount <= 0) {
        emit('error', 'Please enter a valid target amount');
        return;
    }
    
    if (!form.value.target_date) {
        emit('error', 'Please select a target date');
        return;
    }
    
    isSubmitting.value = true;
    
    // Submit to API
    router.post(route('mygrownet.tools.goals.store'), form.value, {
        onSuccess: (page: any) => {
            // Add the new goal to local list immediately
            const newGoal: Goal = {
                id: Date.now(), // Temporary ID until we get real one from backend
                goal_type: form.value.goal_type,
                target_amount: form.value.target_amount,
                target_date: form.value.target_date,
                description: form.value.description || null,
                current_progress: 0,
                status: 'active',
                created_at: new Date().toISOString(),
            };
            
            localGoals.value.unshift(newGoal);
            
            showCreateModal.value = false;
            resetForm();
            emit('success', 'Goal created successfully! 🎯');
            isSubmitting.value = false;
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors).flat().join(', ') || 'Failed to create goal';
            emit('error', errorMessage);
            isSubmitting.value = false;
        },
        preserveScroll: true,
    });
};

const updateProgress = (goalId: number, progress: number) => {
    // Update local state immediately
    const goalIndex = localGoals.value.findIndex(g => g.id === goalId);
    if (goalIndex !== -1) {
        localGoals.value[goalIndex].current_progress = progress;
        
        // Check if goal is completed
        if (progress >= localGoals.value[goalIndex].target_amount) {
            localGoals.value[goalIndex].status = 'completed';
        }
    }
    
    // Update progress via API
    router.patch(route('mygrownet.tools.goals.update', goalId), {
        current_progress: progress,
    }, {
        onSuccess: () => {
            emit('success', 'Progress updated! 📈');
        },
        onError: () => {
            emit('error', 'Failed to update progress');
            // Revert local change on error
            if (goalIndex !== -1) {
                localGoals.value[goalIndex].current_progress = progress - 1;
            }
        },
        preserveScroll: true,
    });
};

const resetForm = () => {
    form.value = {
        goal_type: 'monthly_income',
        target_amount: 0,
        target_date: '',
        description: '',
    };
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <div class="p-4 space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <TargetIcon class="h-6 w-6 text-purple-600" />
                    Goal Tracker
                </h2>
                <p class="text-xs text-gray-600 mt-1">Set and track your goals</p>
            </div>
            <button
                @click="showCreateModal = true"
                class="px-3 py-2 bg-purple-600 text-white rounded-lg text-xs font-semibold"
            >
                <PlusCircleIcon class="h-4 w-4 inline mr-1" />
                New Goal
            </button>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-3 text-white">
                <p class="text-xs opacity-90">Active</p>
                <p class="text-2xl font-bold mt-1">{{ activeGoals.length }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-3 text-white">
                <p class="text-xs opacity-90">Done</p>
                <p class="text-2xl font-bold mt-1">{{ completedGoals.length }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-3 text-white">
                <p class="text-xs opacity-90">Earnings</p>
                <p class="text-lg font-bold mt-1">K{{ currentEarnings }}</p>
            </div>
        </div>

        <!-- Active Goals -->
        <div v-if="activeGoals.length > 0">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Active Goals</h3>
            <div class="space-y-3">
                <div
                    v-for="goal in activeGoals"
                    :key="goal.id"
                    class="bg-white rounded-xl shadow-sm p-4"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="text-2xl">{{ getGoalType(goal.goal_type).icon }}</div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">{{ getGoalType(goal.goal_type).label }}</h4>
                                <p class="text-xs text-gray-600">Target: {{ goal.target_amount }} {{ getGoalType(goal.goal_type).unit }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="goal.description" class="mb-3">
                        <p class="text-xs text-gray-600">{{ goal.description }}</p>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-700">Progress</span>
                            <span class="text-xs font-bold text-purple-600">{{ getGoalProgress(goal).toFixed(0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div 
                                class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all duration-500"
                                :style="{ width: `${getGoalProgress(goal)}%` }"
                            ></div>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs text-gray-600">{{ goal.current_progress }} / {{ goal.target_amount }}</span>
                            <span class="text-xs text-gray-600">{{ getDaysRemaining(goal.target_date) }} days left</span>
                        </div>
                    </div>

                    <!-- Target Date -->
                    <div class="flex items-center justify-between text-xs border-t pt-2">
                        <span class="text-gray-600">Target:</span>
                        <span class="font-semibold text-gray-900">{{ formatDate(goal.target_date) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Goals -->
        <div v-if="completedGoals.length > 0">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Completed Goals 🎉</h3>
            <div class="grid grid-cols-1 gap-2">
                <div
                    v-for="goal in completedGoals"
                    :key="goal.id"
                    class="bg-green-50 border border-green-200 rounded-lg p-3"
                >
                    <div class="flex items-center gap-2">
                        <CheckCircleIcon class="h-4 w-4 text-green-600" />
                        <div class="flex-1">
                            <h4 class="text-xs font-semibold text-gray-900">{{ getGoalType(goal.goal_type).label }}</h4>
                            <p class="text-xs text-gray-600">{{ goal.target_amount }} {{ getGoalType(goal.goal_type).unit }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="activeGoals.length === 0 && completedGoals.length === 0" class="bg-white rounded-xl shadow-sm p-8 text-center">
            <TargetIcon class="h-12 w-12 text-gray-400 mx-auto mb-3" />
            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Goals Yet</h3>
            <p class="text-xs text-gray-600 mb-4">Start tracking your progress!</p>
            <button
                @click="showCreateModal = true"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg text-xs font-semibold"
            >
                Create Your First Goal
            </button>
        </div>

        <!-- Create Goal Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click="showCreateModal = false">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full" @click.stop>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Create New Goal</h3>
                
                <form @submit.prevent="createGoal" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-2">Goal Type</label>
                        <select
                            v-model="form.goal_type"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                        >
                            <option v-for="type in goalTypes" :key="type.value" :value="type.value">
                                {{ type.icon }} {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-2">
                            Target Amount ({{ getGoalType(form.goal_type).unit }})
                        </label>
                        <input
                            v-model.number="form.target_amount"
                            type="number"
                            required
                            min="1"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-2">Target Date</label>
                        <input
                            v-model="form.target_date"
                            type="date"
                            required
                            :min="new Date().toISOString().split('T')[0]"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                            placeholder="Why is this goal important?"
                        ></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="flex-1 px-4 py-3 bg-purple-600 text-white rounded-lg text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ isSubmitting ? 'Creating...' : 'Create Goal' }}
                        </button>
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            :disabled="isSubmitting"
                            class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold disabled:opacity-50"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
