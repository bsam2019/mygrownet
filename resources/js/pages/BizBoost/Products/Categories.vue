<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    TagIcon,
    FolderIcon,
    ArrowPathIcon,
    CheckIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Category {
    id: number | null;
    name: string;
    slug: string;
    description: string | null;
    color: string | null;
    icon: string | null;
    is_active: boolean;
    product_count: number;
    is_legacy?: boolean;
}

interface Props {
    categories: Category[];
    legacyCategories: Category[];
}

const props = defineProps<Props>();

const showAddForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    description: '',
    color: '',
    icon: '',
});

const editForm = useForm({
    name: '',
    description: '',
    color: '',
    icon: '',
    is_active: true,
});

// Color options for categories
const colorOptions = [
    { value: 'violet', label: 'Violet', class: 'bg-violet-500' },
    { value: 'blue', label: 'Blue', class: 'bg-blue-500' },
    { value: 'green', label: 'Green', class: 'bg-green-500' },
    { value: 'yellow', label: 'Yellow', class: 'bg-yellow-500' },
    { value: 'orange', label: 'Orange', class: 'bg-orange-500' },
    { value: 'red', label: 'Red', class: 'bg-red-500' },
    { value: 'pink', label: 'Pink', class: 'bg-pink-500' },
    { value: 'gray', label: 'Gray', class: 'bg-gray-500' },
];

const allCategories = computed(() => [...props.categories, ...props.legacyCategories]);
const hasLegacyCategories = computed(() => props.legacyCategories.length > 0);

const submit = () => {
    if (!form.name.trim()) return;
    
    form.post('/bizboost/products/categories', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showAddForm.value = false;
        },
    });
};

