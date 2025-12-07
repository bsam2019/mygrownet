<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon, PhotoIcon } from '@heroicons/vue/24/outline';

interface Props {
    categories: string[];
    industries: string[];
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    description: '',
    category: '',
    industry: '',
    template_data: {
        caption: '',
        hashtags: [] as string[],
        cta: '',
    },
    is_premium: false,
    is_active: true,
    sort_order: 0,
    preview: null as File | null,
});

const previewUrl = ref<string | null>(null);
const hashtagInput = ref('');

const handlePreviewChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        form.preview = target.files[0];
        previewUrl.value = URL.createObjectURL(target.files[0]);
    }
};

const addHashtag = () => {
    const tag = hashtagInput.value.trim().replace(/^#/, '');
    if (tag && !form.template_data.hashtags.includes(tag)) {
        form.template_data.hashtags.push(tag);
    }
    hashtagInput.value = '';
};

const removeHashtag = (index: number) => {
    form.template_data.hashtags.splice(index, 1);
};

const submit = () => {
    form.post('/admin/bizboost/templates', {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Create Template - Admin" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <Link
                    href="/admin/bizboost/templates"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-6"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Templates
                </Link>

                <div class="bg-white rounded-xl shadow p-6">
                    <h1 class="text-xl font-bold text-gray-900 mb-6">Create Template</h1>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Preview Image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preview Image</label>
                            <div class="flex items-center gap-4">
                                <div class="h-32 w-32 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                                    <img v-if="previewUrl" :src="previewUrl" class="h-full w-full object-cover" alt="Preview" />
                                    <PhotoIcon v-else class="h-10 w-10 text-gray-300" aria-hidden="true" />
                                </div>
                                <label class="cursor-pointer rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Choose Image
                                    <input type="file" accept="image/*" class="hidden" @change="handlePreviewChange" />
                                </label>
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="2"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            />
                        </div>

                        <!-- Category & Industry -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                                <input
                                    id="category"
                                    v-model="form.category"
                                    type="text"
                                    list="categories"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                                />
                                <datalist id="categories">
                                    <option v-for="cat in categories" :key="cat" :value="cat" />
                                </datalist>
                            </div>
                            <div>
                                <label for="industry" class="block text-sm font-medium text-gray-700">Industry</label>
                                <select
                                    id="industry"
                                    v-model="form.industry"
                                    class="mt-1 block w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                                >
                                    <option value="">All Industries</option>
                                    <option v-for="ind in industries" :key="ind" :value="ind">{{ ind }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Template Content -->
                        <div class="border-t pt-6">
                            <h3 class="text-sm font-medium text-gray-900 mb-4">Template Content</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="caption" class="block text-sm font-medium text-gray-700">Caption Template</label>
                                    <textarea
                                        id="caption"
                                        v-model="form.template_data.caption"
                                        rows="4"
                                        placeholder="Use {product_name}, {business_name}, {price} as placeholders..."
                                        class="mt-1 block w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Available placeholders: {product_name}, {business_name}, {price}, {description}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hashtags</label>
                                    <div class="mt-1 flex gap-2">
                                        <input
                                            v-model="hashtagInput"
                                            type="text"
                                            placeholder="Add hashtag"
                                            class="flex-1 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                                            @keyup.enter.prevent="addHashtag"
                                        />
                                        <button
                                            type="button"
                                            @click="addHashtag"
                                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                                        >
                                            Add
                                        </button>
                                    </div>
                                    <div v-if="form.template_data.hashtags.length" class="mt-2 flex flex-wrap gap-2">
                                        <span
                                            v-for="(tag, index) in form.template_data.hashtags"
                                            :key="index"
                                            class="inline-flex items-center gap-1 px-2 py-1 bg-violet-100 text-violet-700 text-sm rounded-full"
                                        >
                                            #{{ tag }}
                                            <button type="button" @click="removeHashtag(index)" class="hover:text-violet-900">&times;</button>
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label for="cta" class="block text-sm font-medium text-gray-700">Call to Action</label>
                                    <input
                                        id="cta"
                                        v-model="form.template_data.cta"
                                        type="text"
                                        placeholder="e.g., Shop now!, Learn more, Contact us"
                                        class="mt-1 block w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="border-t pt-6">
                            <h3 class="text-sm font-medium text-gray-900 mb-4">Settings</h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center gap-6">
                                    <label class="flex items-center gap-2">
                                        <input
                                            v-model="form.is_premium"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                                        />
                                        <span class="text-sm text-gray-700">Premium template</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            v-model="form.is_active"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                                        />
                                        <span class="text-sm text-gray-700">Active</span>
                                    </label>
                                </div>

                                <div class="w-32">
                                    <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                                    <input
                                        id="sort_order"
                                        v-model.number="form.sort_order"
                                        type="number"
                                        min="0"
                                        class="mt-1 block w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-3 pt-4 border-t">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Template' }}
                            </button>
                            <Link
                                href="/admin/bizboost/templates"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
