<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    PlusIcon,
    FireIcon,
    TrashIcon,
    StarIcon,
    BookOpenIcon,
    BoltIcon,
    HeartIcon,
    SparklesIcon,
    SunIcon,
    BeakerIcon,
    CakeIcon,
    MoonIcon,
    PencilIcon,
    FlagIcon,
    BanknotesIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface HabitDay {
    date: string;
    completed: boolean;
}

interface Habit {
    id: number;
    name: string;
    icon: string;
    color: string;
    frequency: string;
    streak: number;
    today_completed: boolean;
}

interface WeekProgress {
    id: number;
    name: string;
    icon: string;
    color: string;
    days: HabitDay[];
    completed_count: number;
}

const props = defineProps<{
    habits: Habit[];
    weekProgress: WeekProgress[];
}>();

const showAddModal = ref(false);

const form = useForm({
    name: '',
    icon: 'star',
    color: '#10b981',
    frequency: 'daily',
});

const iconOptions = [
    { name: 'star', component: StarIcon, label: 'Star' },
    { name: 'book', component: BookOpenIcon, label: 'Book' },
    { name: 'bolt', component: BoltIcon, label: 'Exercise' },
    { name: 'heart', component: HeartIcon, label: 'Health' },
    { name: 'sparkles', component: SparklesIcon, label: 'Sparkles' },
    { name: 'sun', component: SunIcon, label: 'Morning' },
    { name: 'beaker', component: BeakerIcon, label: 'Water' },
    { name: 'cake', component: CakeIcon, label: 'Food' },
    { name: 'moon', component: MoonIcon, label: 'Sleep' },
    { name: 'pencil', component: PencilIcon, label: 'Write' },
    { name: 'flag', component: FlagIcon, label: 'Goal' },
    { name: 'banknotes', component: BanknotesIcon, label: 'Money' },
];

const colors = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#ec4899'];

const getIconComponent = (iconName: string) => {
    const icon = iconOptions.find(i => i.name === iconName);
    return icon?.component || StarIcon;
};

const getDayLabel = (dateStr: string) => {
    const date = new Date(dateStr);
    return ['S', 'M', 'T', 'W', 'T', 'F', 'S'][date.getDay()];
};

const logHabit = (habitId: number, date?: string) => {
    router.post(route('lifeplus.habits.log', habitId), { date }, {
        preserveScroll: true,
    });
};

const deleteHabit = (id: number) => {
    if (confirm('Delete this habit?')) {
        router.delete(route('lifeplus.habits.destroy', id), {
            preserveScroll: true,
        });
    }
};

const submitHabit = () => {
    form.post(route('lifeplus.habits.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Habit Tracker</h1>
            <button 
                @click="showAddModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-purple-500 text-white rounded-xl font-medium hover:bg-purple-600 transition-colors"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Add Habit
            </button>
        </div>

        <!-- Week Overview -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4">This Week</h2>
            
            <div v-if="weekProgress.length === 0" class="text-center py-8">
                <p class="text-gray-500">No habits yet</p>
                <button 
                    @click="showAddModal = true"
                    class="mt-3 text-purple-600 font-medium"
                >
                    Create your first habit
                </button>
            </div>
            
            <div v-else class="space-y-4">
                <div 
                    v-for="habit in weekProgress" 
                    :key="habit.id"
                    class="group"
                >
                    <div class="flex items-center gap-3 mb-2">
                        <component 
                            :is="getIconComponent(habit.icon)" 
                            class="h-5 w-5"
                            :style="{ color: habit.color }"
                            aria-hidden="true"
                        />
                        <span class="text-sm font-medium text-gray-900 flex-1">{{ habit.name }}</span>
                        <span class="text-xs text-gray-500">{{ habit.completed_count }}/7</span>
                        <button 
                            @click="deleteHabit(habit.id)"
                            class="p-1.5 rounded-lg opacity-0 group-hover:opacity-100 hover:bg-red-50 transition-all"
                            aria-label="Delete habit"
                        >
                            <TrashIcon class="h-4 w-4 text-red-500" aria-hidden="true" />
                        </button>
                    </div>
                    
                    <div class="flex gap-1.5">
                        <button 
                            v-for="(day, idx) in habit.days" 
                            :key="idx"
                            @click="logHabit(habit.id, day.date)"
                            class="flex-1 flex flex-col items-center gap-1 py-2 rounded-lg transition-colors"
                            :class="day.completed ? 'bg-emerald-100' : 'bg-gray-50 hover:bg-gray-100'"
                        >
                            <span class="text-[10px] text-gray-500">{{ getDayLabel(day.date) }}</span>
                            <div 
                                class="w-6 h-6 rounded-full flex items-center justify-center text-xs"
                                :class="day.completed ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-400'"
                            >
                                {{ day.completed ? '✓' : '' }}
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Habits with Streaks -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4">Your Habits</h2>
            
            <div class="space-y-3">
                <div 
                    v-for="habit in habits" 
                    :key="habit.id"
                    class="flex items-center gap-3 p-3 rounded-xl bg-gray-50"
                >
                    <div 
                        class="p-2 rounded-lg"
                        :style="{ backgroundColor: habit.color + '20' }"
                    >
                        <component 
                            :is="getIconComponent(habit.icon)" 
                            class="h-6 w-6"
                            :style="{ color: habit.color }"
                            aria-hidden="true"
                        />
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ habit.name }}</p>
                        <p class="text-xs text-gray-500 capitalize">{{ habit.frequency }}</p>
                    </div>
                    
                    <div v-if="habit.streak > 0" class="flex items-center gap-1 px-2 py-1 bg-orange-100 rounded-full">
                        <FireIcon class="h-4 w-4 text-orange-500" aria-hidden="true" />
                        <span class="text-sm font-medium text-orange-600">{{ habit.streak }}</span>
                    </div>
                    
                    <button 
                        @click="logHabit(habit.id)"
                        class="px-4 py-2 rounded-lg font-medium text-sm transition-colors"
                        :class="habit.today_completed 
                            ? 'bg-emerald-100 text-emerald-700' 
                            : 'bg-purple-500 text-white hover:bg-purple-600'"
                    >
                        {{ habit.today_completed ? 'Done ✓' : 'Log Today' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Habit Modal -->
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
                            <h2 class="text-lg font-semibold text-gray-900">Add Habit</h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitHabit" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Habit Name</label>
                                <input 
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="e.g., Morning exercise"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
                                <div class="grid grid-cols-6 gap-2">
                                    <button 
                                        v-for="icon in iconOptions" 
                                        :key="icon.name"
                                        type="button"
                                        @click="form.icon = icon.name"
                                        :class="[
                                            'p-2 rounded-lg flex items-center justify-center transition-colors',
                                            form.icon === icon.name ? 'bg-purple-100 ring-2 ring-purple-500' : 'bg-gray-100 hover:bg-gray-200'
                                        ]"
                                        :title="icon.label"
                                    >
                                        <component 
                                            :is="icon.component" 
                                            class="h-5 w-5 text-gray-700"
                                            aria-hidden="true"
                                        />
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                                <div class="flex gap-2">
                                    <button 
                                        v-for="color in colors" 
                                        :key="color"
                                        type="button"
                                        @click="form.color = color"
                                        :class="[
                                            'w-8 h-8 rounded-full transition-transform',
                                            form.color === color ? 'ring-2 ring-offset-2 ring-gray-400 scale-110' : ''
                                        ]"
                                        :style="{ backgroundColor: color }"
                                    ></button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                                <select 
                                    v-model="form.frequency"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                >
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                </select>
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-purple-500 text-white rounded-xl font-medium hover:bg-purple-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Habit' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
