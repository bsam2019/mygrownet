<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    PlusIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    CalendarIcon,
    TrashIcon,
    PencilIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolid } from '@heroicons/vue/24/solid';

interface Todo {
    id: number;
    title: string;
    description: string | null;
    due_date: string | null;
    due_time: string | null;
    priority: 'low' | 'medium' | 'high';
    status: 'pending' | 'in_progress' | 'completed';
    category: string | null;
    tags: string[];
    is_overdue: boolean;
    is_due_today: boolean;
    is_due_tomorrow: boolean;
    completed_at: string | null;
}

interface Props {
    todos: Todo[];
    stats: {
        total: number;
        pending: number;
        in_progress: number;
        completed: number;
        overdue: number;
        due_today: number;
        completion_rate: number;
    };
    categories: string[];
    filters: {
        status: string | null;
        priority: string | null;
        category: string | null;
        search: string | null;
        due_date: string | null;
    };
    statuses: { value: string; label: string }[];
    priorities: { value: string; label: string }[];
}

const props = defineProps<Props>();

defineOptions({ layout: GrowBizLayout });

const showAddModal = ref(false);
const showFilters = ref(false);
const searchQuery = ref(props.filters.search || '');
const editingTodo = ref<Todo | null>(null);

// New todo form
const newTodo = ref({
    title: '',
    description: '',
    due_date: '',
    due_time: '',
    priority: 'medium',
    category: '',
});

const resetForm = () => {
    newTodo.value = {
        title: '',
        description: '',
        due_date: '',
        due_time: '',
        priority: 'medium',
        category: '',
    };
    editingTodo.value = null;
};

const createTodo = () => {
    if (!newTodo.value.title.trim()) return;
    
    router.post(route('growbiz.todos.store'), newTodo.value, {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            resetForm();
        },
    });
};

const toggleTodo = (todo: Todo) => {
    router.post(route('growbiz.todos.toggle', todo.id), {}, {
        preserveScroll: true,
    });
};

const deleteTodo = (todo: Todo) => {
    if (!confirm('Delete this todo?')) return;
    router.delete(route('growbiz.todos.destroy', todo.id), {
        preserveScroll: true,
    });
};

const editTodo = (todo: Todo) => {
    editingTodo.value = todo;
    newTodo.value = {
        title: todo.title,
        description: todo.description || '',
        due_date: todo.due_date || '',
        due_time: todo.due_time || '',
        priority: todo.priority,
        category: todo.category || '',
    };
    showAddModal.value = true;
};

const updateTodo = () => {
    if (!editingTodo.value || !newTodo.value.title.trim()) return;
    
    router.put(route('growbiz.todos.update', editingTodo.value.id), newTodo.value, {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            resetForm();
        },
    });
};

const applyFilter = (key: string, value: string | null) => {
    router.get(route('growbiz.todos.index'), {
        ...props.filters,
        [key]: value,
    }, { preserveState: true, preserveScroll: true });
};

const search = () => {
    applyFilter('search', searchQuery.value || null);
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'high': return 'text-red-600 bg-red-50';
        case 'medium': return 'text-amber-600 bg-amber-50';
        case 'low': return 'text-gray-600 bg-gray-100';
        default: return 'text-gray-600 bg-gray-100';
    }
};

const formatDate = (date: string | null) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

