<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { 
    CalendarIcon, 
    ChevronLeftIcon, 
    ChevronRightIcon, 
    PlusIcon, 
    ClockIcon,
    SparklesIcon,
    XMarkIcon,
    PencilIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

interface Post {
    id: number;
    title: string;
    caption: string;
    status: string;
    date: string;
    platform_targets: string[];
    post_type: string;
    has_media: boolean;
    media_count: number;
    thumbnail: string | null;
}

interface Suggestion {
    date: string;
    type: string;
    name: string;
    suggestion: string;
}

interface WeeklyTheme {
    id: number;
    week_start: string;
    theme: string;
    description: string | null;
    color: string | null;
}

interface DefaultTheme {
    theme: string;
    description: string;
}

interface Props {
    posts: Post[];
    startDate: string;
    endDate: string;
    suggestedTimes: { day: string; times: string[] }[];
    suggestions: Suggestion[];
    weeklyThemes: WeeklyTheme[];
    defaultThemes: DefaultTheme[];
    business: {
        industry: string;
        timezone: string;
    };
}

const props = defineProps<Props>();

// Weekly theme modal state
const showThemeModal = ref(false);
const editingTheme = ref<WeeklyTheme | null>(null);
const selectedWeekStart = ref<string>('');

const themeForm = useForm({
    week_start: '',
    theme: '',
    description: '',
    color: 'violet',
});

// Posting times modal state
const showPostingTimesModal = ref(false);
const postingTimesForm = useForm({
    times: [...props.suggestedTimes],
});

const openPostingTimesModal = () => {
    postingTimesForm.times = JSON.parse(JSON.stringify(props.suggestedTimes));
    showPostingTimesModal.value = true;
};

const closePostingTimesModal = () => {
    showPostingTimesModal.value = false;
};

const addTime = (dayIndex: number) => {
    postingTimesForm.times[dayIndex].times.push('12:00');
};

const removeTime = (dayIndex: number, timeIndex: number) => {
    postingTimesForm.times[dayIndex].times.splice(timeIndex, 1);
};

const savePostingTimes = () => {
    postingTimesForm.post('/bizboost/calendar/posting-times', {
        preserveScroll: true,
        onSuccess: () => closePostingTimesModal(),
    });
};

const resetPostingTimes = () => {
    if (confirm('Reset posting times to industry defaults?')) {
        router.delete('/bizboost/calendar/posting-times', {
            preserveScroll: true,
            onSuccess: () => closePostingTimesModal(),
        });
    }
};

const themeColors = [
    { value: 'violet', class: 'bg-violet-500' },
    { value: 'blue', class: 'bg-blue-500' },
    { value: 'green', class: 'bg-green-500' },
    { value: 'yellow', class: 'bg-yellow-500' },
    { value: 'orange', class: 'bg-orange-500' },
    { value: 'pink', class: 'bg-pink-500' },
];

const openThemeModal = (weekStart: string, existingTheme?: WeeklyTheme) => {
    selectedWeekStart.value = weekStart;
    themeForm.week_start = weekStart;
    
    if (existingTheme) {
        editingTheme.value = existingTheme;
        themeForm.theme = existingTheme.theme;
        themeForm.description = existingTheme.description || '';
        themeForm.color = existingTheme.color || 'violet';
    } else {
        editingTheme.value = null;
        themeForm.theme = '';
        themeForm.description = '';
        themeForm.color = 'violet';
    }
    
    showThemeModal.value = true;
};

const closeThemeModal = () => {
    showThemeModal.value = false;
    editingTheme.value = null;
    themeForm.reset();
};

const saveTheme = () => {
    themeForm.post('/bizboost/calendar/weekly-themes', {
        preserveScroll: true,
        onSuccess: () => closeThemeModal(),
        onError: (errors) => {
            console.error('Save theme errors:', errors);
        },
    });
};

const deleteTheme = (id: number) => {
    if (confirm('Remove this weekly theme?')) {
        router.delete(`/bizboost/calendar/weekly-themes/${id}`, {
            preserveScroll: true,
        });
    }
};

const useDefaultTheme = (defaultTheme: DefaultTheme) => {
    themeForm.theme = defaultTheme.theme;
    themeForm.description = defaultTheme.description;
};

const getThemeForWeek = (weekStart: string): WeeklyTheme | undefined => {
    return props.weeklyThemes.find(t => t.week_start === weekStart);
};

const getThemeColorClass = (color: string | null): string => {
    const found = themeColors.find(c => c.value === color);
    return found?.class || 'bg-violet-500';
};

const currentDate = ref(new Date(props.startDate));

const statusColors: Record<string, string> = {
    draft: 'bg-gray-200 border-gray-400',
    scheduled: 'bg-blue-200 border-blue-400',
    publishing: 'bg-yellow-200 border-yellow-400',
    published: 'bg-green-200 border-green-400',
    failed: 'bg-red-200 border-red-400',
};

const monthName = computed(() => {
    return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const days = [];
    
    // Add empty slots for days before the first day of the month
    for (let i = 0; i < firstDay.getDay(); i++) {
        days.push(null);
    }
    
    // Add all days of the month
    for (let i = 1; i <= lastDay.getDate(); i++) {
        days.push(new Date(year, month, i));
    }
    
    return days;
});

const getPostsForDate = (date: Date | null) => {
    if (!date) return [];
    const dateStr = date.toISOString().split('T')[0];
    return props.posts.filter(p => p.date.startsWith(dateStr));
};

const getSuggestionForDate = (date: Date | null) => {
    if (!date) return null;
    const dateStr = date.toISOString().split('T')[0];
    return props.suggestions.find(s => s.date === dateStr);
};

const prevMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
    router.get(route('bizboost.calendar.index'), {
        start: currentDate.value.toISOString().split('T')[0],
    }, { preserveState: true });
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
    router.get(route('bizboost.calendar.index'), {
        start: currentDate.value.toISOString().split('T')[0],
    }, { preserveState: true });
};

