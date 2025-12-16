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
        <!-- Greeting -->
        <div class="pt-2">
            <h1 class="text-2xl font-bold text-gray-900">{{ getGreeting() }}, {{ user?.name?.split(' ')[0] }}!</h1>
            <p class="text-gray-500 text-sm mt-1">Here's your day at a glance</p>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-3 gap-3">
            <Link 
                :href="route('lifeplus.money.index')"
                class="flex flex-col items-center justify-center p-4 bg-emerald-50 rounded-2xl hover:bg-emerald-100 transition-colors"
            >
                <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center mb-2">
                    <BanknotesIcon class="h-5 w-5 text-white" aria-hidden="true" />
                </div>
                <span class="text-xs font-medium text-emerald-700">+ Expense</span>
            </Link>
            
            <Link 
                :href="route('lifeplus.tasks.index')"
                class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-2xl hover:bg-blue-100 transition-colors"
            >
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center mb-2">
                    <ClipboardDocumentCheckIcon class="h-5 w-5 text-white" aria-hidden="true" />
                </div>
                <span class="text-xs font-medium text-blue-700">+ Task</span>
            </Link>
            
            <Link 
                :href="route('lifeplus.habits.index')"
                class="flex flex-col items-center justify-center p-4 bg-purple-50 rounded-2xl hover:bg-purple-100 transition-colors"
            >
                <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center mb-2">
                    <SparklesIcon class="h-5 w-5 text-white" aria-hidden="true" />
                </div>
                <span class="text-xs font-medium text-purple-700">+ Habit</span>
            </Link>
        </div>

        <!-- Budget Overview Card -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-900">This Month</h2>
                <Link :href="route('lifeplus.money.index')" class="text-emerald-600 text-sm font-medium">
                    View All
                </Link>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Spent</span>
                    <span class="font-semibold text-gray-900">{{ formatCurrency(monthSummary.total_spent) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Budget</span>
                    <span class="font-medium text-gray-600">{{ formatCurrency(monthSummary.budget) }}</span>
                </div>
                
                <div class="relative h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div 
                        class="absolute inset-y-0 left-0 rounded-full transition-all"
                        :class="monthSummary.is_over_budget ? 'bg-red-500' : 'bg-emerald-500'"
                        :style="{ width: `${Math.min(100, monthSummary.percentage)}%` }"
                    ></div>
                </div>
                
                <p class="text-xs" :class="monthSummary.is_over_budget ? 'text-red-600' : 'text-gray-500'">
                    {{ monthSummary.is_over_budget ? 'Over budget by' : 'Remaining:' }}
                    {{ formatCurrency(Math.abs(monthSummary.remaining)) }}
                </p>
            </div>
        </div>

        <!-- Today's Tasks -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-900">Today's Tasks</h2>
                <Link :href="route('lifeplus.tasks.index')" class="text-blue-600 text-sm font-medium">
                    View All
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
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-900">Habit Tracker</h2>
                <Link :href="route('lifeplus.habits.index')" class="text-purple-600 text-sm font-medium">
                    View All
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
        <div v-if="dailyTip" class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-4 border border-amber-100">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <LightBulbIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-amber-900 text-sm">Daily Tip</h3>
                    <p class="text-amber-800 text-sm mt-1 leading-relaxed">
                        {{ dailyTip.content?.substring(0, 150) }}{{ dailyTip.content?.length > 150 ? '...' : '' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Hub Link -->
        <Link 
            href="/apps"
            class="block bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-4 text-white shadow-lg shadow-emerald-200"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold">Explore MyGrowNet Hub</h3>
                    <p class="text-emerald-100 text-sm mt-0.5">Discover more apps and services</p>
                </div>
                <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
            </div>
        </Link>
    </div>
</template>
