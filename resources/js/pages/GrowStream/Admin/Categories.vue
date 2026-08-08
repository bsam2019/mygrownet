<template>
    <AdminLayout title="Categories - GrowStream Admin">
        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-[var(--gs-text)]">Categories</h1>
                    <p class="mt-2 text-[var(--gs-muted)]">Organise content and manage category chips shown to viewers</p>
                </div>
                <button @click="openCreate" class="gs-btn gs-btn-primary">Add Category</button>
            </div>

            <!-- Search -->
            <div class="mb-6">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search categories..."
                    class="gs-input flex-1 min-w-[300px]"
                    @input="debouncedSearch"
                />
            </div>

            <!-- Categories table -->
            <div class="gs-surface overflow-x-auto">
                <table class="min-w-full divide-y divide-[var(--gs-border)]">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Videos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--gs-border)]">
                        <tr v-for="cat in filteredCategories" :key="cat.id" class="hover:bg-[var(--gs-card-hover)]">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-10 w-10 items-center justify-center rounded-full"
                                        :style="{ backgroundColor: cat.color || 'var(--gs-card-hover)' }"
                                    >
                                        <span class="material-symbols-outlined text-lg text-white" aria-hidden="true">{{ iconName(cat.icon) }}</span>
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-[var(--gs-text)]">{{ cat.name }}</span>
                                            <span v-if="cat.parent_id" class="text-xs text-[var(--gs-muted)]">subcategory</span>
                                        </div>
                                        <div class="text-sm text-[var(--gs-muted)]">{{ cat.description || cat.slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--gs-text)]">{{ cat.parent_id ? cat.parent_name : 'Top level' }}</td>
                            <td class="px-6 py-4 text-sm text-[var(--gs-text)]">{{ cat.videos_count }}</td>
                            <td class="px-6 py-4">
                                <span :class="cat.is_active ? 'gs-chip gs-chip-primary' : 'gs-chip bg-red-500/15 text-red-400'">
                                    {{ cat.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <button @click="openEdit(cat)" class="text-[var(--gs-accent)] hover:opacity-85" aria-label="Edit" title="Edit">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button @click="confirmDelete(cat)" class="text-red-400 hover:text-red-300" aria-label="Delete" title="Delete">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="filteredCategories.length === 0" class="px-6 py-12 text-center text-[var(--gs-muted)]">
                    No categories found. Click "Add Category" to create one.
                </div>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4">
                <div class="fixed inset-0 bg-black/70" @click="showModal = false"></div>
                <div class="gs-card relative w-full max-w-lg p-6">
                    <h3 class="mb-4 text-lg font-semibold text-[var(--gs-text)]">{{ editing ? 'Edit Category' : 'Add Category' }}</h3>
                    <form @submit.prevent="save">
                        <div class="mb-4">
                            <label class="gs-label">Name *</label>
                            <input v-model="form.name" type="text" required class="gs-input" placeholder="e.g. Comedy" />
                        </div>
                        <div class="mb-4">
                            <label class="gs-label">Description</label>
                            <textarea v-model="form.description" rows="2" class="gs-input" placeholder="What kind of content lives here?"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="gs-label">Parent Category</label>
                                <select v-model="form.parent_id" class="gs-input">
                                    <option :value="null">None (top level)</option>
                                    <option v-for="c in topLevel" :key="c.id" :value="c.id" :disabled="c.id === editing?.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="gs-label">Sort Order</label>
                                <input v-model.number="form.sort_order" type="number" min="0" class="gs-input" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="gs-label">Icon (Material icon name)</label>
                                <input v-model="form.icon" type="text" class="gs-input" placeholder="theater_comedy" />
                            </div>
                            <div class="mb-4">
                                <label class="gs-label">Color</label>
                                <input v-model="form.color" type="color" class="gs-input h-11" />
                            </div>
                        </div>
                        <div class="mb-4 flex items-center gap-2">
                            <input v-model="form.is_active" type="checkbox" id="cat-active" class="h-4 w-4 accent-[var(--gs-primary)]" />
                            <label for="cat-active" class="gs-label !mb-0">Active (visible to viewers)</label>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showModal = false" class="gs-btn gs-btn-outline">Cancel</button>
                            <button type="submit" class="gs-btn gs-btn-primary">Save Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    icon?: string;
    color?: string;
    sort_order: number;
    is_active: boolean;
    parent_id?: number;
    parent_name?: string;
    videos_count: number;
}

interface Props {
    categories: Category[];
}

const props = defineProps<Props>();

const filters = reactive({ search: '' });
let searchTimeout: ReturnType<typeof setTimeout>;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { /* local filter only */ }, 300);
};

