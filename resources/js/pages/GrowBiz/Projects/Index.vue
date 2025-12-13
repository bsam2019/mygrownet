<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    PlusIcon,
    FolderIcon,
    ViewColumnsIcon,
    ChartBarIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    CalendarDaysIcon,
    ClockIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Project {
    id: number;
    name: string;
    description: string | null;
    color: string;
    status: string;
    status_label: string;
    status_color: string;
    start_date: string | null;
    end_date: string | null;
    progress_percentage: number;
    task_count: number;
    completed_task_count: number;
    days_remaining: number | null;
    is_overdue: boolean;
}

const props = defineProps<{
    projects: Project[];
    filters: Record<string, string>;
}>();

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const showFilters = ref(false);
const showCreateModal = ref(false);
const isSubmitting = ref(false);

const newProject = ref({
    name: '',
    description: '',
    color: '#3b82f6',
    start_date: '',
    end_date: '',
});

const colors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
];

const filteredProjects = computed(() => {
    let result = props.projects;
    if (search.value) {
        const s = search.value.toLowerCase();
        result = result.filter(p => p.name.toLowerCase().includes(s));
    }
    return result;
});

const applyFilters = () => {
    router.get(route('growbiz.projects.index'), {
        status: selectedStatus.value || undefined,
        search: search.value || undefined,
    }, { preserveState: true });
};

const clearFilters = () => {
    selectedStatus.value = '';
    search.value = '';
    router.get(route('growbiz.projects.index'));
};

const createProject = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.projects.store'), newProject.value, {
        onSuccess: () => {
            showCreateModal.value = false;
            resetForm();
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const resetForm = () => {
    newProject.value = {
        name: '',
        description: '',
        color: '#3b82f6',
        start_date: '',
        end_date: '',
    };
};
</script>

<template>
    <GrowBizLayout>
        <div class="p-4 space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Projects</h1>
                    <p class="text-sm text-gray-500">{{ projects.length }} projects</p>
                </div>
                <button
                    @click="showCreateModal = true"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    <span class="hidden sm:inline">New Project</span>
                </button>
            </div>

            <!-- Search & Filters -->
            <div class="flex items-center gap-2">
                <div class="flex-1 relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search projects..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <button
                    @click="showFilters = !showFilters"
                    class="p-2.5 bg-white border border-gray-200 rounded-xl hover:bg-gray-50"
                    :class="{ 'bg-blue-50 border-blue-200': showFilters }"
                    aria-label="Toggle filters"
                >
                    <FunnelIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
            </div>

            <!-- Filter Panel -->
            <div v-if="showFilters" class="bg-white rounded-xl p-4 border border-gray-200 space-y-3">
                <select v-model="selectedStatus" class="w-full text-sm border-gray-200 rounded-lg">
                    <option value="">All Status</option>
                    <option value="planning">Planning</option>
                    <option value="active">Active</option>
                    <option value="on_hold">On Hold</option>
                    <option value="completed">Completed</option>
                    <option value="archived">Archived</option>
                </select>
                <div class="flex justify-end gap-2">
                    <button @click="clearFilters" class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900">Clear</button>
                    <button @click="applyFilters" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Apply</button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredProjects.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
                <FolderIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                <p class="text-gray-500">No projects yet</p>
                <button @click="showCreateModal = true" class="mt-3 text-blue-600 font-medium">Create your first project</button>
            </div>

            <!-- Projects Grid -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div
                    v-for="project in filteredProjects"
                    :key="project.id"
                    class="bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition-shadow"
                >
                    <!-- Color Bar -->
                    <div class="h-2" :style="{ backgroundColor: project.color }"></div>
                    
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ project.name }}</h3>
                                <span :class="project.status_color" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                    {{ project.status_label }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                <Link
                                    :href="route('growbiz.projects.kanban', project.id)"
                                    class="p-2 hover:bg-gray-100 rounded-lg"
                                    title="Kanban Board"
                                    aria-label="Open Kanban board"
                                >
                                    <ViewColumnsIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                                </Link>
                                <Link
                                    :href="route('growbiz.projects.gantt', project.id)"
                                    class="p-2 hover:bg-gray-100 rounded-lg"
                                    title="Gantt Chart"
                                    aria-label="Open Gantt chart"
                                >
                                    <ChartBarIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                                </Link>
                            </div>
                        </div>

                        <p v-if="project.description" class="text-sm text-gray-500 mb-3 line-clamp-2">
                            {{ project.description }}
                        </p>

                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span>Progress</span>
                                <span>{{ project.progress_percentage }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all"
                                    :style="{ width: `${project.progress_percentage}%`, backgroundColor: project.color }"
                                ></div>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">
                                {{ project.completed_task_count }}/{{ project.task_count }} tasks
                            </span>
                            <div class="flex items-center gap-3">
                                <span v-if="project.end_date" class="flex items-center gap-1 text-gray-500" :class="{ 'text-red-500': project.is_overdue }">
                                    <CalendarDaysIcon class="h-4 w-4" aria-hidden="true" />
                                    {{ project.days_remaining !== null ? `${project.days_remaining}d left` : 'No deadline' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <Link
                            :href="route('growbiz.projects.show', project.id)"
                            class="text-sm text-blue-600 font-medium hover:text-blue-700"
                        >
                            View Details
                        </Link>
                        <Link
                            :href="route('growbiz.projects.kanban', project.id)"
                            class="text-sm text-gray-600 font-medium hover:text-gray-900"
                        >
                            Open Board →
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Project Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
            >
                <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/50" @click="showCreateModal = false"></div>
            </Transition>
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-to-class="translate-y-full"
            >
                <div v-if="showCreateModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl max-h-[90vh] overflow-y-auto safe-area-bottom">
                    <div class="sticky top-0 bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">New Project</h2>
                        <button @click="showCreateModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                    <form @submit.prevent="createProject" class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Project Name *</label>
                            <input v-model="newProject.name" type="text" required class="w-full border-gray-200 rounded-lg" placeholder="My Awesome Project" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="newProject.description" rows="3" class="w-full border-gray-200 rounded-lg" placeholder="What's this project about?"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="color in colors"
                                    :key="color"
                                    type="button"
                                    @click="newProject.color = color"
                                    class="w-8 h-8 rounded-full border-2 transition-all"
                                    :class="newProject.color === color ? 'border-gray-900 scale-110' : 'border-transparent'"
                                    :style="{ backgroundColor: color }"
                                    :aria-label="`Select color ${color}`"
                                ></button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input v-model="newProject.start_date" type="date" class="w-full border-gray-200 rounded-lg" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input v-model="newProject.end_date" type="date" class="w-full border-gray-200 rounded-lg" />
                            </div>
                        </div>
                        <button
                            type="submit"
                            :disabled="isSubmitting || !newProject.name"
                            class="w-full py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Creating...' : 'Create Project' }}
                        </button>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </GrowBizLayout>
</template>
