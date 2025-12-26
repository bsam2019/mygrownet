<script setup lang="ts">
import type { Page } from '../../types';

const props = defineProps<{
    show: boolean;
    page: Page | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update', page: Page): void;
}>();

const handleUpdate = () => {
    if (props.page) {
        emit('update', props.page);
    }
};
</script>

<template>
    <Teleport to="body">
        <div v-if="show && page" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Page</h2>
                <form @submit.prevent="handleUpdate" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
                        <input
                            v-model="page.title"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                        <input
                            v-model="page.slug"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900"
                            :disabled="page.isHomepage"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model="page.showInNav" type="checkbox" id="editShowInNav" class="rounded border-gray-300" />
                        <label for="editShowInNav" class="text-sm text-gray-700">Show in navigation</label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="emit('close')" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