const filteredCategories = computed(() => {
    const q = filters.search.trim().toLowerCase();
    if (!q) return props.categories;
    return props.categories.filter((c) =>
        c.name.toLowerCase().includes(q) ||
        c.slug.toLowerCase().includes(q) ||
        (c.description || '').toLowerCase().includes(q)
    );
});

const topLevel = computed(() => props.categories.filter((c) => !c.parent_id));

const showModal = ref(false);
const editing = ref<Category | null>(null);
const form = reactive<{
    name: string;
    description: string;
    parent_id: number | null;
    icon: string;
    color: string;
    sort_order: number;
    is_active: boolean;
}>({ name: '', description: '', parent_id: null, icon: '', color: '#e2571f', sort_order: 0, is_active: true });

const resetForm = () => {
    editing.value = null;
    Object.assign(form, { name: '', description: '', parent_id: null, icon: '', color: '#e2571f', sort_order: 0, is_active: true });
};

const openCreate = () => { resetForm(); showModal.value = true; };
const openEdit = (cat: Category) => {
    editing.value = cat;
    Object.assign(form, {
        name: cat.name,
        description: cat.description || '',
        parent_id: cat.parent_id ?? null,
        icon: cat.icon || '',
        color: cat.color || '#e2571f',
        sort_order: cat.sort_order,
        is_active: cat.is_active,
    });
    showModal.value = true;
};

const save = () => {
    const payload = {
        name: form.name,
        description: form.description,
        parent_id: form.parent_id,
        icon: form.icon,
        color: form.color,
        sort_order: form.sort_order,
        is_active: form.is_active,
    };
    if (editing.value) {
        router.put(route('growstream.admin.categories.update', { id: editing.value.id }), payload, { preserveScroll: true });
    } else {
        router.post(route('growstream.admin.categories.store'), payload, { preserveScroll: true });
    }
    showModal.value = false;
};

const confirmDelete = (cat: Category) => {
    if (!confirm(`Delete "${cat.name}"? Its subcategories will be moved up, and it will be removed from videos.`)) return;
    router.delete(route('growstream.admin.categories.destroy', { id: cat.id }), { preserveScroll: true });
};

const iconName = (icon?: string): string => {
    if (!icon) return 'category';
    const mapped: Record<string, string> = {
        'laugh': 'theater_comedy',
        'theater-comedy': 'theater_comedy',
        'mic': 'mic',
        'movie': 'movie',
        'drama-masks': 'drama_masks',
        'bolt': 'bolt',
        'favorite': 'favorite',
        'visibility': 'visibility',
        'live_tv': 'live_tv',
        'smart_display': 'smart_display',
        'music_note': 'music_note',
        'play_circle': 'play_circle',
        'mic_external_on': 'mic_external_on',
        'music': 'music_note',
        'public': 'public',
        'landscape': 'landscape',
        'history': 'history',
        'park': 'park',
        'group': 'group',
        'school': 'school',
        'menu_book': 'menu_book',
        'build': 'build',
        'record_voice_over': 'record_voice_over',
        'briefcase': 'briefcase',
        'self_improvement': 'self_improvement',
        'checkroom': 'checkroom',
        'restaurant': 'restaurant',
        'flight': 'flight',
        'home': 'home',
        'sports_soccer': 'sports_soccer',
        'fitness_center': 'fitness_center',
        'newspaper': 'newspaper',
        'toys': 'toys',
        'category': 'category',
    };
    return mapped[icon] || icon;
};
</script>
