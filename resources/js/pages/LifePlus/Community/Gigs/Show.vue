<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    MapPinIcon,
    UserCircleIcon,
    ClockIcon,
    CheckCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Application {
    id: number;
    user: { id: number; name: string };
    message: string | null;
    status: string;
    applied_at: string;
}

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
    assigned_to: { id: number; name: string } | null;
    applications: Application[];
    posted_at: string;
    is_owner: boolean;
    has_applied: boolean;
}

const props = defineProps<{
    gig: Gig;
}>();

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const showApplyModal = ref(false);
const showAssignModal = ref(false);

const applyForm = useForm({
    message: '',
});

const submitApplication = () => {
    applyForm.post(route('lifeplus.gigs.apply', props.gig.id), {
        onSuccess: () => {
            showApplyModal.value = false;
            applyForm.reset();
        },
    });
};

const assignWorker = (workerId: number) => {
    useForm({ worker_id: workerId }).post(route('lifeplus.gigs.assign', props.gig.id), {
        onSuccess: () => {
            showAssignModal.value = false;
        },
    });
};

const completeGig = () => {
    useForm({}).post(route('lifeplus.gigs.complete', props.gig.id));
};

const getStatusLabel = (status: string) => {
    return {
        open: 'Open',
        assigned: 'In Progress',
        completed: 'Completed',
        cancelled: 'Cancelled',
    }[status] || status;
};

const getStatusColor = (status: string) => {
    return {
        open: 'bg-emerald-100 text-emerald-700',
        assigned: 'bg-amber-100 text-amber-700',
        completed: 'bg-blue-100 text-blue-700',
        cancelled: 'bg-gray-100 text-gray-700',
    }[status] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <div class="p-4 space-y-6 pb-24">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.gigs.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Back to gigs"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900">Gig Details</h1>
        </div>

        <!-- Gig Card -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-start gap-4">
                <span class="text-4xl">{{ gig.category_icon }}</span>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span 
                            class="text-xs px-2.5 py-1 rounded-full font-medium"
                            :class="getStatusColor(gig.status)"
                        >
                            {{ getStatusLabel(gig.status) }}
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">{{ gig.title }}</h2>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                <!-- Payment -->
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold text-emerald-600">{{ gig.formatted_payment }}</span>
                </div>

                <!-- Location -->
                <div v-if="gig.location" class="flex items-center gap-2 text-gray-600">
                    <MapPinIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    <span>{{ gig.location }}</span>
                </div>

                <!-- Posted by -->
                <div v-if="gig.poster" class="flex items-center gap-2 text-gray-600">
                    <UserCircleIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    <span>Posted by {{ gig.poster.name }}</span>
                </div>

                <!-- Posted time -->
                <div class="flex items-center gap-2 text-gray-500 text-sm">
                    <ClockIcon class="h-4 w-4" aria-hidden="true" />
                    <span>{{ gig.posted_at }}</span>
                </div>
            </div>

            <!-- Description -->
            <div v-if="gig.description" class="mt-5 pt-5 border-t border-gray-100">
                <h3 class="font-semibold text-gray-900 mb-2">Description</h3>
                <p class="text-gray-600 whitespace-pre-wrap">{{ gig.description }}</p>
            </div>
        </div>

        <!-- Assigned Worker (if assigned) -->
        <div v-if="gig.assigned_to && gig.status === 'assigned'" class="bg-amber-50 rounded-2xl p-4 border border-amber-200">
            <h3 class="font-semibold text-amber-800 mb-2">Assigned Worker</h3>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-200 rounded-full flex items-center justify-center">
                    <UserCircleIcon class="h-6 w-6 text-amber-700" aria-hidden="true" />
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ gig.assigned_to.name }}</p>
                    <p class="text-sm text-gray-500">Working on this gig</p>
                </div>
            </div>
        </div>

        <!-- Applications (for owner) -->
        <div v-if="gig.is_owner && gig.applications.length > 0 && gig.status === 'open'" class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-900 mb-4">
                Applications ({{ gig.applications.length }})
            </h3>
            <div class="space-y-3">
                <div 
                    v-for="app in gig.applications" 
                    :key="app.id"
                    class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl"
                >
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <UserCircleIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900">{{ app.user.name }}</p>
                        <p v-if="app.message" class="text-sm text-gray-600 mt-1">{{ app.message }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ app.applied_at }}</p>
                    </div>
                    <button 
                        @click="assignWorker(app.user.id)"
                        class="px-3 py-1.5 bg-emerald-500 text-white text-sm rounded-lg font-medium hover:bg-emerald-600 transition-colors"
                    >
                        Assign
                    </button>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="fixed bottom-20 left-0 right-0 p-4 bg-white border-t border-gray-100 safe-area-bottom">
            <!-- For non-owners: Apply button -->
            <button 
                v-if="!gig.is_owner && gig.status === 'open' && !gig.has_applied"
                @click="showApplyModal = true"
                class="w-full py-3 bg-blue-500 text-white rounded-xl font-medium hover:bg-blue-600 transition-colors"
            >
                Apply for this Gig
            </button>

            <!-- Already applied -->
            <div 
                v-else-if="!gig.is_owner && gig.has_applied"
                class="w-full py-3 bg-gray-100 text-gray-600 rounded-xl font-medium text-center flex items-center justify-center gap-2"
            >
                <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                Application Submitted
            </div>

            <!-- For owners: Complete button -->
            <button 
                v-else-if="gig.is_owner && gig.status === 'assigned'"
                @click="completeGig"
                class="w-full py-3 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 transition-colors flex items-center justify-center gap-2"
            >
                <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                Mark as Completed
            </button>

            <!-- Completed status -->
            <div 
                v-else-if="gig.status === 'completed'"
                class="w-full py-3 bg-blue-50 text-blue-700 rounded-xl font-medium text-center flex items-center justify-center gap-2"
            >
                <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                This gig has been completed
            </div>
        </div>

        <!-- Apply Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showApplyModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center">
                    <div 
                        class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom"
                        @click.stop
                    >
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Apply for Gig</h2>
                            <button 
                                @click="showApplyModal = false"
                                class="p-2 hover:bg-gray-100 rounded-full"
                                aria-label="Close modal"
                            >
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitApplication" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Message (optional)
                                </label>
                                <textarea 
                                    v-model="applyForm.message"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Tell the poster why you're a good fit..."
                                ></textarea>
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="applyForm.processing"
                                class="w-full py-3 bg-blue-500 text-white rounded-xl font-medium hover:bg-blue-600 disabled:opacity-50 transition-colors"
                            >
                                {{ applyForm.processing ? 'Submitting...' : 'Submit Application' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
