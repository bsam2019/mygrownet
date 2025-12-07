<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import StatCard from '@/Components/BizBoost/Dashboard/StatCard.vue';
import ActivityFeed from '@/Components/BizBoost/Dashboard/ActivityFeed.vue';
import QuickActionsWidget from '@/Components/BizBoost/Dashboard/QuickActionsWidget.vue';
import DashboardWidget from '@/Components/BizBoost/Dashboard/DashboardWidget.vue';
import DashboardCustomizer from '@/Components/BizBoost/Dashboard/DashboardCustomizer.vue';
import { useBizBoostDashboard } from '@/composables/useBizBoostDashboard';
import {
    ShoppingBagIcon,
    UsersIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    SparklesIcon,
    ArrowRightIcon,
    CalendarDaysIcon,
    ChartBarIcon,
    LightBulbIcon,
    CommandLineIcon,
    Cog6ToothIcon,
    PencilSquareIcon,
    CheckIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    stats: {
        products: number;
        products_previous?: number;
        customers: number;
        customers_previous?: number;
        posts: number;
        posts_previous?: number;
        sales_total: number;
        sales_previous?: number;
        posts_this_month: number;
        ai_credits_used: number;
        ai_credits_limit: number;
    };
    recentPosts: Array<{
        id: number;
        caption: string;
        status: string;
        scheduled_at: string | null;
        published_at: string | null;
    }>;
    upcomingPosts: Array<{
        id: number;
        caption: string;
        scheduled_at: string;
    }>;
    recommendations: Array<{
        id?: string;
        title: string;
        description: string;
        action: string;
        actionUrl: string;
        priority?: 'high' | 'medium' | 'low';
    }>;
    recentActivity?: Array<{
        id: string | number;
        type: 'sale' | 'customer' | 'post' | 'product' | 'engagement';
        title: string;
        description?: string;
        amount?: number;
        timestamp: string;
        href?: string;
    }>;
    pendingTasks?: number;
    scheduledPostsToday?: number;
    lowStockProducts?: number;
    sparklineData?: {
        sales: number[];
        customers: number[];
        posts: number[];
        products: number[];
    };
}

const props = withDefaults(defineProps<Props>(), {
    pendingTasks: 0,
    scheduledPostsToday: 0,
    lowStockProducts: 0,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const business = computed(() => page.props.business);

// Dashboard customization
const {
    layout,
    mainColumnWidgets,
    sidebarWidgets,
    isCustomizing,
    toggleCustomizing,
    reorderWidgets,
    startDrag,
    endDrag,
} = useBizBoostDashboard();

const showCustomizer = ref(false);

// Get widget config by ID (reactive)
const getWidget = (widgetId: string) => {
    return layout.value.widgets.find(w => w.id === widgetId);
};

// Check if a widget is visible
const isWidgetVisible = (widgetId: string) => {
    const widget = getWidget(widgetId);
    return widget?.visible ?? true;
};

// Full-width widgets at the top (stats, quick-actions) - sorted by order
const topWidgets = computed(() => {
    return layout.value.widgets
        .filter(w => w.visible && ['stats', 'quick-actions'].includes(w.id))
        .sort((a, b) => a.order - b.order);
});

// Main column widgets inside the grid (activity only for now) - sorted by order
const mainWidgets = computed(() => {
    return layout.value.widgets
        .filter(w => w.visible && w.column === 'main' && ['activity'].includes(w.id))
        .sort((a, b) => a.order - b.order);
});

// Sidebar widgets (ai-credits, upcoming-posts, suggestions) - sorted by order
const sideWidgets = computed(() => {
    return layout.value.widgets
        .filter(w => w.visible && w.column === 'sidebar')
        .sort((a, b) => a.order - b.order);
});

// Bottom full-width widgets (recent-posts, performance-cta) - sorted by order
const bottomWidgets = computed(() => {
    return layout.value.widgets
        .filter(w => w.visible && w.column === 'main' && ['recent-posts', 'performance-cta'].includes(w.id))
        .sort((a, b) => a.order - b.order);
});

// Time-based greeting
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
});

// AI credits progress
const aiCreditsProgress = computed(() => {
    if (!props.stats.ai_credits_limit) return 0;
    return Math.min((props.stats.ai_credits_used / props.stats.ai_credits_limit) * 100, 100);
});

// Format date for display
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Keyboard shortcut hint visibility
const showKeyboardHint = ref(true);
onMounted(() => {
    setTimeout(() => {
        showKeyboardHint.value = false;
    }, 8000);
});

