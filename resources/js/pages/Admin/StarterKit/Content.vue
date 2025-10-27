<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { PlusIcon, PencilIcon, TrashIcon, EyeIcon, EyeOffIcon } from 'lucide-vue-next';

interface ContentItem {
    id: number;
    title: string;
    description: string | null;
    category: 'training' | 'ebook' | 'video' | 'tool' | 'library';
    unlock_day: number;
    file_path: string | null;
    file_type: string | null;
    file_size: number | null;
    thumbnail: string | null;
    estimated_value: number;
    sort_order: number;
    is_active: boolean;
    category_label: string;
    unlock_description: string;
    file_size_formatted: string | null;
}

interface Props {
    items: Record<string, ContentItem[]>;
    stats: {
        total_items: number;
        active_items: number;
        total_value: number;
        by_category: Record<string, { count: number; value: number }>;
    };
}

const props = defineProps<Props>();

const showModal = ref(false);
const editingItem = ref<ContentItem | null>(null);

const form = useForm({
    title: '',
    description: '',
    category: 'training' as 'training' | 'ebook' | 'video' | 'tool' | 'library',
    unlock_day: 0,
    file: null as File | null,
    thumbnail_file: null as File | null,
    estimated_value: 0,
    sort_order: 0,
    is_active: true,
});

const categories = [
    { value: 'training', label: 'Training Module', icon: '📚' },
    { value: 'ebook', label: 'eBook', icon: '📖' },
    { value: 'video', label: 'Video Tutorial', icon: '🎥' },
    { value: 'tool', label: 'Marketing Tool', icon: '🛠️' },
    { value: 'library', label: 'Library Access', icon: '📚' },
];

const openCreateModal = () => {
    editingItem.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (item: ContentItem) => {
    editingItem.value = item;
    form.title = item.title;
    form.description = item.description || '';
    form.category = item.category;
    form.unlock_day = item.unlock_day;
    form.file = null;
    form.thumbnail_file = null;
    form.estimated_value = item.estimated_value;
    form.sort_order = item.sort_order;
    form.is_active = item.is_active;
    showModal.value = true;
};

const submit = () => {
    if (editingItem.value) {
        form.put(route('admin.starter-kit.content.update', editingItem.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.starter-kit.content.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteItem = (item: ContentItem) => {
    if (confirm(`Are you sure you want to delete "${item.title}"?`)) {
        router.delete(route('admin.starter-kit.content.destroy', item.id));
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getCategoryIcon = (category: string) => {
    return categories.find(c => c.value === category)?.icon || '📄';
};
</script>

<template>
    <Head title="Manage Starter Kit Content" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Starter Kit Content</h1>
                        <p class="text-sm text-gray-600 mt-1">Manage content items included in the starter kit</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Add Content Item
                    </button>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600">Total Items</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_items }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600">Active Items</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.active_items }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600">Total Value</p>
                        <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(stats.total_value) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600">Categories</p>
                        <p class="text-2xl font-bold text-gray-900">{{ Object.keys(items).length }}</p>
                    </div>
                </div>

                <!-- Content by Category -->
                <div class="space-y-6">
                    <div v-for="(categoryItems, category) in items" :key="category" class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">{{ getCategoryIcon(category) }}</span>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ categories.find(c => c.value === category)?.label || category }}
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            {{ categoryItems.length }} items • 
                                            {{ formatCurrency(categoryItems.reduce((sum, item) => sum + item.estimated_value, 0)) }} value
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-200">
                            <div
                                v-for="item in categoryItems"
                                :key="item.id"
                                class="px-6 py-4 hover:bg-gray-50"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <h4 class="text-base font-medium text-gray-900">{{ item.title }}</h4>
                                            <span
                                                v-if="!item.is_active"
                                                class="ml-2 px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded"
                                            >
                                                Inactive
                                            </span>
                                        </div>
                                        <p v-if="item.description" class="text-sm text-gray-600 mt-1">{{ item.description }}</p>
                                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                            <span>{{ item.unlock_description }}</span>
                                            <span>•</span>
                                            <span>{{ formatCurrency(item.estimated_value) }}</span>
                                            <span v-if="item.file_size_formatted">•</span>
                                            <span v-if="item.file_size_formatted">{{ item.file_size_formatted }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 ml-4">
                                        <button
                                            @click="openEditModal(item)"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded"
                                            title="Edit"
                                        >
                                            <PencilIcon class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click="deleteItem(item)"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded"
                                            title="Delete"
                                        >
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ editingItem ? 'Edit Content Item' : 'Add Content Item' }}
                    </h2>
                </div>

                <form @submit.prevent="submit" class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                            <select
                                v-model="form.category"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                    {{ cat.icon }} {{ cat.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unlock Day *</label>
                            <input
                                v-model.number="form.unlock_day"
                                type="number"
                                min="0"
                                max="30"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                            <p class="text-xs text-gray-500 mt-1">0 = Immediate, 1-30 = Days after purchase</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Value (K) *</label>
                            <input
                                v-model.number="form.estimated_value"
                                type="number"
                                min="0"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input
                                v-model.number="form.sort_order"
                                type="number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Content File {{ editingItem ? '(leave empty to keep current)' : '' }}
                        </label>
                        <input
                            @change="(e) => form.file = (e.target as HTMLInputElement).files?.[0] || null"
                            type="file"
                            accept=".pdf,.doc,.docx,.mp4,.mov,.avi,.zip,.ppt,.pptx"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Max 100MB. Supported: PDF, DOC, MP4, ZIP, PPT
                        </p>
                        <p v-if="editingItem?.file_path" class="text-xs text-green-600 mt-1">
                            Current: {{ editingItem.file_path.split('/').pop() }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Thumbnail Image {{ editingItem ? '(leave empty to keep current)' : '' }}
                        </label>
                        <input
                            @change="(e) => form.thumbnail_file = (e.target as HTMLInputElement).files?.[0] || null"
                            type="file"
                            accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                        <p class="text-xs text-gray-500 mt-1">Max 2MB. Optional preview image</p>
                        <p v-if="editingItem?.thumbnail" class="text-xs text-green-600 mt-1">
                            Current thumbnail exists
                        </p>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <span class="ml-2 text-sm text-gray-700">Active (visible to members)</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : (editingItem ? 'Update' : 'Create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
