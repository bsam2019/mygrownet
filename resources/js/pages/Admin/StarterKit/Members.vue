<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

interface Member {
    id: number;
    name: string;
    email: string;
    purchased_at: string;
    progress: number;
    achievements_count: number;
    last_access: string | null;
}

interface Pagination {
    data: Member[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    members: Pagination;
    filters: {
        search?: string;
    };
}>();

const searchForm = useForm({
    search: props.filters.search || '',
});

const search = () => {
    searchForm.get(route('admin.starter-kit.members'), {
        preserveState: true,
    });
};

const getProgressColor = (progress: number) => {
    if (progress >= 80) return 'bg-green-500';
    if (progress >= 50) return 'bg-blue-500';
    if (progress >= 25) return 'bg-yellow-500';
    return 'bg-gray-300';
};
</script>

<template>
    <Head title="Starter Kit Members" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link :href="route('admin.starter-kit.dashboard')" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">
                        ← Back to Dashboard
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900">Starter Kit Members</h1>
                </div>

                <!-- Search -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex gap-4">
                        <div class="flex-1 relative">
                            <input
                                v-model="searchForm.search"
                                type="text"
                                placeholder="Search by name or email..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                @keyup.enter="search"
                            />
                            <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                        </div>
                        <button
                            @click="search"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            Search
                        </button>
                    </div>
                </div>

                <!-- Members Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="member in members.data" :key="member.id" class="bg-white rounded-lg shadow p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ member.name }}</h3>
                            <p class="text-sm text-gray-500">{{ member.email }}</p>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Progress</span>
                                    <span class="font-semibold">{{ member.progress }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div
                                        :class="getProgressColor(member.progress)"
                                        class="h-2 rounded-full transition-all"
                                        :style="{ width: `${member.progress}%` }"
                                    ></div>
                                </div>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Achievements</span>
                                <span class="font-semibold">{{ member.achievements_count }}</span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Purchased</span>
                                <span class="font-semibold">{{ member.purchased_at }}</span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Last Access</span>
                                <span class="font-semibold">{{ member.last_access || 'Never' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ (members.current_page - 1) * members.per_page + 1 }} to 
                        {{ Math.min(members.current_page * members.per_page, members.total) }} of 
                        {{ members.total }} members
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="members.current_page > 1"
                            :href="route('admin.starter-kit.members', { ...filters, page: members.current_page - 1 })"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="members.current_page < members.last_page"
                            :href="route('admin.starter-kit.members', { ...filters, page: members.current_page + 1 })"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