const isToday = (date: Date | null) => {
    if (!date) return false;
    const today = new Date();
    return date.toDateString() === today.toDateString();
};

// Get weeks in the current month for theme display
const weeksInMonth = computed(() => {
    const weeks: { start: Date; end: Date; startStr: string }[] = [];
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    
    let current = new Date(year, month, 1);
    // Go to the Monday of the first week (getDay() returns 0 for Sunday, 1 for Monday, etc.)
    const dayOfWeek = current.getDay();
    const daysToSubtract = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // If Sunday, go back 6 days; otherwise go back to Monday
    current.setDate(current.getDate() - daysToSubtract);
    
    const monthEnd = new Date(year, month + 1, 0);
    
    while (current <= monthEnd) {
        const weekStart = new Date(current);
        const weekEnd = new Date(current);
        weekEnd.setDate(weekEnd.getDate() + 6);
        
        // Format date as YYYY-MM-DD without timezone issues
        const startStr = `${weekStart.getFullYear()}-${String(weekStart.getMonth() + 1).padStart(2, '0')}-${String(weekStart.getDate()).padStart(2, '0')}`;
        
        weeks.push({
            start: weekStart,
            end: weekEnd,
            startStr: startStr,
        });
        
        current.setDate(current.getDate() + 7);
    }
    
    return weeks;
});

const formatWeekRange = (start: Date, end: Date): string => {
    const startDay = start.getDate();
    const endDay = end.getDate();
    const startMonth = start.toLocaleDateString('en-US', { month: 'short' });
    const endMonth = end.toLocaleDateString('en-US', { month: 'short' });
    
    if (startMonth === endMonth) {
        return `${startMonth} ${startDay} - ${endDay}`;
    }
    return `${startMonth} ${startDay} - ${endMonth} ${endDay}`;
};
</script>

