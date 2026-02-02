<template>
    <TransitionRoot :show="modelValue" as="template">
        <Dialog as="div" class="relative z-50" @close="$emit('update:modelValue', false)">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all">
                            <DialogTitle class="text-lg font-semibold text-gray-900 mb-4">
                                {{ card ? 'Edit Promotional Card' : 'Create Promotional Card' }}
                            </DialogTitle>

                            <form @submit.prevent="handleSubmit" class="space-y-4">
                                <!-- Title -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Title <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Enter card title"
                                    />
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Description
                                    </label>
                                    <textarea
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Enter card description"
                                    ></textarea>
                                </div>

                                <!-- Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.category"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="">Select category</option>
                                        <option value="general">General</option>
                                        <option value="opportunity">Opportunity</option>
                                        <option value="training">Training</option>
                                        <option value="success">Success Story</option>
                                        <option value="announcement">Announcement</option>
                                    </select>
                                </div>

                                <!-- Image Upload -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Image <span v-if="!card" class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1 flex items-center gap-4">
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            accept="image/jpeg,image/png,image/jpg,image/webp"
                                            @change="handleFileChange"
                                            class="hidden"
                                        />
                                        <button
                                            type="button"
                                            @click="$refs.fileInput.click()"
                                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                                        >
                                            Choose Image
                                        </button>
                                        <span v-if="imagePreview || card?.image_url" class="text-sm text-gray-600">
                                            {{ form.image?.name || 'Current image' }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Recommended: 1200x630px, max 2MB (JPEG, PNG, WebP)
                                    </p>
                                    <!-- Image Preview -->
                                    <div v-if="imagePreview || card?.image_url" class="mt-3">
                                        <img
                                            :src="imagePreview || card?.image_url"
                                            alt="Preview"
                                            class="h-32 w-auto rounded-lg border border-gray-200"
                                        />
                                    </div>
                                </div>

                                <!-- OG Title -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Social Share Title (OG Title)
                                    </label>
                                    <input
                                        v-model="form.og_title"
                                        type="text"
                                        maxlength="60"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Leave empty to use main title"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Max 60 characters. Used when sharing on social media.
                                    </p>
                                </div>

                                <!-- OG Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Social Share Description (OG Description)
                                    </label>
                                    <textarea
                                        v-model="form.og_description"
                                        rows="2"
                                        maxlength="155"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Leave empty to use main description"
                                    ></textarea>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Max 155 characters. Used when sharing on social media.
                                    </p>
                                </div>

                                <!-- Active Status -->
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        id="is_active"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <label for="is_active" class="text-sm font-medium text-gray-700">
                                        Active (visible to members)
                                    </label>
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-end gap-3 pt-4 border-t">
                                    <button
                                        type="button"
                                        @click="$emit('update:modelValue', false)"
                                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="loading"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                                    >
                                        {{ loading ? 'Saving...' : (card ? 'Update' : 'Create') }}
                                    </button>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue';

interface Card {
    id: number;
    title: string;
    description: string | null;
    category: string;
    image_url: string;
    og_title: string | null;
    og_description: string | null;
    is_active: boolean;
}

const props = defineProps<{
    modelValue: boolean;
    card: Card | null;
}>();

const emit = defineEmits(['update:modelValue', 'saved']);

const loading = ref(false);
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const form = ref({
    title: '',
    description: '',
    category: '',
    image: null as File | null,
    og_title: '',
    og_description: '',
    is_active: true,
});

// Reset form when modal opens/closes or card changes
watch(() => props.card, (newCard) => {
    if (newCard) {
        form.value = {
            title: newCard.title,
            description: newCard.description || '',
            category: newCard.category,
            image: null,
            og_title: newCard.og_title || '',
            og_description: newCard.og_description || '',
            is_active: newCard.is_active,
        };
        imagePreview.value = null;
    } else {
        form.value = {
            title: '',
            description: '',
            category: '',
            image: null,
            og_title: '',
            og_description: '',
            is_active: true,
        };
        imagePreview.value = null;
    }
}, { immediate: true });

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        form.value.image = file;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const handleSubmit = () => {
    loading.value = true;

    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('description', form.value.description);
    formData.append('category', form.value.category);
    formData.append('og_title', form.value.og_title);
    formData.append('og_description', form.value.og_description);
    formData.append('is_active', form.value.is_active ? '1' : '0');
    
    if (form.value.image) {
        formData.append('image', form.value.image);
    }

    const url = props.card
        ? route('admin.promotional-cards.update', props.card.id)
        : route('admin.promotional-cards.store');

    const method = props.card ? 'post' : 'post'; // Laravel handles PUT via _method

    if (props.card) {
        formData.append('_method', 'PUT');
    }

    router.post(url, formData, {
        forceFormData: true,
        onSuccess: () => {
            loading.value = false;
            emit('saved');
        },
        onError: (errors) => {
            loading.value = false;
            console.error('Validation errors:', errors);
        },
    });
};
</script>
