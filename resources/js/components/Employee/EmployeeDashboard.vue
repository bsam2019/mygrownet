<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { 
    UserIcon, 
    ChartBarIcon, 
    CurrencyDollarIcon, 
    UsersIcon,
    ClockIcon,
    TrophyIcon,
    ArrowTrendingUpIcon,
    BanknotesIcon,
    CalendarIcon,
    ExclamationCircleIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency, formatDate, formatPercentage } from '@/utils/formatting';

interface Employee {
    id: number;
    employee_number: string;
    first_name: string;
    last_name: string;
    email: string;
    phone?: string;
    hire_date: string;
    employment_status: string;
    department: {
        id: number;
        name: string;
    };
    position: {
        id: number;
        title: string;
    };
    base_salary: number;
    commission_rate: number;
    performance_rating: number;
    last_performance_review?: string;
}

interface PerformanceMetrics {
    investments_facilitated_count: number;
    investments_facilitated_amount: number;
    client_retention_rate: number;
    commission_generated: number;
    new_client_acquisitions: number;
    goal_achievement_rate: number;
    overall_score: number;
}

interface Commission {
    id: number;
    commission_type: string;
    base_amount: number;
    commission_rate: number;
    commission_amount: number;
    calculation_date: string;
    payment_date?: string;
    status: string;
}

interface ClientAssignment {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
    assignment_type: string;
    assigned_date: string;
    is_active: boolean;
}

interface Props {
    employee: Employee;
    performanceMetrics?: PerformanceMetrics;
    recentCommissions: Commission[];
    assignedClients: ClientAssignment[];
    totalCommissionsThisMonth: number;
    totalCommissionsThisQuarter: number;
    pendingCommissions: number;
}

const props = defineProps<Props>();

const loading = ref(false);
const error = ref<string | null>(null);

const fullName = computed(() => `${props.employee.first_name} ${props.employee.last_name}`);

