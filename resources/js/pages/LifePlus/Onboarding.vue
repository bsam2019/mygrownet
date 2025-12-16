<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    SparklesIcon,
    BanknotesIcon,
    CheckCircleIcon,
    ArrowRightIcon,
    CurrencyDollarIcon,
    HeartIcon,
    FireIcon,
} from '@heroicons/vue/24/outline';

interface Category {
    id: number;
    name: string;
    icon: string;
}

const props = defineProps<{
    user: { name: string; email: string };
    categories: Category[];
}>();

// Setup mode: 'quick' or 'complete'
const setupMode = ref<'quick' | 'complete' | null>(null);
const step = ref(0); // 0 = mode selection

// Dynamic total steps based on mode
const totalSteps = computed(() => {
    if (setupMode.value === 'quick') return 2; // Budget + Habits
    return 4; // Budget + Habits + Health + Fitness
});

const form = useForm({
    setup_mode: 'quick',
    
    // Financial (Always included)
    monthly_income: '',
    monthly_budget: '',
    savings_percentage: '',
    
    // Habits (Always included)
    habits: [] as { name: string }[],
    
    // Health & Fitness (Optional - Complete mode only)
    age: '',
    gender: '',
    height: '',
    weight: '',
    fitness_goal: '',
    weekly_workout_goal: '',
    dietary_preference: '',
});

// Auto-calculate savings percentage when income and budget change
watch([() => form.monthly_income, () => form.monthly_budget], () => {
    if (form.monthly_income && form.monthly_budget) {
        const income = Number(form.monthly_income);
        const budget = Number(form.monthly_budget);
        if (income > 0 && budget > 0 && budget < income) {
            const savings = income - budget;
            const percentage = Math.round((savings / income) * 100);
            form.savings_percentage = percentage.toString();
        } else {
            form.savings_percentage = '';
        }
    }
});

const suggestedHabits = [
    { name: 'Track daily expenses', icon: 'banknotes' },
    { name: 'Save money daily', icon: 'banknotes' },
    { name: 'Morning exercise', icon: 'bolt' },
    { name: 'Drink water', icon: 'beaker' },
    { name: 'Read for 30 mins', icon: 'book' },
    { name: 'Plan meals', icon: 'cake' },
];

const fitnessGoals = [
    { value: 'lose_weight', label: 'Lose Weight', icon: 'bolt' },
    { value: 'gain_muscle', label: 'Build Muscle', icon: 'bolt' },
    { value: 'stay_healthy', label: 'Stay Healthy', icon: 'heart' },
    { value: 'improve_fitness', label: 'Get Fit', icon: 'bolt' },
];

const dietaryPreferences = [
    { value: 'balanced', label: 'Balanced', icon: 'heart' },
    { value: 'vegetarian', label: 'Vegetarian', icon: 'heart' },
    { value: 'traditional', label: 'Traditional', icon: 'cake' },
];

const iconComponents: Record<string, any> = {
    banknotes: BanknotesIcon,
    bolt: FireIcon,
    beaker: HeartIcon,
    book: CheckCircleIcon,
    cake: HeartIcon,
    heart: HeartIcon,
};

const newHabitName = ref('');

const addHabit = (name: string) => {
    if (name && !form.habits.find(h => h.name === name)) {
        form.habits.push({ name });
    }
    newHabitName.value = '';
};

const removeHabit = (index: number) => {
    form.habits.splice(index, 1);
};

const selectMode = (mode: 'quick' | 'complete') => {
    setupMode.value = mode;
    form.setup_mode = mode;
    step.value = 1;
};

const nextStep = () => {
    if (step.value < totalSteps.value) {
        step.value++;
    }
};

const prevStep = () => {
    if (step.value > 1) {
        step.value--;
    } else if (step.value === 1) {
        step.value = 0;
        setupMode.value = null;
    }
};

const completeOnboarding = () => {
    form.post(route('lifeplus.onboarding.complete'));
};

