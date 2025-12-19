<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    PlusIcon,
    BanknotesIcon,
    ClipboardDocumentCheckIcon,
    SparklesIcon,
    LightBulbIcon,
    ArrowRightIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    StarIcon,
    BookOpenIcon,
    BoltIcon,
    HeartIcon,
    SunIcon,
    BeakerIcon,
    CakeIcon,
    MoonIcon,
    PencilIcon,
    FlagIcon,
} from '@heroicons/vue/24/outline';

const getIconComponent = (iconName: string) => {
    const iconMap: Record<string, any> = {
        star: StarIcon,
        book: BookOpenIcon,
        bolt: BoltIcon,
        heart: HeartIcon,
        sparkles: SparklesIcon,
        sun: SunIcon,
        beaker: BeakerIcon,
        cake: CakeIcon,
        moon: MoonIcon,
        pencil: PencilIcon,
        flag: FlagIcon,
        banknotes: BanknotesIcon,
    };
    return iconMap[iconName] || StarIcon;
};

defineOptions({ layout: LifePlusLayout });

interface Task {
    id: number;
    title: string;
    priority: string;
    priority_color: string;
    is_completed: boolean;
    is_overdue: boolean;
}

interface HabitProgress {
    id: number;
    name: string;
    icon: string;
    color: string;
    days: { date: string; completed: boolean }[];
    completed_count: number;
}

interface MonthSummary {
    total_spent: number;
    budget: number;
    remaining: number;
    percentage: number;
    is_over_budget: boolean;
}

interface DailyTip {
    id: number;
    title: string;
    content: string;
    category_icon: string;
}

const props = defineProps<{
    todayTasks: Task[];
    taskStats: { completed: number; pending: number; today: number; overdue: number };
    habits: HabitProgress[];
    monthSummary: MonthSummary;
    dailyTip: DailyTip | null;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
};