const getStatusColor = (status: string): string => {
    switch (status.toLowerCase()) {
        case 'active':
            return 'text-green-600 bg-green-50';
        case 'inactive':
            return 'text-gray-600 bg-gray-50';
        case 'terminated':
            return 'text-red-600 bg-red-50';
        case 'suspended':
            return 'text-yellow-600 bg-yellow-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
};

const getCommissionStatusColor = (status: string): string => {
    switch (status.toLowerCase()) {
        case 'paid':
            return 'text-green-600 bg-green-50';
        case 'approved':
            return 'text-blue-600 bg-blue-50';
        case 'pending':
            return 'text-yellow-600 bg-yellow-50';
        case 'cancelled':
            return 'text-red-600 bg-red-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
};

const getAssignmentTypeColor = (type: string): string => {
    switch (type.toLowerCase()) {
        case 'primary':
            return 'text-blue-600 bg-blue-50';
        case 'secondary':
            return 'text-purple-600 bg-purple-50';
        case 'support':
            return 'text-gray-600 bg-gray-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
};

const activeClients = computed(() => 
    props.assignedClients.filter(client => client.is_active)
);

const performanceScore = computed(() => 
    props.performanceMetrics?.overall_score || 0
);

const getPerformanceColor = (score: number): string => {
    if (score >= 8) return 'text-green-600';
    if (score >= 6) return 'text-yellow-600';
    return 'text-red-600';
};
</script>

<template>
    <div class="space-y-6">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <ExclamationCircleIcon class="h-5 w-5 text-red-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div v-else>
            <!-- Welcome Section -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-primary-50 rounded-lg">
                                <UserIcon class="h-8 w-8 text-primary-600" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ fullName }}</h1>
                            <p class="text-gray-600">{{ employee.position.title }} • {{ employee.department.name }}</p>
                            <p class="text-sm text-gray-500">Employee ID: {{ employee.employee_number }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span 
                            :class="getStatusColor(employee.employment_status)"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                        >
                            {{ employee.employment_status.charAt(0).toUpperCase() + employee.employment_status.slice(1) }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">
                            Hired: {{ formatDate(employee.hire_date) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Performance Score -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-purple-50 rounded-lg">
                                <TrophyIcon class="h-6 w-6 text-purple-600" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Performance Score</p>
                            <p :class="getPerformanceColor(performanceScore)" class="text-2xl font-bold">
                                {{ performanceScore.toFixed(1) }}/10
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Monthly Commissions -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-green-50 rounded-lg">
                                <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">This Month</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ formatCurrency(totalCommissionsThisMonth) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Active Clients -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-blue-50 rounded-lg">
                                <UsersIcon class="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Clients</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ activeClients.length }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pending Commissions -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-yellow-50 rounded-lg">
                                <ClockIcon class="h-6 w-6 text-yellow-600" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                {{ formatCurrency(pendingCommissions) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics (if available) -->
            <div v-if="performanceMetrics" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Performance Overview</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="p-3 bg-blue-50 rounded-lg inline-flex mb-2">
                            <ChartBarIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <p class="text-sm text-gray-500">Investments Facilitated</p>
                        <p class="text-xl font-bold text-gray-900">{{ performanceMetrics.investments_facilitated_count }}</p>
                        <p class="text-sm text-gray-600">{{ formatCurrency(performanceMetrics.investments_facilitated_amount) }}</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="p-3 bg-green-50 rounded-lg inline-flex mb-2">
                            <ArrowTrendingUpIcon class="h-6 w-6 text-green-600" />
                        </div>
                        <p class="text-sm text-gray-500">Client Retention</p>
                        <p class="text-xl font-bold text-green-600">{{ formatPercentage(performanceMetrics.client_retention_rate) }}</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="p-3 bg-purple-50 rounded-lg inline-flex mb-2">
                            <UsersIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <p class="text-sm text-gray-500">New Clients</p>
                        <p class="text-xl font-bold text-gray-900">{{ performanceMetrics.new_client_acquisitions }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Commissions -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Commissions</h3>
                        <Link 
                            :href="route('employee.commissions.index')" 
                            class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                        >
                            View All
                        </Link>
                    </div>

                    <div class="space-y-4">
                        <div v-for="commission in recentCommissions.slice(0, 5)" :key="commission.id" 
                            class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ commission.commission_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                </p>
                                <p class="text-sm text-gray-500">{{ formatDate(commission.calculation_date) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600">{{ formatCurrency(commission.commission_amount) }}</p>
                                <span 
                                    :class="getCommissionStatusColor(commission.status)"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ commission.status.charAt(0).toUpperCase() + commission.status.slice(1) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!recentCommissions.length" class="text-center py-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <BanknotesIcon class="h-8 w-8 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No commissions yet</h3>
                        <p class="text-gray-500">Start facilitating investments to earn commissions.</p>
                    </div>
                </div>

                <!-- Assigned Clients -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Assigned Clients</h3>
                        <Link 
                            :href="route('employee.clients.index')" 
                            class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                        >
                            View All
                        </Link>
                    </div>

                    <div class="space-y-4">
                        <div v-for="client in activeClients.slice(0, 5)" :key="client.id" 
                            class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ client.user.name }}</p>
                                <p class="text-sm text-gray-500">{{ client.user.email }}</p>
                                <p class="text-xs text-gray-400">Assigned: {{ formatDate(client.assigned_date) }}</p>
                            </div>
                            <div class="text-right">
                                <span 
                                    :class="getAssignmentTypeColor(client.assignment_type)"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ client.assignment_type.charAt(0).toUpperCase() + client.assignment_type.slice(1) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!activeClients.length" class="text-center py-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <UsersIcon class="h-8 w-8 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No clients assigned</h3>
                        <p class="text-gray-500">Contact your manager to get client assignments.</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link 
                        :href="route('employee.performance.index')" 
                        class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                    >
                        <ChartBarIcon class="h-6 w-6 text-blue-600 mr-3" />
                        <span class="font-medium text-blue-900">View Performance</span>
                    </Link>
                    
                    <Link 
                        :href="route('employee.commissions.index')" 
                        class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"
                    >
                        <CurrencyDollarIcon class="h-6 w-6 text-green-600 mr-3" />
                        <span class="font-medium text-green-900">Commission History</span>
                    </Link>
                    
                    <Link 
                        :href="route('employee.profile.edit')" 
                        class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors"
                    >
                        <UserIcon class="h-6 w-6 text-purple-600 mr-3" />
                        <span class="font-medium text-purple-900">Update Profile</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>