<template>
    <div class="p-4 space-y-4">
        <!-- Header with Stats -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">My To-Do List</h1>
                <p class="text-sm text-gray-500">{{ stats.pending + stats.in_progress }} active tasks</p>
            </div>
            <button
                @click="showAddModal = true; resetForm()"
                class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-medium shadow-lg shadow-emerald-200 active:scale-95 transition-transform"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                <span>Add</span>
            </button>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-4 gap-2">
            <button
                @click="applyFilter('due_date', 'today')"
                :class="[
                    'p-3 rounded-xl text-center transition-colors',
                    props.filters.due_date === 'today' ? 'bg-emerald-100 ring-2 ring-emerald-500' : 'bg-white'
                ]"
            >
                <CalendarIcon class="h-5 w-5 mx-auto text-emerald-600" aria-hidden="true" />
                <p class="text-lg font-bold text-gray-900">{{ stats.due_today }}</p>
                <p class="text-xs text-gray-500">Today</p>
            </button>
            <button
                @click="applyFilter('due_date', 'overdue')"
                :class="[
                    'p-3 rounded-xl text-center transition-colors',
                    props.filters.due_date === 'overdue' ? 'bg-red-100 ring-2 ring-red-500' : 'bg-white'
                ]"
            >
                <ExclamationTriangleIcon class="h-5 w-5 mx-auto text-red-600" aria-hidden="true" />
                <p class="text-lg font-bold text-gray-900">{{ stats.overdue }}</p>
                <p class="text-xs text-gray-500">Overdue</p>
            </button>
            <button
                @click="applyFilter('status', 'pending')"
                :class="[
                    'p-3 rounded-xl text-center transition-colors',
                    props.filters.status === 'pending' ? 'bg-amber-100 ring-2 ring-amber-500' : 'bg-white'
                ]"
            >
                <ClockIcon class="h-5 w-5 mx-auto text-amber-600" aria-hidden="true" />
                <p class="text-lg font-bold text-gray-900">{{ stats.pending }}</p>
                <p class="text-xs text-gray-500">Pending</p>
            </button>
            <button
                @click="applyFilter('status', 'completed')"
                :class="[
                    'p-3 rounded-xl text-center transition-colors',
                    props.filters.status === 'completed' ? 'bg-emerald-100 ring-2 ring-emerald-500' : 'bg-white'
                ]"
            >
                <CheckCircleIcon class="h-5 w-5 mx-auto text-emerald-600" aria-hidden="true" />
                <p class="text-lg font-bold text-gray-900">{{ stats.completed }}</p>
                <p class="text-xs text-gray-500">Done</p>
            </button>
        </div>

        <!-- Search & Filter Bar -->
        <div class="flex gap-2">
            <div class="flex-1 relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                <input
                    v-model="searchQuery"
                    @keyup.enter="search"
                    type="text"
                    placeholder="Search todos..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border-0 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                />
            </div>
            <button
                @click="showFilters = !showFilters"
                :class="[
                    'p-2.5 rounded-xl transition-colors',
                    showFilters ? 'bg-emerald-100 text-emerald-600' : 'bg-white text-gray-600'
                ]"
            >
                <FunnelIcon class="h-5 w-5" aria-hidden="true" />
            </button>
            <button
                v-if="Object.values(props.filters).some(v => v)"
                @click="router.get(route('growbiz.todos.index'))"
                class="px-3 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm font-medium"
            >
                Clear
            </button>
        </div>

        <!-- Filter Options -->
        <div v-if="showFilters" class="bg-white rounded-xl p-4 space-y-3">
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Priority</label>
                <div class="flex gap-2 mt-1">
                    <button
                        v-for="p in priorities"
                        :key="p.value"
                        @click="applyFilter('priority', props.filters.priority === p.value ? null : p.value)"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                            props.filters.priority === p.value ? getPriorityColor(p.value) + ' ring-2 ring-current' : 'bg-gray-100 text-gray-600'
                        ]"
                    >
                        {{ p.label }}
                    </button>
                </div>
            </div>
            <div v-if="categories.length > 0">
                <label class="text-xs font-medium text-gray-500 uppercase">Category</label>
                <div class="flex flex-wrap gap-2 mt-1">
                    <button
                        v-for="cat in categories"
                        :key="cat"
                        @click="applyFilter('category', props.filters.category === cat ? null : cat)"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                            props.filters.category === cat ? 'bg-emerald-100 text-emerald-700 ring-2 ring-emerald-500' : 'bg-gray-100 text-gray-600'
                        ]"
                    >
                        {{ cat }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Todo List -->
        <div class="space-y-2">
            <div v-if="todos.length === 0" class="text-center py-12 bg-white rounded-xl">
                <CheckCircleIcon class="h-12 w-12 mx-auto text-gray-300" aria-hidden="true" />
                <p class="mt-2 text-gray-500">No todos found</p>
                <button
                    @click="showAddModal = true; resetForm()"
                    class="mt-4 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium"
                >
                    Create your first todo
                </button>
            </div>

            <div
                v-for="todo in todos"
                :key="todo.id"
                :class="[
                    'bg-white rounded-xl p-4 flex items-start gap-3 transition-all',
                    todo.status === 'completed' ? 'opacity-60' : '',
                    todo.is_overdue ? 'ring-2 ring-red-200' : ''
                ]"
            >
                <!-- Checkbox -->
                <button
                    @click="toggleTodo(todo)"
                    :class="[
                        'flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors',
                        todo.status === 'completed'
                            ? 'bg-emerald-500 border-emerald-500'
                            : 'border-gray-300 hover:border-emerald-500'
                    ]"
                >
                    <CheckCircleSolid
                        v-if="todo.status === 'completed'"
                        class="h-4 w-4 text-white"
                        aria-hidden="true"
                    />
                </button>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p :class="[
                        'font-medium',
                        todo.status === 'completed' ? 'line-through text-gray-400' : 'text-gray-900'
                    ]">
                        {{ todo.title }}
                    </p>
                    <p v-if="todo.description" class="text-sm text-gray-500 mt-0.5 line-clamp-2">
                        {{ todo.description }}
                    </p>
                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                        <span
                            v-if="todo.due_date"
                            :class="[
                                'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                                todo.is_overdue ? 'bg-red-100 text-red-700' :
                                todo.is_due_today ? 'bg-amber-100 text-amber-700' :
                                'bg-gray-100 text-gray-600'
                            ]"
                        >
                            <CalendarIcon class="h-3 w-3" aria-hidden="true" />
                            {{ todo.is_due_today ? 'Today' : todo.is_due_tomorrow ? 'Tomorrow' : formatDate(todo.due_date) }}
                        </span>
                        <span
                            :class="[
                                'px-2 py-0.5 rounded-full text-xs font-medium',
                                getPriorityColor(todo.priority)
                            ]"
                        >
                            {{ todo.priority }}
                        </span>
                        <span
                            v-if="todo.category"
                            class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700"
                        >
                            {{ todo.category }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-1">
                    <button
                        @click="editTodo(todo)"
                        class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
                    >
                        <PencilIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <button
                        @click="deleteTodo(todo)"
                        class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50"
                    >
                        <TrashIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showAddModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm" @click="showAddModal = false" />
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div
                v-if="showAddModal"
                class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 bg-white rounded-t-3xl shadow-2xl max-h-[85vh] overflow-y-auto"
            >
                <div class="flex justify-center pt-3 pb-2">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>

                <div class="px-6 pb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">
                        {{ editingTodo ? 'Edit Todo' : 'New Todo' }}
                    </h2>

                    <form @submit.prevent="editingTodo ? updateTodo() : createTodo()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                            <input
                                v-model="newTodo.title"
                                type="text"
                                required
                                placeholder="What needs to be done?"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea
                                v-model="newTodo.description"
                                rows="2"
                                placeholder="Add details..."
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                <input
                                    v-model="newTodo.due_date"
                                    type="date"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                                <input
                                    v-model="newTodo.due_time"
                                    type="time"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <div class="flex gap-2">
                                <button
                                    v-for="p in priorities"
                                    :key="p.value"
                                    type="button"
                                    @click="newTodo.priority = p.value"
                                    :class="[
                                        'flex-1 py-2.5 rounded-xl text-sm font-medium transition-colors',
                                        newTodo.priority === p.value
                                            ? getPriorityColor(p.value) + ' ring-2 ring-current'
                                            : 'bg-gray-100 text-gray-600'
                                    ]"
                                >
                                    {{ p.label }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <input
                                v-model="newTodo.category"
                                type="text"
                                placeholder="e.g., Work, Personal, Shopping"
                                list="categories"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            />
                            <datalist id="categories">
                                <option v-for="cat in categories" :key="cat" :value="cat" />
                            </datalist>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                @click="showAddModal = false; resetForm()"
                                class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="flex-1 py-3 bg-emerald-600 text-white rounded-xl font-medium shadow-lg shadow-emerald-200"
                            >
                                {{ editingTodo ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
