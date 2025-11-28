<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import InvestorLayout from '@/Layouts/InvestorLayout.vue';
import {
    UserGroupIcon,
    MapPinIcon,
    BriefcaseIcon,
    EnvelopeIcon,
    PencilIcon,
    EyeIcon,
    EyeSlashIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    CheckBadgeIcon,
    CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

interface DirectoryProfile {
    id: number;
    investor_account_id: number;
    is_listed: boolean;
    display_name: string | null;
    industry: string | null;
    location: string | null;
    bio: string | null;
    show_investment_date: boolean;
    allow_contact: boolean;
    investor_account?: {
        user: { name: string };
        created_at: string;
    };
}

interface Listing {
    data: DirectoryProfile[];
    links: any;
    meta: any;
}

interface Investor {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    investor: Investor;
    listings: Listing;
    myProfile: DirectoryProfile;
    activePage?: string;
    unreadMessages?: number;
    unreadAnnouncements?: number;
}>();

const showEditModal = ref(false);
const showContactModal = ref(false);
const selectedProfile = ref<DirectoryProfile | null>(null);
const searchQuery = ref('');

const profileForm = useForm({
    is_listed: props.myProfile.is_listed,
    display_name: props.myProfile.display_name || '',
    industry: props.myProfile.industry || '',
    location: props.myProfile.location || '',
    bio: props.myProfile.bio || '',
    show_investment_date: props.myProfile.show_investment_date,
    allow_contact: props.myProfile.allow_contact,
});

const contactForm = useForm({
    recipient_id: 0,
    message: '',
});

const filteredListings = computed(() => {
    if (!searchQuery.value) return props.listings.data;
    const query = searchQuery.value.toLowerCase();
    return props.listings.data.filter(p => 
        p.display_name?.toLowerCase().includes(query) ||
        p.industry?.toLowerCase().includes(query) ||
        p.location?.toLowerCase().includes(query)
    );
});

const saveProfile = () => {
    profileForm.post(route('investor.directory.update'), {
        onSuccess: () => { showEditModal.value = false; },
    });
};

const openContactModal = (profile: DirectoryProfile) => {
    selectedProfile.value = profile;
    contactForm.recipient_id = profile.investor_account_id;
    contactForm.message = '';
    showContactModal.value = true;
};

const sendContactRequest = () => {
    contactForm.post(route('investor.directory.contact'), {
        onSuccess: () => {
            showContactModal.value = false;
            contactForm.reset();
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', { year: 'numeric', month: 'short' });
};

const getInitials = (name: string | null) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getAvatarColor = (id: number) => {
    const colors = [
        'from-blue-500 to-indigo-600',
        'from-emerald-500 to-teal-600',
        'from-purple-500 to-violet-600',
        'from-amber-500 to-orange-600',
        'from-rose-500 to-pink-600',
    ];
    return colors[id % colors.length];
};
</script>

<template>
    <InvestorLayout 
        :investor="investor" 
        page-title="Directory" 
        :active-page="activePage || 'directory'"
        :unread-messages="unreadMessages"
        :unread-announcements="unreadAnnouncements"
    >
        <Head title="Shareholder Directory" />

        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <UserGroupIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Shareholder Directory</h1>
                    </div>
                    <p class="text-gray-600">Connect with fellow shareholders</p>
                </div>
                <button @click="showEditModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                    <PencilIcon class="h-5 w-5" aria-hidden="true" />
                    Edit My Profile
                </button>
            </div>

            <div :class="['rounded-xl p-4 mb-8 border', myProfile.is_listed ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 border-gray-200']">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div :class="['p-2 rounded-lg', myProfile.is_listed ? 'bg-emerald-100' : 'bg-gray-100']">
                            <component :is="myProfile.is_listed ? EyeIcon : EyeSlashIcon" :class="['h-5 w-5', myProfile.is_listed ? 'text-emerald-600' : 'text-gray-500']" aria-hidden="true" />
                        </div>
                        <div>
                            <p :class="['font-medium', myProfile.is_listed ? 'text-emerald-800' : 'text-gray-700']">
                                {{ myProfile.is_listed ? 'Your profile is visible' : 'Your profile is hidden' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ myProfile.is_listed ? 'Other shareholders can discover you' : 'Enable listing to connect' }}
                            </p>
                        </div>
                    </div>
                    <div v-if="myProfile.is_listed" class="hidden sm:flex items-center gap-1.5 px-3 py-1 bg-emerald-100 rounded-full">
                        <CheckBadgeIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                        <span class="text-sm font-medium text-emerald-700">Listed</span>
                    </div>
                </div>
            </div>

            <div class="relative mb-6">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                <input v-model="searchQuery" type="text" placeholder="Search by name, industry, or location..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="profile in filteredListings" :key="profile.id" class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-4">
                        <div :class="['w-12 h-12 rounded-full bg-gradient-to-br flex items-center justify-center text-white font-semibold', getAvatarColor(profile.id)]">
                            {{ getInitials(profile.display_name) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 truncate">{{ profile.display_name || 'Anonymous' }}</h3>
                            <div v-if="profile.industry" class="flex items-center gap-1.5 text-sm text-gray-600 mt-1">
                                <BriefcaseIcon class="h-4 w-4" aria-hidden="true" />
                                <span class="truncate">{{ profile.industry }}</span>
                            </div>
                            <div v-if="profile.location" class="flex items-center gap-1.5 text-sm text-gray-600 mt-1">
                                <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                                <span class="truncate">{{ profile.location }}</span>
                            </div>
                            <div v-if="profile.show_investment_date && profile.investor_account" class="flex items-center gap-1.5 text-sm text-gray-500 mt-1">
                                <CalendarDaysIcon class="h-4 w-4" aria-hidden="true" />
                                <span>Since {{ formatDate(profile.investor_account.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-if="profile.bio" class="text-sm text-gray-600 mt-3 line-clamp-2">{{ profile.bio }}</p>
                    <button v-if="profile.allow_contact" @click="openContactModal(profile)" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 font-medium rounded-lg hover:bg-blue-100">
                        <EnvelopeIcon class="h-4 w-4" aria-hidden="true" />
                        Send Message
                    </button>
                </div>
            </div>

            <div v-if="filteredListings.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <UserGroupIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                <p class="text-gray-500">No shareholders found</p>
            </div>
        </div>

        <!-- Edit Profile Modal -->
        <Teleport to="body">
            <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
                    <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Edit Directory Profile</h2>
                            <button @click="showEditModal = false" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Close modal">
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>
                        <form @submit.prevent="saveProfile" class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">List in Directory</p>
                                    <p class="text-sm text-gray-600">Make your profile visible</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="profileForm.is_listed" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                                <input v-model="profileForm.display_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="How you want to appear" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                                <input v-model="profileForm.industry" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g., Technology, Finance" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <input v-model="profileForm.location" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g., Lusaka, Zambia" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                                <textarea v-model="profileForm.bio" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Tell others about yourself..."></textarea>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Show Investment Date</p>
                                    <p class="text-sm text-gray-600">Display when you became a shareholder</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="profileForm.show_investment_date" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Allow Contact</p>
                                    <p class="text-sm text-gray-600">Let others send you messages</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="profileForm.allow_contact" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex gap-3 pt-4">
                                <button type="button" @click="showEditModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">Cancel</button>
                                <button type="submit" :disabled="profileForm.processing" class="flex-1 px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    {{ profileForm.processing ? 'Saving...' : 'Save Profile' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>


        <!-- Contact Modal -->
        <Teleport to="body">
            <div v-if="showContactModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showContactModal = false"></div>
                    <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">
                                Contact {{ selectedProfile?.display_name || 'Shareholder' }}
                            </h2>
                            <button @click="showContactModal = false" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Close contact modal">
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>
                        <form @submit.prevent="sendContactRequest" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Your Message</label>
                                <textarea v-model="contactForm.message" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Introduce yourself and explain why you'd like to connect..."></textarea>
                            </div>
                            <p class="text-sm text-gray-500">Your message will be sent to this shareholder. They can choose to respond via the platform.</p>
                            <div class="flex gap-3 pt-4">
                                <button type="button" @click="showContactModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">Cancel</button>
                                <button type="submit" :disabled="contactForm.processing || !contactForm.message" class="flex-1 px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    {{ contactForm.processing ? 'Sending...' : 'Send Message' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </InvestorLayout>
</template>