const skipOnboarding = () => {
    form.post(route('lifeplus.onboarding.complete'));
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-emerald-500 to-teal-600 flex flex-col">
        <!-- Header -->
        <div class="p-6 text-white text-center">
            <SparklesIcon class="h-12 w-12 mx-auto mb-3" aria-hidden="true" />
            <h1 class="text-2xl font-bold">Welcome to Life+</h1>
            <p class="text-emerald-100 mt-1">Your personal finance and wellness companion</p>
        </div>

        <!-- Progress (only show after mode selection) -->
        <div v-if="step > 0" class="px-6 mb-4">
            <div class="flex gap-2">
                <div 
                    v-for="s in totalSteps" 
                    :key="s"
                    class="flex-1 h-1.5 rounded-full transition-colors"
                    :class="s <= step ? 'bg-white' : 'bg-white/30'"
                ></div>
            </div>
            <p class="text-white/80 text-sm text-center mt-2">Step {{ step }} of {{ totalSteps }}</p>
        </div>

        <!-- Content Card -->
        <div class="flex-1 bg-white rounded-t-3xl p-6 overflow-y-auto">
            <!-- Step 0: Mode Selection -->
            <div v-if="step === 0" class="space-y-6">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">How would you like to start?</h2>
                    <p class="text-gray-500">Choose the setup that works best for you</p>
                </div>

                <!-- Quick Start Option -->
                <button 
                    @click="selectMode('quick')"
                    class="w-full p-6 border-2 border-gray-200 rounded-2xl hover:border-emerald-500 hover:bg-emerald-50 transition-all text-left group"
                >
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-emerald-100 rounded-xl group-hover:bg-emerald-200 transition-colors">
                            <BanknotesIcon class="h-8 w-8 text-emerald-600" aria-hidden="true" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Quick Start</h3>
                            <p class="text-sm text-gray-600 mb-3">
                                Focus on what matters most - managing your money and building good habits
                            </p>
                            <div class="flex items-center gap-2 text-sm text-emerald-600 font-medium">
                                <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                                <span>2 simple steps • 2 minutes</span>
                            </div>
                            <ul class="mt-3 space-y-1 text-sm text-gray-500">
                                <li>• Budget & savings tracking</li>
                                <li>• Daily habit building</li>
                            </ul>
                        </div>
                    </div>
                </button>

                <!-- Complete Setup Option -->
                <button 
                    @click="selectMode('complete')"
                    class="w-full p-6 border-2 border-gray-200 rounded-2xl hover:border-purple-500 hover:bg-purple-50 transition-all text-left group"
                >
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-purple-100 rounded-xl group-hover:bg-purple-200 transition-colors">
                            <HeartIcon class="h-8 w-8 text-purple-600" aria-hidden="true" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Complete Setup</h3>
                            <p class="text-sm text-gray-600 mb-3">
                                Get the full experience with health, fitness, and wellness tracking
                            </p>
                            <div class="flex items-center gap-2 text-sm text-purple-600 font-medium">
                                <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                                <span>4 steps • 5 minutes</span>
                            </div>
                            <ul class="mt-3 space-y-1 text-sm text-gray-500">
                                <li>• Everything in Quick Start</li>
                                <li>• Health & fitness goals</li>
                                <li>• Nutrition planning</li>
                            </ul>
                        </div>
                    </div>
                </button>

                <button 
                    @click="skipOnboarding"
                    class="w-full py-3 text-gray-500 hover:text-gray-700 text-sm font-medium transition-colors"
                >
                    Skip setup for now
                </button>
            </div>

            <!-- Step 1: Financial Setup (Both modes) -->
            <div v-if="step === 1" class="space-y-6">
                <div class="text-center">
                    <CurrencyDollarIcon class="h-16 w-16 text-emerald-500 mx-auto mb-3" aria-hidden="true" />
                    <h2 class="text-xl font-bold text-gray-900">Financial Planning</h2>
                    <p class="text-gray-500 mt-1">Track your income, expenses, and savings</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Income (K)</label>
                    <input 
                        v-model="form.monthly_income"
                        type="number"
                        min="0"
                        class="w-full px-4 py-4 text-2xl font-bold text-center border border-gray-300 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="0"
                    />
                    <p class="text-sm text-gray-400 text-center mt-2">Your total monthly income</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button 
                        v-for="amount in [2000, 5000, 10000, 20000]" 
                        :key="amount"
                        @click="form.monthly_income = amount.toString()"
                        class="py-2 bg-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors"
                    >
                        K {{ amount.toLocaleString() }}
                    </button>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Budget (K)</label>
                    <input 
                        v-model="form.monthly_budget"
                        type="number"
                        min="0"
                        class="w-full px-4 py-4 text-2xl font-bold text-center border border-gray-300 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="0"
                    />
                    <p class="text-sm text-gray-400 text-center mt-2">How much you plan to spend</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button 
                        v-for="amount in [500, 1000, 2000, 5000]" 
                        :key="amount"
                        @click="form.monthly_budget = amount.toString()"
                        class="py-2 bg-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors"
                    >
                        K {{ amount.toLocaleString() }}
                    </button>
                </div>

                <!-- Auto-calculated Savings -->
                <div v-if="form.savings_percentage" class="bg-emerald-50 rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Automatic Savings</span>
                        <span class="text-2xl font-bold text-emerald-600">{{ form.savings_percentage }}%</span>
                    </div>
                    <p class="text-sm text-gray-600">
                        You'll save <span class="font-bold text-emerald-600">K {{ (Number(form.monthly_income) - Number(form.monthly_budget)).toLocaleString() }}</span> per month
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        Great job! Saving {{ form.savings_percentage }}% of your income
                    </p>
                </div>

                <div v-else-if="form.monthly_income && form.monthly_budget" class="bg-amber-50 rounded-2xl p-4 flex items-start gap-2">
                    <span class="text-amber-600 text-lg">⚠</span>
                    <p class="text-sm text-amber-800">
                        Your budget is equal to or higher than your income. Try to reduce expenses to save money.
                    </p>
                </div>
            </div>

            <!-- Step 2: Habits (Both modes) -->
            <div v-if="step === 2" class="space-y-6">
                <div class="text-center">
                    <CheckCircleIcon class="h-16 w-16 text-purple-500 mx-auto mb-3" aria-hidden="true" />
                    <h2 class="text-xl font-bold text-gray-900">Build Good Habits</h2>
                    <p class="text-gray-500 mt-1">Choose habits you want to track daily</p>
                </div>

                <!-- Selected Habits -->
                <div v-if="form.habits.length > 0" class="flex flex-wrap gap-2">
                    <span 
                        v-for="(habit, index) in form.habits" 
                        :key="index"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-100 text-purple-700 rounded-full text-sm font-medium"
                    >
                        {{ habit.name }}
                        <button @click="removeHabit(index)" class="ml-1 hover:text-purple-900">×</button>
                    </span>
                </div>

                <!-- Suggested Habits -->
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">Suggested habits:</p>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            v-for="habit in suggestedHabits.filter(h => !form.habits.find(fh => fh.name === h.name))" 
                            :key="habit.name"
                            @click="addHabit(habit.name)"
                            class="px-3 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition-colors"
                        >
                            {{ habit.icon }} {{ habit.name }}
                        </button>
                    </div>
                </div>

                <!-- Custom Habit -->
                <div class="flex gap-2">
                    <input 
                        v-model="newHabitName"
                        @keyup.enter="addHabit(newHabitName)"
                        type="text"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Add your own habit..."
                    />
                    <button 
                        @click="addHabit(newHabitName)"
                        class="px-4 py-3 bg-purple-500 text-white rounded-xl font-medium hover:bg-purple-600 transition-colors"
                    >
                        Add
                    </button>
                </div>

                <!-- Quick Start ends here -->
                <div v-if="setupMode === 'quick'" class="bg-emerald-50 rounded-2xl p-4 text-center">
                    <p class="text-sm text-emerald-800 font-medium">
                        🎉 You're ready to start! You can add health & fitness tracking anytime from settings.
                    </p>
                </div>
            </div>

            <!-- Step 3: Health Profile (Complete mode only) -->
            <div v-if="step === 3 && setupMode === 'complete'" class="space-y-6">
                <div class="text-center">
                    <HeartIcon class="h-16 w-16 text-red-500 mx-auto mb-3" aria-hidden="true" />
                    <h2 class="text-xl font-bold text-gray-900">Health Profile</h2>
                    <p class="text-gray-500 mt-1">Help us personalize your health journey</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                        <input 
                            v-model="form.age"
                            type="number"
                            min="13"
                            max="120"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            placeholder="25"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <select 
                            v-model="form.gender"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        >
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Height (cm)</label>
                        <input 
                            v-model="form.height"
                            type="number"
                            min="100"
                            max="250"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            placeholder="170"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                        <input 
                            v-model="form.weight"
                            type="number"
                            min="30"
                            max="300"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            placeholder="70"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dietary Preference</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button 
                            v-for="diet in dietaryPreferences" 
                            :key="diet.value"
                            @click="form.dietary_preference = diet.value"
                            class="p-3 border rounded-xl transition-colors text-center"
                            :class="form.dietary_preference === diet.value 
                                ? 'border-red-500 bg-red-50 text-red-700' 
                                : 'border-gray-300 hover:border-gray-400'"
                        >
                            <div class="text-2xl mb-1">{{ diet.icon }}</div>
                            <div class="text-sm font-medium">{{ diet.label }}</div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Fitness Goals (Complete mode only) -->
            <div v-if="step === 4 && setupMode === 'complete'" class="space-y-6">
                <div class="text-center">
                    <FireIcon class="h-16 w-16 text-orange-500 mx-auto mb-3" aria-hidden="true" />
                    <h2 class="text-xl font-bold text-gray-900">Fitness Goals</h2>
                    <p class="text-gray-500 mt-1">What do you want to achieve?</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Primary Goal</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            v-for="goal in fitnessGoals" 
                            :key="goal.value"
                            @click="form.fitness_goal = goal.value"
                            class="p-4 border rounded-xl transition-colors text-center"
                            :class="form.fitness_goal === goal.value 
                                ? 'border-orange-500 bg-orange-50 text-orange-700' 
                                : 'border-gray-300 hover:border-gray-400'"
                        >
                            <div class="text-3xl mb-2">{{ goal.icon }}</div>
                            <div class="text-sm font-medium">{{ goal.label }}</div>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Weekly Workout Goal</label>
                    <div class="flex gap-2">
                        <button 
                            v-for="days in [2, 3, 4, 5, 6, 7]" 
                            :key="days"
                            @click="form.weekly_workout_goal = days.toString()"
                            class="flex-1 py-3 border rounded-xl transition-colors"
                            :class="form.weekly_workout_goal === days.toString()
                                ? 'border-orange-500 bg-orange-50 text-orange-700 font-bold' 
                                : 'border-gray-300 hover:border-gray-400'"
                        >
                            {{ days }}
                        </button>
                    </div>
                    <p class="text-sm text-gray-400 text-center mt-2">days per week</p>
                </div>

                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-6 text-white text-center">
                    <p class="text-lg font-semibold mb-2">🎉 Your complete profile is ready!</p>
                    <p class="text-emerald-100 text-sm">
                        Life+ will help you track progress and achieve your goals
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="bg-white p-6 border-t border-gray-100 safe-area-bottom">
            <div class="flex gap-3">
                <button 
                    v-if="step > 0"
                    @click="prevStep"
                    class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors"
                >
                    Back
                </button>
                <button 
                    v-if="step > 0 && step < totalSteps"
                    @click="nextStep"
                    class="flex-1 py-3 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 transition-colors flex items-center justify-center gap-2"
                >
                    Next
                    <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                </button>
                <button 
                    v-if="step === totalSteps"
                    @click="completeOnboarding"
                    :disabled="form.processing"
                    class="flex-1 py-3 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 disabled:opacity-50 transition-colors"
                >
                    {{ form.processing ? 'Setting up...' : 'Get Started' }}
                </button>
            </div>
        </div>
    </div>
</template>
