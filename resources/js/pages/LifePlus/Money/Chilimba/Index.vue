<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    PlusIcon,
    UserGroupIcon,
    CalendarDaysIcon,
    BanknotesIcon,
    ChevronRightIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Group {
    id: number;
    name: string;
    meeting_frequency: string;
    meeting_day: string | null;
    contribution_amount: number;
    total_members: number;
    user_role: string;
    is_secretary: boolean;
    my_total_contributed: number;
    my_position: number | null;
    next_meeting: string | null;
}

const props = defineProps<{ groups: Group[] }>();

const showAddModal = ref(false);
const form = useForm({
    name: '',
    meeting_frequency: 'monthly',
    meeting_day: '',
    meeting_time: '',
    meeting_location: '',
    min_contribution: '',
    max_contribution: '',
    initial_contribution: '',
    teacher_contribution: '',
    absence_penalty: '',
    total_members: '',
    user_role: 'member',
});

const formatCurrency = (amount: number) => 'K ' + amount.toLocaleString();

const submitGroup = () => {
    form.post(route('lifeplus.chilimba.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};
</script>


<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                Chilimba Groups
            </h1>
            <button 
                @click="showAddModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 transition-all shadow-lg"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                New Group
            </button>
        </div>

        <!-- Info Card -->
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl p-4 border border-emerald-200">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                    <UserGroupIcon class="h-5 w-5 text-white" aria-hidden="true" />
                </div>
                <div>
                    <h3 class="font-semibold text-emerald-900">Village Banking Tracker</h3>
                    <p class="text-sm text-emerald-700 mt-1">
                        Track your chilimba contributions, payouts, and loans. Stay organized with meeting reminders.
                    </p>
                </div>
            </div>
        </div>

        <!-- Groups List -->
        <div class="space-y-3">
            <div v-if="groups.length === 0" class="text-center py-12 bg-gradient-to-br from-white to-emerald-50 rounded-3xl border border-emerald-200">
                <UserGroupIcon class="h-12 w-12 text-emerald-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">No chilimba groups yet</p>
                <button 
                    @click="showAddModal = true"
                    class="mt-3 text-emerald-600 font-semibold hover:text-emerald-700"
                >
                    Create your first group
                </button>
            </div>

            <Link 
                v-for="group in groups" 
                :key="group.id"
                :href="route('lifeplus.chilimba.show', group.id)"
                class="block bg-gradient-to-br from-white to-gray-50 rounded-2xl p-4 shadow-lg border border-gray-200 hover:shadow-xl hover:scale-[1.02] transition-all"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-900">{{ group.name }}</h3>
                            <span 
                                v-if="group.is_secretary"
                                class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700"
                            >
                                Secretary
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
                            <span class="flex items-center gap-1">
                                <CalendarDaysIcon class="h-4 w-4" aria-hidden="true" />
                                {{ group.next_meeting || 'No meeting scheduled' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-3">
                            <div class="bg-emerald-50 rounded-lg p-2">
                                <p class="text-xs text-emerald-600">My Contributions</p>
                                <p class="font-semibold text-emerald-700">{{ formatCurrency(group.my_total_contributed) }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2">
                                <p class="text-xs text-blue-600">Queue Position</p>
                                <p class="font-semibold text-blue-700">{{ group.my_position || '-' }} of {{ group.total_members }}</p>
                            </div>
                        </div>
                    </div>
                    <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                </div>
            </Link>
        </div>


        <!-- Add Group Modal -->
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
                    <div class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom max-h-[90vh] overflow-y-auto" @click.stop>
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Create Chilimba Group</h2>
                            <button @click="showAddModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitGroup" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Group Name</label>
                                <input v-model="form.name" type="text" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                                    placeholder="e.g., Chilenje Ladies Chilimba" />
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Frequency</label>
                                    <select v-model="form.meeting_frequency" class="w-full px-4 py-3 border border-gray-300 rounded-xl">
                                        <option value="weekly">Weekly</option>
                                        <option value="bi-weekly">Bi-weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Day</label>
                                    <input v-model="form.meeting_day" type="text"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                        placeholder="e.g., 1st Saturday" />
                                </div>
                            </div>

                            <!-- Contribution Rules -->
                            <div class="bg-emerald-50 rounded-xl p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-emerald-800">Contribution Rules</h4>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Initial Contribution (K)</label>
                                    <input v-model="form.initial_contribution" type="number" min="0"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                        placeholder="First meeting amount (optional)" />
                                    <p class="text-xs text-gray-500 mt-1">Higher amount for first meeting</p>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Min (K)</label>
                                        <input v-model="form.min_contribution" type="number" min="1" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                            placeholder="50" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Max (K)</label>
                                        <input v-model="form.max_contribution" type="number" min="1"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                            placeholder="200" />
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">Regular contribution range per meeting</p>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Teacher (K)</label>
                                        <input v-model="form.teacher_contribution" type="number" min="0"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                            placeholder="5" />
                                        <p class="text-xs text-gray-500 mt-1">Host fee</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Absence (K)</label>
                                        <input v-model="form.absence_penalty" type="number" min="0"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                            placeholder="20" />
                                        <p class="text-xs text-gray-500 mt-1">Miss meeting fee</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Members</label>
                                <input v-model="form.total_members" type="number" min="2" max="100" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                    placeholder="20" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Your Role</label>
                                <select v-model="form.user_role" class="w-full px-4 py-3 border border-gray-300 rounded-xl">
                                    <option value="member">Member</option>
                                    <option value="secretary">Secretary</option>
                                    <option value="treasurer">Treasurer</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Location</label>
                                <input v-model="form.meeting_location" type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                    placeholder="e.g., Mrs. Banda's house" />
                            </div>
                            
                            <button type="submit" :disabled="form.processing"
                                class="w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 disabled:opacity-50">
                                {{ form.processing ? 'Creating...' : 'Create Group' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
