<template>
    <div class="bg-white rounded-lg shadow">
        <!-- Table Header with Filters -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex gap-4">
                    <select v-model="filters.status" class="rounded-md border-gray-300">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select v-model="filters.category" class="rounded-md border-gray-300">
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        v-model="filters.search"
                        placeholder="Search investments..."
                        class="rounded-md border-gray-300"
                    >
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="investment in filteredInvestments" :key="investment.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ investment.user.name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ formatCurrency(investment.amount) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ investment.category.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatusClass(investment.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                {{ investment.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(investment.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button
                                v-if="investment.status === 'pending'"
                                @click="approveInvestment(investment.id)"
                                class="text-green-600 hover:text-green-900 mr-2"
                            >
                                Approve
                            </button>
                            <button
                                v-if="investment.status === 'pending'"
                                @click="rejectInvestment(investment.id)"
                                class="text-red-600 hover:text-red-900 mr-2"
                            >
                                Reject
                            </button>
                            <Link
                                :href="route('admin.investments.show', investment.id)"
                                class="text-blue-600 hover:text-blue-900"
                            >
                                View Details
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200">
            <Pagination :links="investments.links" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    investments: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        required: true
    }
});

const filters = ref({
    status: '',
    category: '',
    search: ''
});

const filteredInvestments = computed(() => {
    return props.investments.data.filter(investment => {
        const matchesStatus = !filters.value.status || investment.status === filters.value.status;
        const matchesCategory = !filters.value.category || investment.category.id === parseInt(filters.value.category);
        const matchesSearch = !filters.value.search ||
            investment.user.name.toLowerCase().includes(filters.value.search.toLowerCase()) ||
            investment.category.name.toLowerCase().includes(filters.value.search.toLowerCase());

        return matchesStatus && matchesCategory && matchesSearch;
    });
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW'
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        active: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const approveInvestment = (id) => {
    router.patch(route('admin.investments.approve', id));
};

const rejectInvestment = (id) => {
    router.patch(route('admin.investments.reject', id));
};
</script>
