<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    TagIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Category {
    id: number;
    name: string;
    icon: string;
    color: string;
    is_default: boolean;
    expense_count: number;
    total_spent: number;
    formatted_total: string;
}

const props = defineProps<{
    categories: Category[];
}>();

const showAddModal = ref(false);

const form = useForm({
    name: '',
    icon: '📦',
    color: '#6b7280',
});

const iconOptions = ['🍔', '🚌', '📱', '🏠', '📚', '💊', '🎮', '👕', '🎁', '💼', '🛒', '⚡', '💧', '📦'];
const colorOptions = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#14b8a6', '#3b82f6', '#8b5cf6', '#ec4899', '#6b7280'];

const submitCategory = () => {
    form.post(route('lifeplus.money.categories.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
            form.icon = '📦';
            form.color = '#6b7280';
        },
    });
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.money.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Back to money overview"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900 flex-1">Categories</h1>
            <button 
                @click="showAddModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 transition-colors"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Add
            </button>
        </div>

        <!-- Categories List -->
        <div class="space-y-3">
            <div v-if="categories.length === 0" class="text-center py-12">
                <TagIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">No categories yet</p>
            </div>

            <div 
                v-for="category in categories" 
                :key="category.id"
                class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"
            >
                <div class="flex items-center gap-3">
                    <div 
                        class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
                        :style="{ backgroundColor: category.color + '20' }"
                    >
                        {{ category.icon }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-900">{{ category.name }}</h3>
                            <span 
                                v-if="category.is_default"
                                class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full"
                            >
                                Default
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ category.expense_count }} expenses • {{ category.formatted_total }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Category Modal -->
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
                            <h2 class="text-lg font-semibold text-gray-900">Add Category</h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitCategory" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input 
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="e.g., Entertainment"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        v-for="icon in iconOptions" 
                                        :key="icon"
                                        type="button"
                                        @click="form.icon = icon"
                                        :class="[
                                            'w-10 h-10 rounded-lg text-xl flex items-center justify-center transition-colors',
                                            form.icon === icon 
                                                ? 'bg-emerald-100 ring-2 ring-emerald-500' 
                                                : 'bg-gray-100 hover:bg-gray-200'
                                        ]"
                                    >
                                        {{ icon }}
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        v-for="color in colorOptions" 
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
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Category' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
