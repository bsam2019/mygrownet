<template>
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-4 sm:mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Network Management</h1>
                    <p class="mt-1 text-xs sm:text-sm text-gray-600">
                        Move members between referrers while maintaining network integrity
                    </p>
                </div>

                <!-- Search Section -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-4 sm:mb-6">
                    <h2 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">Search Members</h2>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <!-- User to Move -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Member to Move
                            </label>
                            <input
                                v-model="searchQuery.user"
                                @input="searchUsers('user')"
                                type="text"
                                placeholder="Search by name, email, or phone..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            
                            <!-- User Search Results -->
                            <div v-if="searchResults.user.length > 0" class="mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                <button
                                    v-for="user in searchResults.user"
                                    :key="user.id"
                                    @click="selectUser(user)"
                                    class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
                                >
                                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                </button>
                            </div>
                            
                            <!-- Selected User -->
                            <div v-if="selectedUser" class="mt-4 p-4 bg-blue-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ selectedUser.name }}</div>
                                        <div class="text-sm text-gray-600">{{ selectedUser.email }}</div>
                                        <div v-if="selectedUser.referrer" class="text-sm text-gray-500 mt-1">
                                            Current Referrer: {{ selectedUser.referrer.name }}
                                        </div>
                                    </div>
                                    <button @click="clearUser" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- New Referrer -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                New Referrer
                            </label>
                            <input
                                v-model="searchQuery.referrer"
                                @input="searchUsers('referrer')"
                                type="text"
                                placeholder="Search by name, email, or phone..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            
                            <!-- Referrer Search Results -->
                            <div v-if="searchResults.referrer.length > 0" class="mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                <button
                                    v-for="user in searchResults.referrer"
                                    :key="user.id"
                                    @click="selectReferrer(user)"
                                    class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
                                >
                                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                </button>
                            </div>
                            
                            <!-- Selected Referrer -->
                            <div v-if="selectedReferrer" class="mt-4 p-4 bg-green-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ selectedReferrer.name }}</div>
                                        <div class="text-sm text-gray-600">{{ selectedReferrer.email }}</div>
                                        <div v-if="referrerStats" class="text-sm text-gray-500 mt-1">
                                            Direct Downlines: {{ referrerStats.direct_downlines }}/3
                                            <span v-if="referrerStats.matrix_slots_available > 0" class="text-green-600">
                                                ({{ referrerStats.matrix_slots_available }} slots available)
                                            </span>
                                            <span v-else class="text-red-600">
                                                (Matrix full)
                                            </span>
                                        </div>
                                    </div>
                                    <button @click="clearReferrer" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Move Options -->
                    <div v-if="selectedUser && selectedReferrer" class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center mb-4">
                            <input
                                v-model="moveEntireTree"
                                type="checkbox"
                                id="moveTree"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label for="moveTree" class="ml-2 block text-sm text-gray-700">
                                Move entire downline tree with this member
                            </label>
                        </div>

                        <!-- Validation Message -->
                        <div v-if="validationMessage" class="mb-4 p-4 rounded-lg" :class="{
                            'bg-red-50 text-red-800': !canMove,
                            'bg-green-50 text-green-800': canMove
                        }">
                            {{ validationMessage }}
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <button
                                @click="validateMove"
                                :disabled="loading"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50"
                            >
                                Validate Move
                            </button>
                            <button
                                @click="performMove"
                                :disabled="!canMove || loading"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ loading ? 'Moving...' : 'Move Member' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Network Tree Visualization -->
                <div v-if="selectedUser && userNetwork" class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">Current Network Structure</h2>
                    <NetworkTree :tree="userNetwork.tree" />
                </div>

                <!-- Change History -->
                <div v-if="selectedUser && userHistory.length > 0" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Change History for {{ selectedUser.name }}</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">By</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="record in userHistory" :key="record.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ new Date(record.created_at).toLocaleString() }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span v-if="record.old_referrer" class="text-gray-900">
                                            {{ record.old_referrer.name }}
                                        </span>
                                        <span v-else class="text-gray-400 italic">None</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="text-gray-900">{{ record.new_referrer.name }}</div>
                                        <div v-if="record.is_spillover && record.target_referrer" class="text-xs text-gray-500">
                                            (Spillover from {{ record.target_referrer.name }})
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span v-if="record.is_spillover" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Spillover
                                        </span>
                                        <span v-else class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Direct
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ record.performed_by.name }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="userHistory.length === 0" class="text-center py-8 text-gray-500">
                        No change history found for this member
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import NetworkTree from './NetworkTree.vue';
import axios from 'axios';

