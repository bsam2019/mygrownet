<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { PlusIcon, PencilIcon, TrashIcon, ExternalLinkIcon, EyeIcon } from 'lucide-vue-next';

interface Resource {
    id: number;
    title: string;
    description: string | null;
    type: string;
    category: string;
    resource_url: string;
    author: string | null;
    duration_minutes: number | null;
    difficulty: string;
    is_external: boolean;
    is_featured: boolean;
    is_active: boolean;
    view_count: number;
    type_label: string;
    category_label: string;
    difficulty_label: string;
    duration_formatted: string | null;
}

interface Props {
    resources: Record<string, Resource[]>;
    stats: {
        total_resources: number;
        active_resources: number;
        featured_resources: number;
        total_views: number;
    };
}

const props = defineProps<Props>();

const showModal = ref(false);
const editingResource = ref<Resource | null>(null);

const form = useForm({
    title: '',
    description: '',
    type: 'article' as 'pdf' | 'video' | 'article' | 'course' | 'tool' | 'template',
    category: 'business' as 'business' | 'marketing' | 'finance' | 'leadership' | 'personal_development' | 'network_building',
    resource_url: '',
    thumbnail_file: null as File | null,
    author: '',
    duration_minutes: null as number | null,
    difficulty: 'beginner' as 'beginner' | 'intermediate' | 'advanced',
    is_external: true,
    is_featured: false,
    is_active: true,
    sort_order: 0,
});

const types = [
    { value: 'pdf', label: 'PDF Document', icon: '📄' },
    { value: 'video', label: 'Video', icon: '🎥' },
    { value: 'article', label: 'Article', icon: '📰' },
    { value: 'course', label: 'Online Course', icon: '🎓' },
    { value: 'tool', label: 'Tool/Template', icon: '🛠️' },
    { value: 'template', label: 'Template', icon: '📋' },
];

const categories = [
    { value: 'business', label: 'Business Fundamentals', icon: '💼' },
    { value: 'marketing', label: 'Marketing & Sales', icon: '📈' },
    { value: 'finance', label: 'Financial Management', icon: '💰' },
    { value: 'leadership', label: 'Leadership', icon: '👔' },
    { value: 'personal_development', label: 'Personal Development', icon: '🌱' },
    { value: 'network_building', label: 'Network Building', icon: '🤝' },
];

const openCreateModal = () => {
    editingResource.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (resource: Resource) => {
    editingResource.value = resource;
    form.title = resource.title;
    form.description = resource.description || '';
    form.type = resource.type as any;
    form.category = resource.category as any;
    form.resource_url = resource.resource_url;
    form.author = resource.author || '';
    form.duration_minutes = resource.duration_minutes;
    form.difficulty = resource.difficulty as any;
    form.is_external = resource.is_external;
    form.is_featured = resource.is_featured;
    form.is_active = resource.is_active;
    form.thumbnail_file = null;
    showModal.value = true;
};

const submit = () => {
    if (editingResource.value) {
        form.put(route('admin.library.resources.update', editingResource.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.library.resources.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteResource = (resource: Resource) => {
    if (confirm(`Are you sure you want to delete "${resource.title}"?`)) {
        router.delete(route('admin.library.resources.destroy', resource.id));
    }
};

const getTypeIcon = (type: string) => {
    return types.find(t => t.value === type)?.icon || '📄';
};

const getCategoryIcon = (category: string) => {
    return categories.find(c => c.value === category)?.icon || '💼';
};
</script>

<template>
    <Head title="Manage Library Resources" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Library Resources</h1>
                        <p class="text-sm text-gray-600 mt-1">Manage curated resources for starter kit members</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Add Resource
                    </button>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600">Total Resources</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_resources }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600">Active</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.active_resources }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600">Featured</p>
                        <p class="text-2xl font-bold text-blue-600">{{ stats.featured_resources }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-600">Total Views</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_views }}</p>
                    </div>
                </div>

                <!-- Resources by Category -->
                <div class="space-y-6">
                    <div v-for="(categoryResources, category) in resources" :key="category" class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">{{ getCategoryIcon(category) }}</span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ categories.find(c => c.value === category)?.label || category }}
                                    </h3>
                                    <p class="text-sm text-gray-600">{{ categoryResources.length }} resources</p>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-200">
                            <div
                                v-for="resource in categoryResources"
                                :key="resource.id"
                                class="px-6 py-4 hover:bg-gray-50"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl">{{ getTypeIcon(resource.type) }}</span>
                                            <h4 class="text-base font-medium text-gray-900">{{ resource.title }}</h4>
                                            <span v-if="resource.is_featured" class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">Featured</span>
                                            <span v-if="!resource.is_active" class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded">Inactive</span>
                                            <span v-if="resource.is_external" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded flex items-center gap-1">
                                                <ExternalLinkIcon class="w-3 h-3" />
                                                External
                                            </span>
                                        </div>
                                        <p v-if="resource.description" class="text-sm text-gray-600 mt-1">{{ resource.description }}</p>
                                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                            <span>{{ resource.type_label }}</span>
                                            <span v-if="resource.author">• {{ resource.author }}</span>
                                            <span v-if="resource.duration_formatted">• {{ resource.duration_formatted }}</span>
                                            <span>• {{ resource.difficulty_label }}</span>
                                            <span class="flex items-center gap-1">
                                                <EyeIcon class="w-4 h-4" />
                                                {{ resource.view_count }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 ml-4">
                                        <a
                                            :href="resource.resource_url"
                                            target="_blank"
                                            class="p-2 text-gray-600 hover:bg-gray-100 rounded"
                                            title="View Resource"
                                        >
                                            <ExternalLinkIcon class="w-4 h-4" />
                                        </a>
                                        <button
                                            @click="openEditModal(resource)"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded"
                                            title="Edit"
                                        >
                                            <PencilIcon class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click="deleteResource(resource)"
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
                        {{ editingResource ? 'Edit Resource' : 'Add Resource' }}
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                            <select
                                v-model="form.type"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option v-for="type in types" :key="type.value" :value="type.value">
                                    {{ type.icon }} {{ type.label }}
                                </option>
                            </select>
                        </div>

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
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Resource URL *</label>
                        <input
                            v-model="form.resource_url"
                            type="url"
                            required
                            placeholder="https://..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                        <p class="text-xs text-gray-500 mt-1">External link or uploaded file path</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                            <input
                                v-model="form.author"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                            <input
                                v-model.number="form.duration_minutes"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty *</label>
                        <select
                            v-model="form.difficulty"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thumbnail (optional)</label>
                        <input
                            @change="(e) => form.thumbnail_file = (e.target as HTMLInputElement).files?.[0] || null"
                            type="file"
                            accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input
                                v-model="form.is_external"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <span class="ml-2 text-sm text-gray-700">External Resource</span>
                        </label>

                        <label class="flex items-center">
                            <input
                                v-model="form.is_featured"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <span class="ml-2 text-sm text-gray-700">Featured Resource</span>
                        </label>

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
                            {{ form.processing ? 'Saving...' : (editingResource ? 'Update' : 'Create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