const formatCurrency = (amount: number) => {
    return 'K ' + amount.toLocaleString();
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Greeting with Gradient -->
        <div class="pt-2 pb-4">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                {{ getGreeting() }}, {{ user?.name?.split(' ')[0] }}!
            </h1>
            <div class="flex items-center gap-2 mt-2">
                <SparklesIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                <p class="text-gray-600 text-sm">Here's your day at a glance</p>
            </div>
        </div>

        <!-- Quick Actions with Gradients -->
        <div class="grid grid-cols-3 gap-3">
            <Link 
                :href="route('lifeplus.money.index')"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl hover:shadow-lg hover:scale-105 transition-all shadow-md"
            >
                <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-2 shadow-lg">
                    <BanknotesIcon class="h-6 w-6 text-white" aria-hidden="true" />
                </div>
                <span class="text-xs font-semibold text-white">+ Expense</span>
            </Link>
            
            <Link 
                :href="route('lifeplus.tasks.index')"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl hover:shadow-lg hover:scale-105 transition-all shadow-md"
            >
                <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-2 shadow-lg">
                    <ClipboardDocumentCheckIcon class="h-6 w-6 text-white" aria-hidden="true" />
                </div>
                <span class="text-xs font-semibold text-white">+ Task</span>
            </Link>
            
            <Link 
                :href="route('lifeplus.habits.index')"
                class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl hover:shadow-lg hover:scale-105 transition-all shadow-md"
            >
                <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-2 shadow-lg">
                    <SparklesIcon class="h-6 w-6 text-white" aria-hidden="true" />
                </div>
                <span class="text-xs font-semibold text-white">+ Habit</span>
            </Link>
        </div>

        <!-- Budget Overview Card with Gradient -->
        <div class="bg-gradient-to-br from-white to-emerald-50 rounded-3xl p-5 shadow-lg border border-emerald-100">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-md">
                        <BanknotesIcon class="h-4 w-4 text-white" aria-hidden="true" />
                    </div>
                    <h2 class="font-bold text-gray-900">This Month</h2>
                </div>
                <Link :href="route('lifeplus.money.index')" class="text-emerald-600 text-sm font-semibold hover:text-emerald-700">
                    View All →
                </Link>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-medium">Spent</span>
                    <span class="font-bold text-gray-900 text-lg">{{ formatCurrency(monthSummary.total_spent) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-medium">Budget</span>
                    <span class="font-semibold text-gray-700">{{ formatCurrency(monthSummary.budget) }}</span>
                </div>
                
                <div class="relative h-3 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                    <div 
                        class="absolute inset-y-0 left-0 rounded-full transition-all shadow-md"
                        :class="monthSummary.is_over_budget ? 'bg-gradient-to-r from-red-500 to-pink-500' : 'bg-gradient-to-r from-emerald-500 to-green-500'"
                        :style="{ width: `${Math.min(100, monthSummary.percentage)}%` }"
                    ></div>
                </div>
                
                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center gap-1">
                        <component 
                            :is="monthSummary.is_over_budget ? ExclamationCircleIcon : CheckCircleIcon"
                            class="h-4 w-4"
                            :class="monthSummary.is_over_budget ? 'text-red-600' : 'text-emerald-600'"
                            aria-hidden="true"
                        />
                        <p class="text-xs font-semibold" :class="monthSummary.is_over_budget ? 'text-red-600' : 'text-emerald-600'">
                            {{ monthSummary.is_over_budget ? 'Over budget' : 'On track' }}
                        </p>
                    </div>
                    <p class="text-xs font-bold" :class="monthSummary.is_over_budget ? 'text-red-600' : 'text-gray-700'">
                        {{ formatCurrency(Math.abs(monthSummary.remaining)) }} {{ monthSummary.is_over_budget ? 'over' : 'left' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Today's Tasks -->
        <div class="bg-gradient-to-br from-white to-blue-50 rounded-3xl p-5 shadow-lg border border-blue-100">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
                        <ClipboardDocumentCheckIcon class="h-4 w-4 text-white" aria-hidden="true" />
                    </div>
                    <h2 class="font-bold text-gray-900">Today's Tasks</h2>
                </div>
                <Link :href="route('lifeplus.tasks.index')" class="text-blue-600 text-sm font-semibold hover:text-blue-700">
                    View All →
                </Link>
            </div>
            
            <div v-if="todayTasks.length === 0" class="text-center py-6">
                <CheckCircleIcon class="h-10 w-10 text-emerald-400 mx-auto mb-2" aria-hidden="true" />
                <p class="text-gray-500 text-sm">No tasks for today!</p>
                <Link 
                    :href="route('lifeplus.tasks.index')"
                    class="inline-flex items-center gap-1 text-blue-600 text-sm font-medium mt-2"
                >
                    Add a task <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                </Link>
            </div>
            
            <div v-else class="space-y-2">
                <div 
                    v-for="task in todayTasks.slice(0, 4)" 
                    :key="task.id"
                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50"
                >
                    <div 
                        class="w-3 h-3 rounded-full flex-shrink-0"
                        :style="{ backgroundColor: task.priority_color }"
                    ></div>
                    <span 
                        class="text-sm flex-1 truncate"
                        :class="task.is_completed ? 'text-gray-400 line-through' : 'text-gray-700'"
                    >
                        {{ task.title }}
                    </span>
                </div>
                
                <div v-if="todayTasks.length > 4" class="text-center pt-2">
                    <Link 
                        :href="route('lifeplus.tasks.index')"
                        class="text-blue-600 text-sm font-medium"
                    >
                        +{{ todayTasks.length - 4 }} more tasks
                    </Link>
                </div>
            </div>
        </div>

        <!-- Habit Tracker -->
        <div class="bg-gradient-to-br from-white to-purple-50 rounded-3xl p-5 shadow-lg border border-purple-100">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-md">
                        <SparklesIcon class="h-4 w-4 text-white" aria-hidden="true" />
                    </div>
                    <h2 class="font-bold text-gray-900">Habit Tracker</h2>
                </div>
                <Link :href="route('lifeplus.habits.index')" class="text-purple-600 text-sm font-semibold hover:text-purple-700">
                    View All →
                </Link>
            </div>
            
            <div v-if="habits.length === 0" class="text-center py-6">
                <SparklesIcon class="h-10 w-10 text-purple-400 mx-auto mb-2" aria-hidden="true" />
                <p class="text-gray-500 text-sm">No habits yet</p>
                <Link 
                    :href="route('lifeplus.habits.index')"
                    class="inline-flex items-center gap-1 text-purple-600 text-sm font-medium mt-2"
                >
                    Create a habit <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                </Link>
            </div>
            
            <div v-else class="space-y-3">
                <div 
                    v-for="habit in habits.slice(0, 3)" 
                    :key="habit.id"
                    class="flex items-center gap-3"
                >
                    <component 
                        :is="getIconComponent(habit.icon)" 
                        class="h-5 w-5"
                        :style="{ color: habit.color }"
                        aria-hidden="true"
                    />
                    <span class="text-sm text-gray-700 flex-1 truncate">{{ habit.name }}</span>
                    <div class="flex gap-1">
                        <div 
                            v-for="(day, idx) in habit.days" 
                            :key="idx"
                            class="w-5 h-5 rounded flex items-center justify-center"
                            :class="day.completed ? 'bg-emerald-500' : 'bg-gray-100'"
                        >
                            <CheckCircleIcon 
                                v-if="day.completed"
                                class="h-3 w-3 text-white" 
                                aria-hidden="true"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Tip -->
        <div v-if="dailyTip" class="bg-gradient-to-br from-amber-400 via-orange-400 to-pink-400 rounded-3xl p-5 shadow-xl border border-amber-200">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-full bg-white/30 backdrop-blur-sm flex items-center justify-center flex-shrink-0 shadow-lg">
                    <LightBulbIcon class="h-6 w-6 text-white" aria-hidden="true" />
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <LightBulbIcon class="h-5 w-5 text-white" aria-hidden="true" />
                        <h3 class="font-bold text-white text-base drop-shadow-md">Daily Tip</h3>
                    </div>
                    <p class="text-white/95 text-sm leading-relaxed drop-shadow">
                        {{ dailyTip.content?.substring(0, 150) }}{{ dailyTip.content?.length > 150 ? '...' : '' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Hub Link -->
        <Link 
            href="/apps"
            class="block bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 rounded-3xl p-5 text-white shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
                        <BoltIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg drop-shadow-md">Explore MyGrowNet Hub</h3>
                        <p class="text-emerald-100 text-sm mt-1">Discover more apps and services</p>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                </div>
            </div>
        </Link>
    </div>
</template>