const searchQuery = ref({
    user: '',
    referrer: ''
});

const searchResults = ref({
    user: [],
    referrer: []
});

const selectedUser = ref(null);
const selectedReferrer = ref(null);
const userNetwork = ref(null);
const referrerStats = ref(null);
const moveEntireTree = ref(false);
const canMove = ref(false);
const validationMessage = ref('');
const loading = ref(false);

let searchTimeout = null;

const searchUsers = (type) => {
    clearTimeout(searchTimeout);
    
    const query = searchQuery.value[type];
    if (query.length < 2) {
        searchResults.value[type] = [];
        return;
    }
    
    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.get(route('admin.network.search-users'), {
                params: { query }
            });
            searchResults.value[type] = response.data;
        } catch (error) {
            console.error('Search error:', error);
        }
    }, 300);
};

const userHistory = ref([]);

const selectUser = async (user) => {
    selectedUser.value = user;
    searchResults.value.user = [];
    searchQuery.value.user = user.name;
    
    // Load user's network
    try {
        const response = await axios.get(route('admin.network.user-network', user.id));
        userNetwork.value = response.data;
        selectedUser.value.referrer = response.data.user.referrer;
    } catch (error) {
        console.error('Error loading network:', error);
    }
    
    // Load user's history
    try {
        const historyResponse = await axios.get(route('admin.network.user-history', user.id));
        userHistory.value = historyResponse.data;
    } catch (error) {
        console.error('Error loading history:', error);
    }
};

const selectReferrer = async (user) => {
    selectedReferrer.value = user;
    searchResults.value.referrer = [];
    searchQuery.value.referrer = user.name;
    
    // Load referrer stats
    try {
        const response = await axios.get(route('admin.network.user-stats', user.id));
        referrerStats.value = response.data;
    } catch (error) {
        console.error('Error loading stats:', error);
    }
};

const clearUser = () => {
    selectedUser.value = null;
    userNetwork.value = null;
    searchQuery.value.user = '';
    canMove.value = false;
    validationMessage.value = '';
};

const clearReferrer = () => {
    selectedReferrer.value = null;
    referrerStats.value = null;
    searchQuery.value.referrer = '';
    canMove.value = false;
    validationMessage.value = '';
};

const validateMove = async () => {
    if (!selectedUser.value || !selectedReferrer.value) return;
    
    try {
        const response = await axios.post(route('admin.network.check-move'), {
            user_id: selectedUser.value.id,
            new_referrer_id: selectedReferrer.value.id
        });
        
        canMove.value = response.data.allowed;
        validationMessage.value = response.data.allowed 
            ? 'Move is valid and can proceed'
            : response.data.reason;
    } catch (error) {
        canMove.value = false;
        validationMessage.value = 'Error validating move';
        console.error('Validation error:', error);
    }
};

const performMove = async () => {
    if (!canMove.value) return;
    
    loading.value = true;
    
    try {
        const response = await axios.post(route('admin.network.move-user'), {
            user_id: selectedUser.value.id,
            new_referrer_id: selectedReferrer.value.id,
            move_entire_tree: moveEntireTree.value
        });
        
        if (response.data.success) {
            alert('Member successfully moved!');
            clearUser();
            clearReferrer();
        }
    } catch (error) {
        alert(error.response?.data?.message || 'Error moving member');
        console.error('Move error:', error);
    } finally {
        loading.value = false;
    }
};
</script>
