<script setup lang="ts">
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    PlusIcon,
    DocumentTextIcon,
    TrashIcon,
    PencilIcon,
    BookmarkIcon,
} from '@heroicons/vue/24/outline';
import { BookmarkIcon as BookmarkSolid } from '@heroicons/vue/24/solid';

defineOptions({ layout: LifePlusLayout });

interface Note {
    id: number;
    title: string;
    content: string | null;
    excerpt: string | null;
    is_pinned: boolean;
    updated_at: string;
}

const props = defineProps<{
    notes: Note[];
}>();

const showAddModal = ref(false);
const selectedNote = ref<Note | null>(null);

const form = useForm({
    title: '',
    content: '',
});

const openAddModal = (note?: Note) => {
    if (note) {
        selectedNote.value = note;
        form.title = note.title;
        form.content = note.content || '';
    } else {
        selectedNote.value = null;
        form.reset();
    }
    showAddModal.value = true;
};

const submitNote = () => {
    if (selectedNote.value) {
        form.put(route('lifeplus.notes.update', selectedNote.value.id), {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
                selectedNote.value = null;
            },
        });
    } else {
        form.post(route('lifeplus.notes.store'), {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            },
        });
    }
};

const togglePin = (id: number) => {
    router.post(route('lifeplus.notes.pin', id), {}, {
        preserveScroll: true,
    });
};

const deleteNote = (id: number) => {
    if (confirm('Delete this note?')) {
        router.delete(route('lifeplus.notes.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Notes</h1>
            <button 
                @click="openAddModal()"
                class="flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 transition-colors"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                New Note
            </button>
        </div>

        <!-- Notes Grid -->
        <div class="space-y-3">
            <div v-if="notes.length === 0" class="text-center py-12">
                <DocumentTextIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">No notes yet</p>
                <button 
                    @click="openAddModal()"
                    class="mt-3 text-amber-600 font-medium"
                >
                    Create your first note
                </button>
            </div>

            <div 
                v-for="note in notes" 
                :key="note.id"
                class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 group"
                :class="note.is_pinned ? 'ring-2 ring-amber-200' : ''"
            >
                <div class="flex items-start justify-between mb-2">
                    <h3 class="font-semibold text-gray-900 flex-1">{{ note.title }}</h3>
                    <div class="flex items-center gap-1">
                        <button 
                            @click="togglePin(note.id)"
                            class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                            :aria-label="note.is_pinned ? 'Unpin note' : 'Pin note'"
                        >
                            <component 
                                :is="note.is_pinned ? BookmarkSolid : BookmarkIcon"
                                :class="[
                                    'h-4 w-4',
                                    note.is_pinned ? 'text-amber-500' : 'text-gray-400'
                                ]"
                                aria-hidden="true"
                            />
                        </button>
                        <button 
                            @click="openAddModal(note)"
                            class="p-1.5 rounded-lg opacity-0 group-hover:opacity-100 hover:bg-gray-100 transition-all"
                            aria-label="Edit note"
                        >
                            <PencilIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                        </button>
                        <button 
                            @click="deleteNote(note.id)"
                            class="p-1.5 rounded-lg opacity-0 group-hover:opacity-100 hover:bg-red-50 transition-all"
                            aria-label="Delete note"
                        >
                            <TrashIcon class="h-4 w-4 text-red-500" aria-hidden="true" />
                        </button>
                    </div>
                </div>
                
                <p v-if="note.content" class="text-sm text-gray-600 line-clamp-3">
                    {{ note.content }}
                </p>
                
                <p class="text-xs text-gray-400 mt-3">{{ note.updated_at }}</p>
            </div>
        </div>

        <!-- Add/Edit Note Modal -->
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
                        class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom max-h-[90vh] overflow-y-auto"
                        @click.stop
                    >
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ selectedNote ? 'Edit Note' : 'New Note' }}
                            </h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitNote" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input 
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    placeholder="Note title"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                                <textarea 
                                    v-model="form.content"
                                    rows="8"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    placeholder="Write your note..."
                                ></textarea>
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Saving...' : (selectedNote ? 'Update' : 'Save Note') }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