// Handle widget drop
const handleWidgetDrop = (targetId: string, position: 'before' | 'after', sourceId: string) => {
    reorderWidgets(sourceId, targetId, position);
};

// Current dragged widget for drop handling
const currentDraggedWidget = ref<string | null>(null);

const onDragStart = (widgetId: string) => {
    currentDraggedWidget.value = widgetId;
    startDrag(widgetId);
};

const onDragEnd = () => {
    currentDraggedWidget.value = null;
    endDrag();
};

const onDrop = (targetId: string, position: 'before' | 'after') => {
    if (currentDraggedWidget.value) {
        handleWidgetDrop(targetId, position, currentDraggedWidget.value);
    }
};
</script>

<template>
    <Head title="Dashboard - BizBoost" />
    <BizBoostLayout>
        <div class="space-y-6">
            <!-- Welcome Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        {{ greeting }}, {{ user?.name?.split(' ')[0] || 'there' }}! 👋
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-1">
                        Here's what's happening with <span class="font-medium text-slate-700 dark:text-slate-300">{{ business?.name || 'your business' }}</span> today.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Customize Dashboard Button -->
                    <button
                        v-if="!isCustomizing"
                        @click="showCustomizer = true"
                        class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors text-sm font-medium"
                    >
                        <Cog6ToothIcon class="h-4 w-4" aria-hidden="true" />
                        <span class="hidden sm:inline">Customize</span>
                    </button>

                    <!-- Edit Mode Toggle -->
                    <button
                        @click="toggleCustomizing"
                        :class="[
                            'flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium transition-colors',
                            isCustomizing
                                ? 'bg-violet-600 text-white hover:bg-violet-700'
                                : 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 hover:bg-violet-200 dark:hover:bg-violet-900/50',
                        ]"
                    >
                        <PencilSquareIcon v-if="!isCustomizing" class="h-4 w-4" aria-hidden="true" />
                        <CheckIcon v-else class="h-4 w-4" aria-hidden="true" />
                        <span class="hidden sm:inline">{{ isCustomizing ? 'Done Editing' : 'Edit Layout' }}</span>
                    </button>

                    <!-- Keyboard shortcut hint -->
                    <Transition
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="opacity-0 translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div
                            v-if="showKeyboardHint && !isCustomizing"
                            class="hidden lg:flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm"
                        >
                            <CommandLineIcon class="h-4 w-4" aria-hidden="true" />
                            <span>Press</span>
                            <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600 text-xs font-mono">⌘K</kbd>
                            <span>for quick actions</span>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Edit Mode Banner -->
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="isCustomizing"
                    class="flex items-center justify-between p-4 rounded-2xl bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-700"
                >
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-violet-100 dark:bg-violet-800/50">
                            <PencilSquareIcon class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-violet-900 dark:text-violet-100">Edit Mode Active</p>
                            <p class="text-sm text-violet-600 dark:text-violet-400">Drag widgets to rearrange, or hover to hide them</p>
                        </div>
                    </div>
                    <button
                        @click="toggleCustomizing"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition-colors"
                    >
                        <CheckIcon class="h-4 w-4" aria-hidden="true" />
                        Done
                    </button>
                </div>
            </Transition>

            <!-- Top Widgets (Stats, Quick Actions) - Rendered dynamically by order -->
            <template v-for="widget in topWidgets" :key="widget.id">
                <!-- Stats Widget -->
                <DashboardWidget
                    v-if="widget.id === 'stats'"
                    :widget="widget"
                    @dragstart="onDragStart"
                    @dragend="onDragEnd"
                    @drop="onDrop"
                >
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        <StatCard
                            name="Products"
                            :value="stats.products"
                            :previous-value="stats.products_previous"
                            :icon="ShoppingBagIcon"
                            href="/bizboost/products"
                            color="blue"
                            :sparkline-data="sparklineData?.products"
                        />
                        <StatCard
                            name="Customers"
                            :value="stats.customers"
                            :previous-value="stats.customers_previous"
                            :icon="UsersIcon"
                            href="/bizboost/customers"
                            color="green"
                            :sparkline-data="sparklineData?.customers"
                        />
                        <StatCard
                            name="Posts"
                            :value="stats.posts"
                            :previous-value="stats.posts_previous"
                            :icon="DocumentTextIcon"
                            href="/bizboost/posts"
                            color="violet"
                            :sparkline-data="sparklineData?.posts"
                        />
                        <StatCard
                            name="Sales"
                            :value="stats.sales_total"
                            :previous-value="stats.sales_previous"
                            :icon="CurrencyDollarIcon"
                            href="/bizboost/sales"
                            color="amber"
                            prefix="K"
                            :sparkline-data="sparklineData?.sales"
                        />
                    </div>
                </DashboardWidget>

                <!-- Quick Actions Widget -->
                <DashboardWidget
                    v-else-if="widget.id === 'quick-actions'"
                    :widget="widget"
                    @dragstart="onDragStart"
                    @dragend="onDragEnd"
                    @drop="onDrop"
                >
                    <QuickActionsWidget
                        :pending-tasks="pendingTasks"
                        :scheduled-posts="scheduledPostsToday"
                        :low-stock-products="lowStockProducts"
                    />
                </DashboardWidget>
            </template>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Column (2 columns) - Rendered dynamically by order -->
                <div class="lg:col-span-2 space-y-6">
                    <template v-for="widget in mainWidgets" :key="widget.id">
                        <!-- Activity Feed -->
                        <DashboardWidget
                            v-if="widget.id === 'activity'"
                            :widget="widget"
                            @dragstart="onDragStart"
                            @dragend="onDragEnd"
                            @drop="onDrop"
                        >
                            <ActivityFeed 
                                :activities="recentActivity || []" 
                                :max-items="6" 
                                :business-id="business?.id"
                            />
                        </DashboardWidget>
                    </template>
                </div>

                <!-- Right Sidebar - Rendered dynamically by order -->
                <div class="space-y-6">
                    <template v-for="widget in sideWidgets" :key="widget.id">
                        <!-- AI Credits Usage -->
                        <DashboardWidget
                            v-if="widget.id === 'ai-credits'"
                            :widget="widget"
                            @dragstart="onDragStart"
                            @dragend="onDragEnd"
                            @drop="onDrop"
                        >
                            <div class="rounded-2xl bg-gradient-to-br from-violet-600 via-violet-600 to-purple-700 p-5 text-white shadow-lg shadow-violet-500/20">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 rounded-xl bg-white/20">
                                        <SparklesIcon class="h-5 w-5" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold">AI Credits</h3>
                                        <p class="text-sm text-violet-200">{{ stats.ai_credits_used }} / {{ stats.ai_credits_limit || '∞' }} used</p>
                                    </div>
                                </div>
                                <div class="h-2 bg-white/20 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-white rounded-full transition-all duration-500"
                                        :style="{ width: `${aiCreditsProgress}%` }"
                                    ></div>
                                </div>
                                <Link
                                    href="/bizboost/ai"
                                    class="mt-4 flex items-center justify-center gap-2 w-full py-2 rounded-xl bg-white/10 hover:bg-white/20 transition-colors text-sm font-medium"
                                >
                                    Generate Content
                                    <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                                </Link>
                            </div>
                        </DashboardWidget>

                        <!-- Upcoming Posts -->
                        <DashboardWidget
                            v-else-if="widget.id === 'upcoming-posts'"
                            :widget="widget"
                            @dragstart="onDragStart"
                            @dragend="onDragEnd"
                            @drop="onDrop"
                        >
                            <div class="rounded-2xl bg-white dark:bg-slate-800 p-5 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <CalendarDaysIcon class="h-5 w-5 text-slate-400" aria-hidden="true" />
                                        <h3 class="font-semibold text-slate-900 dark:text-white">Upcoming Posts</h3>
                                    </div>
                                    <Link href="/bizboost/calendar" class="text-sm text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300">
                                        Calendar
                                    </Link>
                                </div>
                                <div v-if="upcomingPosts?.length" class="space-y-3">
                                    <div
                                        v-for="post in upcomingPosts.slice(0, 3)"
                                        :key="post.id"
                                        class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                    >
                                        <div class="shrink-0 w-1 h-full min-h-[40px] rounded-full bg-violet-400"></div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm text-slate-700 dark:text-slate-300 line-clamp-2">{{ post.caption }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ formatDate(post.scheduled_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-6">
                                    <CalendarDaysIcon class="h-8 w-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" aria-hidden="true" />
                                    <p class="text-sm text-slate-500 dark:text-slate-400">No scheduled posts</p>
                                    <Link
                                        href="/bizboost/posts/create"
                                        class="text-sm text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300 font-medium mt-1 inline-block"
                                    >
                                        Schedule a post →
                                    </Link>
                                </div>
                            </div>
                        </DashboardWidget>

                        <!-- AI Recommendations -->
                        <DashboardWidget
                            v-else-if="widget.id === 'suggestions'"
                            :widget="widget"
                            @dragstart="onDragStart"
                            @dragend="onDragEnd"
                            @drop="onDrop"
                        >
                            <div class="rounded-2xl bg-white dark:bg-slate-800 p-5 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700">
                                <div class="flex items-center gap-2 mb-4">
                                    <LightBulbIcon class="h-5 w-5 text-amber-500" aria-hidden="true" />
                                    <h3 class="font-semibold text-slate-900 dark:text-white">Smart Suggestions</h3>
                                </div>
                                <div v-if="recommendations?.length" class="space-y-3">
                                    <div
                                        v-for="(rec, index) in recommendations.slice(0, 3)"
                                        :key="rec.id || index"
                                        :class="[
                                            'p-3 rounded-xl border transition-colors',
                                            rec.priority === 'high' ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700' :
                                            rec.priority === 'medium' ? 'bg-violet-50 dark:bg-violet-900/20 border-violet-200 dark:border-violet-700' :
                                            'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-600'
                                        ]"
                                    >
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ rec.title }}</p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">{{ rec.description }}</p>
                                        <Link
                                            :href="rec.actionUrl"
                                            class="text-xs text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300 font-medium mt-2 inline-flex items-center gap-1"
                                        >
                                            {{ rec.action }}
                                            <ArrowRightIcon class="h-3 w-3" aria-hidden="true" />
                                        </Link>
                                    </div>
                                </div>
                                <div v-else class="text-center py-6">
                                    <LightBulbIcon class="h-8 w-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" aria-hidden="true" />
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Complete your profile for personalized tips</p>
                                </div>
                            </div>
                        </DashboardWidget>
                    </template>
                </div>
            </div>

            <!-- Bottom Full-Width Widgets (Recent Posts, Performance CTA) - Rendered dynamically by order -->
            <template v-for="widget in bottomWidgets" :key="widget.id">
                <!-- Recent Posts Section -->
                <DashboardWidget
                    v-if="widget.id === 'recent-posts'"
                    :widget="widget"
                    @dragstart="onDragStart"
                    @dragend="onDragEnd"
                    @drop="onDrop"
                >
                    <div class="rounded-2xl bg-white dark:bg-slate-800 p-6 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Recent Posts</h3>
                            <Link href="/bizboost/posts" class="text-sm text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300 font-medium flex items-center gap-1">
                                View all
                                <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                            </Link>
                        </div>
                        <div v-if="recentPosts?.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <Link
                                v-for="post in recentPosts.slice(0, 4)"
                                :key="post.id"
                                :href="`/bizboost/posts/${post.id}`"
                                class="group p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                            >
                                <p class="text-sm text-slate-700 dark:text-slate-300 line-clamp-2 group-hover:text-slate-900 dark:group-hover:text-white">{{ post.caption }}</p>
                                <div class="flex items-center justify-between mt-3">
                                    <span
                                        :class="[
                                            'text-xs px-2 py-1 rounded-full font-medium',
                                            post.status === 'published' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' :
                                            post.status === 'scheduled' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' :
                                            post.status === 'draft' ? 'bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-300' :
                                            'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
                                        ]"
                                    >
                                        {{ post.status }}
                                    </span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500">
                                        {{ post.published_at ? formatDate(post.published_at) : post.scheduled_at ? formatDate(post.scheduled_at) : 'Draft' }}
                                    </span>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="text-center py-8">
                            <DocumentTextIcon class="h-12 w-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" aria-hidden="true" />
                            <p class="text-slate-500 dark:text-slate-400">No posts yet</p>
                            <Link
                                href="/bizboost/posts/create"
                                class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition-colors"
                            >
                                <DocumentTextIcon class="h-4 w-4" aria-hidden="true" />
                                Create your first post
                            </Link>
                        </div>
                    </div>
                </DashboardWidget>

                <!-- Performance Overview CTA -->
                <DashboardWidget
                    v-else-if="widget.id === 'performance-cta'"
                    :widget="widget"
                    @dragstart="onDragStart"
                    @dragend="onDragEnd"
                    @drop="onDrop"
                >
                    <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-6 text-white">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="p-3 rounded-xl bg-white/10">
                                    <ChartBarIcon class="h-6 w-6" aria-hidden="true" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Ready to grow faster?</h3>
                                    <p class="text-slate-400 text-sm">View detailed analytics and insights to optimize your marketing.</p>
                                </div>
                            </div>
                            <Link
                                href="/bizboost/analytics"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white text-slate-900 text-sm font-semibold hover:bg-slate-100 transition-colors shrink-0"
                            >
                                View Analytics
                                <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </DashboardWidget>
            </template>
        </div>

        <!-- Dashboard Customizer Modal -->
        <DashboardCustomizer
            :open="showCustomizer"
            @close="showCustomizer = false"
        />
    </BizBoostLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