const startEdit = (category: Category) => {
    if (!category.id) return; // Can't edit legacy categories directly
    editingId.value = category.id;
    editForm.name = category.name;
    editForm.description = category.description || '';
    editForm.color = category.color || '';
    editForm.icon = category.icon || '';
    editForm.is_active = category.is_active;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const saveEdit = (id: number) => {
    editForm.put(`/bizboost/products/categories/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
};

const deleteCategory = (category: Category) => {
    if (!category.id) return;
    
    const message = category.product_count > 0
        ? `This will remove the category from ${category.product_count} product(s). They will become uncategorized. Continue?`
        : 'Delete this category?';
    
    if (confirm(message)) {
        router.delete(`/bizboost/products/categories/${category.id}`, {
            preserveScroll: true,
        });
    }
};

const migrateLegacy = (name: string) => {
    router.post('/bizboost/products/categories/migrate-legacy', { name }, {
        preserveScroll: true,
    });
};

const getCategoryColorClass = (color: string | null): string => {
    if (!color) return 'bg-violet-500';
    const option = colorOptions.find(o => o.value === color);
    return option?.class || 'bg-violet-500';
};
</script>

<template>
    <Head title="Product Categories - BizBoost" />
    <BizBoostLayout title="Product Categories">
        <div class="max-w-3xl">
            <Link
                href="/bizboost/products"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mb-6"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Products
            </Link>

            <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Categories</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Organize your products into categories</p>
                    </div>
                    <button
                        @click="showAddForm = !showAddForm"
                        class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                    >
                        <PlusIcon class="h-4 w-4" aria-hidden="true" />
                        Add Category
                    </button>
                </div>

                <!-- Add Category Form -->
                <div v-if="showAddForm" class="mb-6 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                    <h3 class="font-medium text-gray-900 dark:text-white mb-4">New Category</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Electronics, Clothing"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                placeholder="Optional description"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Color</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="color in colorOptions"
                                    :key="color.value"
                                    type="button"
                                    @click="form.color = color.value"
                                    :class="[
                                        'w-8 h-8 rounded-full transition-all',
                                        color.class,
                                        form.color === color.value ? 'ring-2 ring-offset-2 ring-violet-500' : 'hover:scale-110'
                                    ]"
                                    :aria-label="`Select ${color.label} color`"
                                />
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50"
                            >
                                Create Category
                            </button>
                            <button
                                type="button"
                                @click="showAddForm = false; form.reset()"
                                class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Legacy Categories Warning -->
                <div v-if="hasLegacyCategories" class="mb-6 p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                    <div class="flex items-start gap-3">
                        <ArrowPathIcon class="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5" aria-hidden="true" />
                        <div>
                            <h4 class="font-medium text-amber-800 dark:text-amber-200">Legacy Categories Found</h4>
                            <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                Some categories were created before the new system. Click "Migrate" to convert them.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Categories List -->
                <div v-if="allCategories.length" class="space-y-2">
                    <div
                        v-for="cat in allCategories"
                        :key="cat.id ?? cat.name"
                        class="rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        <!-- View Mode -->
                        <div v-if="editingId !== cat.id" class="flex items-center justify-between p-4">
                            <div class="flex items-center gap-3">
                                <div
                                    :class="[
                                        'w-10 h-10 rounded-lg flex items-center justify-center',
                                        getCategoryColorClass(cat.color)
                                    ]"
                                >
                                    <FolderIcon class="h-5 w-5 text-white" aria-hidden="true" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ cat.name }}</p>
                                        <span
                                            v-if="cat.is_legacy"
                                            class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300"
                                        >
                                            Legacy
                                        </span>
                                        <span
                                            v-if="!cat.is_active && !cat.is_legacy"
                                            class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                        >
                                            Inactive
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ cat.product_count }} product{{ cat.product_count !== 1 ? 's' : '' }}
                                        <span v-if="cat.description" class="ml-2">• {{ cat.description }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    v-if="cat.is_legacy"
                                    @click="migrateLegacy(cat.name)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium text-amber-700 bg-amber-100 hover:bg-amber-200 dark:text-amber-300 dark:bg-amber-900/50 dark:hover:bg-amber-900"
                                >
                                    <ArrowPathIcon class="h-4 w-4" aria-hidden="true" />
                                    Migrate
                                </button>
                                <button
                                    v-if="cat.id"
                                    @click="startEdit(cat)"
                                    class="p-2 rounded-lg text-gray-400 hover:text-violet-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors"
                                    :aria-label="`Edit ${cat.name} category`"
                                >
                                    <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                                <button
                                    v-if="cat.id"
                                    @click="deleteCategory(cat)"
                                    class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    :aria-label="`Delete ${cat.name} category`"
                                >
                                    <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>

                        <!-- Edit Mode -->
                        <div v-else class="p-4">
                            <form @submit.prevent="saveEdit(cat.id!)" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                        <input
                                            v-model="editForm.name"
                                            type="text"
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                        <input
                                            v-model="editForm.description"
                                            type="text"
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                                        />
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Color</label>
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-for="color in colorOptions"
                                            :key="color.value"
                                            type="button"
                                            @click="editForm.color = color.value"
                                            :class="[
                                                'w-8 h-8 rounded-full transition-all',
                                                color.class,
                                                editForm.color === color.value ? 'ring-2 ring-offset-2 ring-violet-500' : 'hover:scale-110'
                                            ]"
                                            :aria-label="`Select ${color.label} color`"
                                        />
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input
                                        id="is_active"
                                        v-model="editForm.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                                    />
                                    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Active</label>
                                </div>

                                <div class="flex gap-2">
                                    <button
                                        type="submit"
                                        :disabled="editForm.processing"
                                        class="inline-flex items-center gap-1 rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50"
                                    >
                                        <CheckIcon class="h-4 w-4" aria-hidden="true" />
                                        Save
                                    </button>
                                    <button
                                        type="button"
                                        @click="cancelEdit"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <TagIcon class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" aria-hidden="true" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No categories yet</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Create categories to organize your products and make them easier to find.
                    </p>
                    <button
                        @click="showAddForm = true"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                    >
                        <PlusIcon class="h-4 w-4" aria-hidden="true" />
                        Create Your First Category
                    </button>
                </div>

                <!-- Tips -->
                <div class="mt-6 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <h4 class="font-medium text-blue-900 dark:text-blue-200 mb-2">💡 Tips</h4>
                    <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                        <li>• Categories help customers find products easily</li>
                        <li>• Use colors to visually distinguish categories</li>
                        <li>• Inactive categories won't show in product filters</li>
                        <li>• Deleting a category doesn't delete the products</li>
                    </ul>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
