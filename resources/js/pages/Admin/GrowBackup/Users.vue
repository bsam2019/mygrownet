<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

interface User {
    id: string;
    user_id: string;
    user_name: string;
    user_email: string;
    plan_name: string;
    quota_gb: number;
    used_gb: number;
    files_count: number;
    percent_used: number;
    bonus_quota_bytes: number;
}

interface Props {
    users: {
        data: User[];
        links: any[];
        meta: any;
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();
const showBonusModal = ref(false);
const selectedUser = ref<User | null>(null);

const bonusForm = useForm({
    bonus_gb: 0,
    reason: '',
});

const awardBonus = (user: User) => {
    selectedUser.value = user;
    bonusForm.reset();
    showBonusModal.value = true;
};

const submitBonus = () => {
    if (!selectedUser.value) return;
    
    bonusForm.post(route('admin.growbackup.users.award-bonus', selectedUser.value.id), {
        onSuccess: () => {
            showBonusModal.value = false;
            selectedUser.value = null;
        },
    });
};

const removeBonus = (userId: string) => {
    if (confirm('Remove all bonus storage from this user?')) {
        router.post(route('admin.growbackup.users.remove-bonus', userId));
    }
};

const search = ref(props.filters.search || '');

const performSearch = () => {
    router.get(route('admin.growbackup.users'), { search: search.value }, { preserveState: true });
};
</script>

<template>
    <Head title="GrowBackup Users" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">User Storage Management</h1>

                <!-- Search -->
                <div class="mb-6">
                    <input
                        v-model="search"
                        @keyup.enter="performSearch"
                        type="text"
                        placeholder="Search by name or email..."
                        class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-lg"
                    />
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Files</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bonus</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ user.user_name }}</div>
                                    <div class="text-sm text-gray-500">{{ user.user_email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ user.plan_name }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ user.used_gb.toFixed(2) }} / {{ user.quota_gb }} GB</div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-blue-600 h-2 rounded-full" :style="{ width: `${user.percent_used}%` }"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ user.files_count }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span v-if="user.bonus_quota_bytes > 0" class="text-green-600 font-medium">
                                        +{{ (user.bonus_quota_bytes / (1024 * 1024 * 1024)).toFixed(2) }} GB
                                    </span>
                                    <span v-else class="text-gray-400">None</span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm space-x-2">
                                    <button
                                        @click="awardBonus(user)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Award Bonus
                                    </button>
                                    <button
                                        v-if="user.bonus_quota_bytes > 0"
                                        @click="removeBonus(user.id)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Bonus Modal -->
                <div v-if="showBonusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-lg max-w-md w-full p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Award Bonus Storage</h2>
                        <p class="text-sm text-gray-600 mb-4">User: {{ selectedUser?.user_name }}</p>

                        <form @submit.prevent="submitBonus" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bonus Storage (GB)</label>
                                <input v-model.number="bonusForm.bonus_gb" type="number" step="0.1" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason (Optional)</label>
                                <textarea v-model="bonusForm.reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Why are you awarding this bonus?"></textarea>
                            </div>

                            <div class="flex gap-3">
                                <button type="submit" :disabled="bonusForm.processing" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    {{ bonusForm.processing ? 'Awarding...' : 'Award Bonus' }}
                                </button>
                                <button type="button" @click="showBonusModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
