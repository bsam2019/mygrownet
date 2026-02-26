<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { 
    TargetIcon, 
    TrendingUpIcon, 
    CheckCircleIcon,
    PlusCircleIcon,
    TrashIcon,
    EditIcon
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

const showCreateModal = ref(false);
const editingGoal = ref<Goal | null>(null);

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

const activeGoals = computed(() => props.goals.filter(g => g.status === 'active'));
const completedGoals = computed(() => props.goals.filter(g => g.status === 'completed'));

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
    router.post(route('mygrownet.tools.goals.store'), form.value, {
        onSuccess: () => {
            showCreateModal.value = false;
            resetForm();
        },
    });
};

const updateProgress = (goalId: number, progress: number) => {
    router.patch(route('mygrownet.tools.goals.update', goalId), {
        current_progress: progress,
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
    <Head title="Goal Tracker" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <TargetIcon class="h-8 w-8 text-blue-600" />
                            Goal Tracker
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Set and track your income and growth goals
                        </p>
                    </div>
                    <button
                        @click="showCreateModal = true"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
                    >
                        <PlusCircleIcon class="h-5 w-5 mr-2" />
                        New Goal
                    </button>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                        <p class="text-sm opacity-90">Active Goals</p>
                        <p class="text-4xl font-bold mt-2">{{ activeGoals.length }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
                        <p class="text-sm opacity-90">Completed Goals</p>
                        <p class="text-4xl font-bold mt-2">{{ completedGoals.length }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow p-6 text-white">
                        <p class="text-sm opacity-90">Current Earnings</p>
                        <p class="text-4xl font-bold mt-2">{{ formatCurrency(currentEarnings) }}</p>
                    </div>
                </div>

                <!-- Active Goals -->
                <div v-if="activeGoals.length > 0" class="mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Active Goals</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="goal in activeGoals"
                            :key="goal.id"
                            class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
                        >
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="text-3xl">{{ getGoalType(goal.goal_type).icon }}</div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ getGoalType(goal.goal_type).label }}</h3>
                                        <p class="text-sm text-gray-600">Target: {{ goal.target_amount }} {{ getGoalType(goal.goal_type).unit }}</p>
                                    </div>
                                </div>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <EditIcon class="h-5 w-5" />
                                </button>
                            </div>

                            <div v-if="goal.description" class="mb-4">
                                <p class="text-sm text-gray-600">{{ goal.description }}</p>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Progress</span>
                                    <span class="text-sm font-bold text-blue-600">{{ getGoalProgress(goal).toFixed(0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div 
                                        class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500"
                                        :style="{ width: `${getGoalProgress(goal)}%` }"
                                    ></div>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs text-gray-600">{{ goal.current_progress }} / {{ goal.target_amount }}</span>
                                    <span class="text-xs text-gray-600">{{ getDaysRemaining(goal.target_date) }} days left</span>
                                </div>
                            </div>

                            <!-- Target Date -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Target Date:</span>
                                <span class="font-semibold text-gray-900">{{ formatDate(goal.target_date) }}</span>
                            </div>

                            <!-- Update Progress -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Update Progress</label>
                                <div class="flex gap-2">
                                    <input
                                        type="number"
                                        :value="goal.current_progress"
                                        @change="(e) => updateProgress(goal.id, Number((e.target as HTMLInputElement).value))"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    <button
                                        @click="updateProgress(goal.id, goal.target_amount)"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold"
                                    >
                                        <CheckCircleIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Goals -->
                <div v-if="completedGoals.length > 0" class="mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Completed Goals 🎉</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div
                            v-for="goal in completedGoals"
                            :key="goal.id"
                            class="bg-green-50 border border-green-200 rounded-lg p-4"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <CheckCircleIcon class="h-5 w-5 text-green-600" />
                                <h3 class="font-semibold text-gray-900">{{ getGoalType(goal.goal_type).label }}</h3>
                            </div>
                            <p class="text-sm text-gray-600">Target: {{ goal.target_amount }} {{ getGoalType(goal.goal_type).unit }}</p>
                            <p class="text-xs text-gray-500 mt-2">Completed: {{ formatDate(goal.target_date) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="activeGoals.length === 0 && completedGoals.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                    <TargetIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Goals Yet</h3>
                    <p class="text-gray-600 mb-4">Start tracking your progress by creating your first goal!</p>
                    <button
                        @click="showCreateModal = true"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
                    >
                        <PlusCircleIcon class="h-5 w-5 mr-2" />
                        Create Your First Goal
                    </button>
                </div>

                <!-- Create Goal Modal -->
                <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click="showCreateModal = false">
                    <div class="bg-white rounded-2xl p-6 max-w-md w-full" @click.stop>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Create New Goal</h3>
                        
                        <form @submit.prevent="createGoal" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Goal Type</label>
                                <select
                                    v-model="form.goal_type"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option v-for="type in goalTypes" :key="type.value" :value="type.value">
                                        {{ type.icon }} {{ type.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Target Amount ({{ getGoalType(form.goal_type).unit }})
                                </label>
                                <input
                                    v-model.number="form.target_amount"
                                    type="number"
                                    required
                                    min="1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Target Date</label>
                                <input
                                    v-model="form.target_date"
                                    type="date"
                                    required
                                    :min="new Date().toISOString().split('T')[0]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Why is this goal important to you?"
                                ></textarea>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    type="submit"
                                    class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
                                >
                                    Create Goal
                                </button>
                                <button
                                    type="button"
                                    @click="showCreateModal = false"
                                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-semibold"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