<template>
    <Head title="Content Calendar - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <CalendarIcon class="h-7 w-7 text-blue-600" aria-hidden="true" />
                            Content Calendar
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">Plan and schedule your social media content</p>
                    </div>
                    <Link
                        :href="route('bizboost.posts.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        Schedule Post
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Calendar -->
                    <div class="lg:col-span-3 bg-white rounded-lg shadow">
                        <!-- Month Navigation -->
                        <div class="flex items-center justify-between p-4 border-b">
                            <button @click="prevMonth" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Previous month">
                                <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                            <h2 class="text-lg font-semibold text-gray-900">{{ monthName }}</h2>
                            <button @click="nextMonth" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Next month">
                                <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>

                        <!-- Day Headers -->
                        <div class="grid grid-cols-7 border-b">
                            <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="p-2 text-center text-sm font-medium text-gray-500">
                                {{ day }}
                            </div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7">
                            <div
                                v-for="(date, index) in daysInMonth"
                                :key="index"
                                :class="[
                                    'min-h-24 p-2 border-b border-r',
                                    date ? 'bg-white' : 'bg-gray-50',
                                    isToday(date) ? 'bg-blue-50' : ''
                                ]"
                            >
                                <div v-if="date" class="h-full">
                                    <div :class="['text-sm font-medium mb-1', isToday(date) ? 'text-blue-600' : 'text-gray-900']">
                                        {{ date.getDate() }}
                                    </div>
                                    <div class="space-y-1">
                                        <div
                                            v-for="post in getPostsForDate(date).slice(0, 2)"
                                            :key="post.id"
                                            :class="['text-xs p-1 rounded truncate border-l-2', statusColors[post.status]]"
                                        >
                                            {{ post.title }}
                                        </div>
                                        <div v-if="getPostsForDate(date).length > 2" class="text-xs text-gray-500">
                                            +{{ getPostsForDate(date).length - 2 }} more
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Weekly Themes -->
                        <div class="bg-white rounded-lg shadow p-4">
                            <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <SparklesIcon class="h-5 w-5 text-violet-600" aria-hidden="true" />
                                Weekly Themes
                            </h3>
                            <div class="space-y-2">
                                <div
                                    v-for="week in weeksInMonth"
                                    :key="week.startStr"
                                    class="group relative"
                                >
                                    <div
                                        v-if="getThemeForWeek(week.startStr)"
                                        :class="[
                                            'p-2 rounded-lg border-l-4 cursor-pointer hover:bg-gray-50',
                                            getThemeColorClass(getThemeForWeek(week.startStr)?.color || null).replace('bg-', 'border-')
                                        ]"
                                        @click="openThemeModal(week.startStr, getThemeForWeek(week.startStr))"
                                    >
                                        <div class="text-xs text-gray-500">{{ formatWeekRange(week.start, week.end) }}</div>
                                        <div class="text-sm font-medium text-gray-900">{{ getThemeForWeek(week.startStr)?.theme }}</div>
                                        <div v-if="getThemeForWeek(week.startStr)?.description" class="text-xs text-gray-600 truncate">
                                            {{ getThemeForWeek(week.startStr)?.description }}
                                        </div>
                                    </div>
                                    <button
                                        v-else
                                        @click="openThemeModal(week.startStr)"
                                        class="w-full p-2 rounded-lg border border-dashed border-gray-300 text-left hover:border-violet-400 hover:bg-violet-50 transition-colors"
                                    >
                                        <div class="text-xs text-gray-500">{{ formatWeekRange(week.start, week.end) }}</div>
                                        <div class="text-sm text-gray-400 flex items-center gap-1">
                                            <PlusIcon class="h-3 w-3" aria-hidden="true" />
                                            Set theme
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Suggested Times -->
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <ClockIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                    Best Posting Times
                                </h3>
                                <button
                                    @click="openPostingTimesModal"
                                    class="p-1 hover:bg-gray-100 rounded text-gray-500 hover:text-blue-600"
                                    aria-label="Edit posting times"
                                >
                                    <PencilIcon class="h-4 w-4" aria-hidden="true" />
                                </button>
                            </div>
                            <div class="space-y-3">
                                <div v-for="schedule in suggestedTimes" :key="schedule.day">
                                    <div class="text-sm font-medium text-gray-700 capitalize">{{ schedule.day }}</div>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <span v-for="time in schedule.times" :key="time" class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">
                                            {{ time }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Holidays & Events -->
                        <div class="bg-white rounded-lg shadow p-4">
                            <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <CalendarIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                                Upcoming Holidays
                            </h3>
                            <div v-if="suggestions.length === 0" class="text-sm text-gray-500">
                                No upcoming holidays this month.
                            </div>
                            <div v-else class="space-y-3">
                                <div 
                                    v-for="suggestion in suggestions.slice(0, 5)" 
                                    :key="suggestion.date" 
                                    :class="[
                                        'p-2 rounded-lg border-l-4',
                                        suggestion.type === 'holiday' ? 'border-red-400 bg-red-50' : 'border-blue-400 bg-blue-50'
                                    ]"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900">{{ suggestion.name }}</span>
                                        <span 
                                            :class="[
                                                'text-xs px-2 py-0.5 rounded-full',
                                                suggestion.type === 'holiday' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'
                                            ]"
                                        >
                                            {{ suggestion.type === 'holiday' ? 'Holiday' : 'Event' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ new Date(suggestion.date).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) }}
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1 italic">💡 {{ suggestion.suggestion }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posting Times Modal -->
        <div v-if="showPostingTimesModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="closePostingTimesModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Edit Posting Times</h3>
                        <button @click="closePostingTimesModal" class="p-1 hover:bg-gray-100 rounded" aria-label="Close">
                            <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </button>
                    </div>

                    <form @submit.prevent="savePostingTimes" class="space-y-4">
                        <div v-for="(schedule, dayIndex) in postingTimesForm.times" :key="schedule.day" class="border-b pb-4 last:border-0">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 capitalize">{{ schedule.day }}</span>
                                <button
                                    type="button"
                                    @click="addTime(dayIndex)"
                                    class="text-xs text-blue-600 hover:text-blue-700"
                                >
                                    + Add time
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="(time, timeIndex) in schedule.times" :key="timeIndex" class="flex items-center gap-1">
                                    <input
                                        v-model="schedule.times[timeIndex]"
                                        type="time"
                                        class="rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <button
                                        v-if="schedule.times.length > 1"
                                        type="button"
                                        @click="removeTime(dayIndex, timeIndex)"
                                        class="p-1 text-gray-400 hover:text-red-500"
                                        aria-label="Remove time"
                                    >
                                        <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="postingTimesForm.processing"
                                class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ postingTimesForm.processing ? 'Saving...' : 'Save Times' }}
                            </button>
                            <button
                                type="button"
                                @click="resetPostingTimes"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Reset to Default
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Weekly Theme Modal -->
        <div v-if="showThemeModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="closeThemeModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ editingTheme ? 'Edit Weekly Theme' : 'Set Weekly Theme' }}
                        </h3>
                        <button @click="closeThemeModal" class="p-1 hover:bg-gray-100 rounded" aria-label="Close">
                            <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </button>
                    </div>

                    <form @submit.prevent="saveTheme" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Theme *</label>
                            <input
                                v-model="themeForm.theme"
                                type="text"
                                placeholder="e.g., Product Spotlight, Customer Week"
                                class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea
                                v-model="themeForm.description"
                                rows="2"
                                placeholder="Brief description of the theme focus"
                                class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <div class="flex gap-2">
                                <button
                                    v-for="color in themeColors"
                                    :key="color.value"
                                    type="button"
                                    @click="themeForm.color = color.value"
                                    :class="[
                                        'w-8 h-8 rounded-full transition-all',
                                        color.class,
                                        themeForm.color === color.value ? 'ring-2 ring-offset-2 ring-violet-500' : 'hover:scale-110'
                                    ]"
                                    :aria-label="`Select ${color.value} color`"
                                />
                            </div>
                        </div>

                        <!-- Quick suggestions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Ideas</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="dt in defaultThemes.slice(0, 4)"
                                    :key="dt.theme"
                                    type="button"
                                    @click="useDefaultTheme(dt)"
                                    class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-full text-gray-700"
                                >
                                    {{ dt.theme }}
                                </button>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="themeForm.processing"
                                class="flex-1 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50"
                            >
                                {{ themeForm.processing ? 'Saving...' : 'Save Theme' }}
                            </button>
                            <button
                                v-if="editingTheme"
                                type="button"
                                @click="deleteTheme(editingTheme.id)"
                                class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                            >
                                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                            <button
                                type="button"
                                @click="closeThemeModal"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
