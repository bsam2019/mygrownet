<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import { useLifePlusAccess } from '@/composables/useLifePlusAccess';
import {
    PlusIcon,
    MapPinIcon,
    BriefcaseIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    SparklesIcon,
    WrenchScrewdriverIcon,
    HomeIcon,
    FireIcon,
    CakeIcon,
    AcademicCapIcon,
    TruckIcon,
    PaintBrushIcon,
    EllipsisHorizontalIcon,
    LockClosedIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

const { canAccess } = useLifePlusAccess();
const canPostGig = canAccess('gigs_post');

interface Gig {
    id: number;
    title: string;
    description: string | null;
    category: string | null;
    category_icon: string;
    payment_amount: number | null;
    formatted_payment: string;
    location: string | null;
    status: string;
    status_color: string;
    poster: { id: number; name: string } | null;
    posted_at: string;
}

interface Category {
    id: string;
    name: string;
    icon: string;
}

const props = defineProps<{
    gigs: Gig[];
    categories: Category[];
    filters: Record<string, any>;
}>();

const showAddModal = ref(false);
const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');

const form = useForm({
    title: '',
    description: '',
    category: '',
    payment_amount: '',
    location: '',
});

const search = () => {
    router.get(route('lifeplus.gigs.index'), {
        search: searchQuery.value || null,
        category: selectedCategory.value || null,
    }, { preserveState: true });
};

const filterByCategory = (category: string) => {
    selectedCategory.value = category;
    search();
};

const submitGig = () => {
    form.post(route('lifeplus.gigs.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

// Map category names to Heroicons
const categoryIcons: Record<string, any> = {
    'cleaning': SparklesIcon,
    'yard_work': HomeIcon,
    'cooking': CakeIcon,
    'babysitting': FireIcon,
    'tutoring': AcademicCapIcon,
    'delivery': TruckIcon,
    'repairs': WrenchScrewdriverIcon,
    'firenze': PaintBrushIcon,
    'other': EllipsisHorizontalIcon,
};

const getCategoryIcon = (categoryId: string) => {
    return categoryIcons[categoryId] || BriefcaseIcon;
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Gig Finder</h1>
            <div class="flex gap-2">
                <Link 
                    :href="route('lifeplus.gigs.my-gigs')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors text-sm"
                >
                    My Gigs
                </Link>
                <button 
                    v-if="canPostGig"
                    @click="showAddModal = true"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-xl font-medium hover:bg-blue-600 transition-colors"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Post
                </button>
                <div v-else class="flex items-center gap-2 px-3 py-2 bg-gray-100 text-gray-500 rounded-xl text-sm">
                    <LockClosedIcon class="h-4 w-4" aria-hidden="true" />
                    Premium
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="relative">
            <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
            <input 
                v-model="searchQuery"
                @keyup.enter="search"
                type="text"
                placeholder="Search gigs..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
        </div>

        <!-- Categories -->
        <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4">
            <button 
                @click="filterByCategory('')"
                :class="[
                    'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                    !selectedCategory 
                        ? 'bg-blue-500 text-white' 
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
            >
                All
            </button>
            <button 
                v-for="cat in categories" 
                :key="cat.id"
                @click="filterByCategory(cat.id)"
                :class="[
                    'flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                    selectedCategory === cat.id 
                        ? 'bg-blue-500 text-white' 
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
            >
                <component :is="getCategoryIcon(cat.id)" class="h-4 w-4" aria-hidden="true" />
                {{ cat.name }}
            </button>
        </div>

        <!-- Gigs List -->
        <div class="space-y-3">
            <div v-if="gigs.length === 0" class="text-center py-12">
                <BriefcaseIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">No gigs found</p>
                <button 
                    @click="showAddModal = true"
                    class="mt-3 text-blue-600 font-medium"
                >
                    Post a gig
                </button>
            </div>

            <Link 
                v-for="gig in gigs" 
                :key="gig.id"
                :href="route('lifeplus.gigs.show', gig.id)"
                class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
            >
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <component :is="getCategoryIcon(gig.category || 'other')" class="h-5 w-5 text-blue-600" aria-hidden="true" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900">{{ gig.title }}</h3>
                        <p v-if="gig.description" class="text-sm text-gray-600 mt-1 line-clamp-2">
                            {{ gig.description }}
                        </p>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="text-sm font-semibold text-emerald-600">
                                {{ gig.formatted_payment }}
                            </span>
                            <span v-if="gig.location" class="flex items-center gap-1 text-xs text-gray-500">
                                <MapPinIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                {{ gig.location }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">{{ gig.posted_at }}</p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Post Gig Modal -->
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
                            <h2 class="text-lg font-semibold text-gray-900">Post a Gig</h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitGig" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input 
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g., Need help cleaning house"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea 
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Describe the job..."
                                ></textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select 
                                        v-model="form.category"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Select</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                            {{ cat.name }}
                                        </option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment (K)</label>
                                    <input 
                                        v-model="form.payment_amount"
                                        type="number"
                                        min="0"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0"
                                    />
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <input 
                                    v-model="form.location"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g., Chilenje, Lusaka"
                                />
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-blue-500 text-white rounded-xl font-medium hover:bg-blue-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Posting...' : 'Post Gig' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
